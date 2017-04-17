<?php


namespace BathHacked;


use Carbon\Carbon;

class NodeQuery
{
    /**
     * @var bool
     */
    protected $onlyKnown = true;

    /**
     * @var Carbon
     */
    protected $from = null;

    /**
     * @var Carbon
     */
    protected $to = null;

    /**
     * @var int[]
     */
    protected $categoryIds = [];

    /**
     * @var int[]
     */
    protected $nodeTypeIds = [];

    /**
     * @var string[]
     */
    protected $wheelchair = [];

    /**
     * @var string[]
     */
    protected $wheelchairToilet = [];

    /**
     * NodeQuery constructor.
     */
    public function __construct()
    {
        $this->from = new Carbon('1900-01-01');
        $this->to = new Carbon('9999-01-01');
    }

    /**
     * @return mixed
     */
    public function getNodes()
    {
        $nodes = $this->baseQuery()
            ->selectMany([
                'nv.version',
                'nv.timestamp',
                'n.id',
                'n.category_id',
                'n.node_type_id',
                'n.name',
                'nv.wheelchair',
                'nv.wheelchair_toilet',
                'nv.wheelchair_description'
            ])
            ->select('c.localized_name', 'category_name')
            ->orderByDesc('nv.timestamp')
            ->findMany();

        return $nodes;
    }

    /**
     * @param int $count
     * @return mixed
     */
    public function getLatest($count = 12)
    {
        $nodes = $this->baseQuery()
            ->limit($count)
            ->selectMany([
                'nv.timestamp',
                'n.*',
                'nv.wheelchair',
                'nv.wheelchair_toilet',
                'nv.wheelchair_description'
            ])
            ->select('c.localized_name', 'category_name')
            ->orderByDesc('nv.timestamp')
            ->whereNotNull('name')
            ->findMany();

        return $nodes;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        $count = $this->baseQuery()
            ->selectExpr('COUNT(*)', 'count')
            ->findOne();

        return $count->count;
    }

    /**
     * @return mixed
     */
    public function getCategoryCount()
    {
        $counts = $this->baseQuery()
            ->join('categories', ['n.category_id', '=', 'c.id'], 'c')
            ->selectExpr('COUNT(c.id)', 'count')
            ->select('c.*')
            ->groupBy('c.id')
            ->orderByAsc('c.id')
            ->findMany();

        return $counts;
    }

    /**
     * @param string $field
     * @return mixed
     */
    public function getStatusCount($field = 'wheelchair')
    {
        $counts = $this->baseQuery()
            ->selectExpr('COUNT(n.' . $field . ')', 'count')
            ->select('n.' . $field, 'status')
            ->whereNotEqual('n.' . $field, 'unknown')
            ->groupBy('n.' . $field)
            ->orderByAsc('n.' . $field)
            ->findMany();

        return $counts;
    }

    /**
     * This is the nub of the everything
     *
     * We add a subquery to find the latest version for each node
     * which is within [from, to] and (optionally) has known wheelchair/toilet values
     *
     * @return \ORM
     */
    protected function baseQuery()
    {
        $nodes = new Nodes();

        $subQuery = "SELECT MAX(snv.version) FROM node_versions AS snv WHERE snv.node_id = nv.node_id";

        if($this->isOnlyKnown())
        {
            $subQuery .= " AND (n.wheelchair != 'unknown' OR n.wheelchair_toilet != 'unknown') ";
        }

        if(!empty($this->getFrom())) $subQuery .= " AND snv.timestamp >= '". $this->getFrom()->format('Y-m-d H:i:s') ."'";
        if(!empty($this->getTo())) $subQuery .= " AND snv.timestamp <= '". $this->getTo()->format('Y-m-d H:i:s') ."'";

        $query = $nodes->table()
            ->tableAlias('n')
            ->join('node_versions', ['nv.node_id', '=', 'n.id'], 'nv')
            ->join('categories', ['n.category_id', '=', 'c.id'], 'c')
            ->whereRaw("nv.version=({$subQuery})")
        ;

        if(!empty($this->getCategoryIds())) $query->whereIn('n.category_id', $this->getCategoryIds());
        if(!empty($this->getNodeTypeIds())) $query->whereIn('n.node_type_id', $this->getNodeTypeIds());
        if(!empty($this->getWheelchair())) $query->whereIn('nv.wheelchair', $this->getWheelchair());
        if(!empty($this->getWheelchairToilet())) $query->whereIn('nv.wheelchair', $this->getWheelchairToilet());
        if(!empty($this->getWheelchair())) $query->whereIn('nv.wheelchair', $this->getWheelchair());

        return $query;
    }

    /**
     * @return bool
     */
    public function isOnlyKnown()
    {
        return $this->onlyKnown;
    }

    /**
     * @param bool $onlyKnown
     */
    public function setOnlyKnown($onlyKnown)
    {
        $this->onlyKnown = $onlyKnown;
    }

    /**
     * @return Carbon
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param Carbon $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @return Carbon
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param Carbon $to
     */
    public function setTo($to)
    {
        $this->to = $to;
    }

    /**
     * @return int[]
     */
    public function getCategoryIds()
    {
        return $this->categoryIds;
    }

    /**
     * @param int[] $categoryIds
     */
    public function setCategoryIds($categoryIds)
    {
        $this->categoryIds = $categoryIds;
    }

    /**
     * @return int[]
     */
    public function getNodeTypeIds()
    {
        return $this->nodeTypeIds;
    }

    /**
     * @param int[] $nodeTypeIds
     */
    public function setNodeTypeIds($nodeTypeIds)
    {
        $this->nodeTypeIds = $nodeTypeIds;
    }

    /**
     * @return string[]
     */
    public function getWheelchair()
    {
        return $this->wheelchair;
    }

    /**
     * @param string[] $wheelchair
     */
    public function setWheelchair($wheelchair)
    {
        $this->wheelchair = $wheelchair;
    }

    /**
     * @return string[]
     */
    public function getWheelchairToilet()
    {
        return $this->wheelchairToilet;
    }

    /**
     * @param string[] $wheelchairToilet
     */
    public function setWheelchairToilet($wheelchairToilet)
    {
        $this->wheelchairToilet = $wheelchairToilet;
    }
}