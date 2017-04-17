<?php


namespace BathHacked;


class NodeVersions extends Repository
{
    protected $tableName = 'node_versions';

    protected $fields = [
        'id',
        'node_id', 'version',
        'timestamp',
        'wheelchair', 'wheelchair_toilet', 'wheelchair_description',
        'user', 'user_id'
    ];

    public function byNodeVersion($node, $version)
    {
        return $this->table()
            ->where('node_id',$node->id)
            ->where('version', $version)
            ->findOne();
    }
}