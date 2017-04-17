<?php


namespace BathHacked;


use Carbon\Carbon;
use GuzzleHttp\Client;

class Nodes extends Repository
{
    const STATUS_UNKNOWN = 'unknown';
    const STATUS_YES = 'yes';
    const STATUS_NO = 'no';
    const STATUS_LIMITED = 'limited';

    const OSM_BASE_URI = 'http://api.openstreetmap.org';

    protected $tableName = 'nodes';

    protected $fields = [
        'id',
        'name',
        'lat', 'lon',
        'wheelchair', 'wheelchair_toilet', 'wheelchair_description',
        'street', 'housenumber', 'city', 'postcode',
        'website', 'phone'
    ];

    protected $watch = [
        'wheelchair', 'wheelchair_toilet', 'wheelchair_description',
    ];

    public function updateFromWheelmap($remoteNodes)
    {
        /**
         * Count created & updated
         */
        $created = 0;
        $updated = 0;

        /**
         * Get all local nodes indexed by id
         */
        $local = Helpers::indexOn($this->all(), 'id');

        /**
         * We're going to keep track of local & remote ids
         */
        $localIds = Helpers::pluck($local, 'id');
        $remoteIds = [];

        foreach($remoteNodes as $r) {
            $isDirty = false;
            $isNew = false;
            $data = $this->mapFields($r);

            $remoteIds[] = $r['id'];

            if (isset($r['category']['id'])) $data['category_id'] = $r['category']['id'];

            if (isset($r['node_type']['id'])) $data['node_type_id'] = $r['node_type']['id'];

            if (isset($local[$r['id']])) {
                $l = $local[$r['id']];

                /**
                 * Check dirtiness. We're only counting change wheelchair fields as an update
                 */
                foreach ($this->watch as $key) {
                    if ($l[$key] != $r[$key]) {
                        $isDirty = true;
                    }
                }
            } else {
                $l = $this->table()->create();

                $l->created_at = date('Y-m-d H:i:s');

                $isNew = true;

                $created++;
            }

            if ($isDirty) {
                $l->updated_at = date('Y-m-d H:i:s');

                $updated++;
            }

            $l->set($data)->save();

            /**
             * If we're updated or created a node we're going to update its versions
             */
            if($isDirty || $isNew)
            {
                echo "Updated {$l->name} ({$l->id})", PHP_EOL;

                try
                {
                    $this->updateNodeVersions($l, false);
                }
                catch (\Exception $e)
                {
                    Helpers::logger()->error($e->getMessage(), [
                        'node.id' => $l->id
                    ]);

                    echo $e->getMessage(), PHP_EOL;
                }
            }
        }

        /**
         * Delete all nodes we have locally that are not help remotely
         */
        $diff = array_diff($localIds, $remoteIds);

        if(count($diff) > 0)
        {
            $this->table()->whereIn('id', $diff)->deleteMany();

            echo "Removed ", count($diff), ' nodes', PHP_EOL;
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'deleted' => count($diff),
        ];
    }


    /**
     * @param array $nodes
     * @param array $osmConfig
     * @todo Extract this
     */
    public function updateNodeVersions(
        $node,
        $cache = true,
        $cacheTtl = 86400,
        $baseUri = self::OSM_BASE_URI
    )
    {
        $client = new Client([
            'base_uri' => $baseUri
        ]);

        if($cache)
        {
            $key = \FileSystemCache::generateCacheKey($node->id, 'osm');

            $rawXml = \FileSystemCache::retrieve($key);

            if($rawXml === false)
            {
                $rawXml = $this->fetchNodeVersionsFromOsm($node, $client);

                \FileSystemCache::store($key, $rawXml, $cacheTtl);
            }

        }
        else
        {
            $rawXml = $this->fetchNodeVersionsFromOsm($node, $client);
        }

        $xml = simplexml_load_string($rawXml);

        $versions = $this->parseOsmXml($xml);

        $nodeVersionsRepo = new NodeVersions();

        foreach($versions as $version)
        {
            $version['node_id'] = $node->id;

            $nv = $nodeVersionsRepo->byNodeVersion($node, $version['version']);

            if(!$nv)
            {
                $nv = $nodeVersionsRepo->table()->create($version);
            }
            else
            {
                $nv->set($version);
            }

            $nv->save();
        }
    }

    /**
     * @param \ORM $node
     * @param Client $client
     * @return string
     * @todo Extract this
     */
    protected function fetchNodeVersionsFromOsm($node, $client)
    {
        $uri = $node->id > 0
            ? "api/0.6/node/{$node->id}/history"
            : "api/0.6/way/" . abs($node->id) . "/history";

        $response = $client->request('GET', $uri);

        $data = (string) $response->getBody();

        return $data;
    }

    /**
     * @param \SimpleXMLElement $xml
     * @return array
     * @todo Extract this
     */
    protected function parseOsmXml($xml)
    {
        $versions = [];

        foreach($xml->xpath('//node|//way') as $elt)
        {
            $timestamp = Carbon::createFromFormat(\DateTime::ISO8601, (string) $elt['timestamp']);

            $version = [
                'version' => (string) $elt['version'],
                'user' => (string) $elt['user'],
                'user_id' => (string) $elt['uid'],
                'timestamp' => $timestamp->format('Y-m-d H:i:s'),
            ];

            foreach($elt->xpath('tag') as $tag)
            {
                switch((string) $tag['k'])
                {
                    case 'wheelchair':
                        $version['wheelchair'] = (string) $tag['v'];
                        break;
                    case 'toilets:wheelchair':
                        $version['wheelchair_toilet'] = (string) $tag['v'];
                        break;
                    case 'wheelchair:description':
                        $version['wheelchair_description'] = (string) $tag['v'];
                        break;
                    default:
                        break;
                }
            }

            $versions[] = $version;
        }

        return $versions;
    }
}