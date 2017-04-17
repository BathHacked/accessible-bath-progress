<?php


namespace BathHacked;


abstract class Repository
{
    protected $tableName = null;

    protected $fields = [];

    /**
     * @return \ORM
     */
    public function table()
    {
        if(empty($this->tableName)) throw new Exception('Table name not set');

        return \ORM::forTable($this->tableName);
    }

    public function create($data)
    {
        return $this->table()->create($data);
    }

    /**
     * @return array|\IdiormResultSet
     */
    public function all()
    {
        return $this->table()->findMany();
    }

    /**
     * @param mixed $id
     * @return bool|\ORM
     */
    public function byId($id)
    {
        return $this->table()->findOne($id);
    }

    /**
     * @param $input
     * @return array
     */
    protected function mapFields($input)
    {
        $data = [];

        foreach($this->fields as $field)
        {
            if(isset($input[$field]))
            {
                $data[$field] = $input[$field];
            }
        }

        return $data;
    }
}