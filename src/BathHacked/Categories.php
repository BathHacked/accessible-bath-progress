<?php


namespace BathHacked;


class Categories extends Repository
{
    protected $tableName = 'categories';

    protected $fields = ['id', 'localized_name', 'identifier'];

    public function updateFromWheelmap($remote)
    {
        $local = Helpers::indexOn($this->all(), 'id');

        foreach($remote as $r)
        {
            $data = $this->mapFields($r);

            if(isset($local[$r['id']]))
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