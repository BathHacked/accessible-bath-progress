<?php


namespace BathHacked;


class NodeTypes extends Repository
{
    protected $tableName = 'node_types';

    protected $fields = ['id', 'identifier', 'localized_name', 'icon'];

    public function updateFromWheelmap($remote)
    {
        $local = Helpers::indexOn($this->all(), 'id');

        foreach($remote as $r) {
            $data = $this->mapFields($r);

            if (isset($r['category']['id'])) $data['category_id'] = $r['category']['id'];

            if (isset($local[$r['id']]))
            {
                $l = $local[$r['id']];
            }
            else
            {
                $l = $this->table()->create();
            }

            $l->set($data)->save();
        }
    }
}