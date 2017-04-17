<?php


namespace Wheelmap;


use GuzzleHttp\Client;

class Api
{
    const PAGE_BAILOUT = 10;
    const PAGE_SIZE = 500;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var Client
     */
    protected $client;

    /**
     * ClientBuilder constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        if(!isset($config['base_uri'])) throw new Exception('base_uri required in config');
        if(!isset($config['options']['bbox'])) throw new Exception('options.bbox required in config');

        $this->config = $config;

        $this->client = new Client([
            'base_uri' => $this->config['base_uri']
        ]);
    }

    /**
     * @return mixed
     */
    protected function params()
    {
        return $this->config['params'];
    }

    /**
     * @param array $keys
     * @return array
     */
    protected function options($keys)
    {
        return array_intersect_key($this->config['options'], array_flip($keys));
    }

    /**
     * @param string $path
     * @param array $options
     * @return array
     */
    protected function get($path, $options = [])
    {
        $params = $this->params();

        $params = array_merge($params, $options);

        $response = $this->client->request('GET', $path, [
            'query' => $params
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * @param string $path
     * @param string $resultField
     * @param array $options
     * @return array
     */
    protected function getAll($path, $resultField, $options = [])
    {
        $page = 1;
        $items = [];

        do
        {
            $options['page'] = $page;
            $options['per_page'] = static::PAGE_SIZE;

            $result = $this->get($path, $options);

            $items = array_merge($items, $result[$resultField]);

            $done = (
                $page > static::PAGE_BAILOUT
                || $result['meta']['page'] >= $result['meta']['num_pages']
            );

            $page++;

        } while(!$done);

        return $items;
    }

    /**
     * @return array
     */
    public function getLocales()
    {
        $result = $this->get('api/locales');

        return $result;
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        $result = $this->getAll('api/categories', 'categories');

        return $result;
    }

    /**
     * @return array
     */
    public function getNodeTypes()
    {
        $result = $this->getAll('api/node_types', 'node_types');

        return $result;
    }

    /**
     * @return array
     */
    public function getNodes()
    {
        $options = $this->options(['bbox']);

        $result = $this->getAll('api/nodes', 'nodes', $options);

        return $result;
    }

    /**
     * @param int $nodeId
     * @return array
     */
    public function getNodePhotos($nodeId)
    {
        $result = $this->getAll('api/nodes/' . (int) $nodeId, 'photos');

        return $result;
    }
}