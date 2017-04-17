<?php


namespace BathHacked;


use Carbon\Carbon;

class StatsBuilder
{
    /**
     * @var array
     */
    protected $stats;

    /**
     * StatsBuilder constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return array
     * @todo Make this scaleable to larger node sets
     */
    public function getStats()
    {
        $this->stats = [];

        $query = new NodeQuery();

        $query->setOnlyKnown(false);
        $this->gather('all', $query->getNodes());

        $query->setOnlyKnown(true);
        $this->gather('known', $query->getNodes());

        $this->stats['latest'] = $query->getLatest();

        $now = new Carbon();

        $query->setFrom($now->copy()->subWeeks(1));
        $this->gather('week', $query->getNodes());

        $query->setFrom($now->copy()->subMonths(1));
        $this->gather('month', $query->getNodes());

        $query->setFrom($now->copy()->subYears(1));
        $this->gather('year', $query->getNodes());

        return $this->stats;
    }

    /**
     * @param $type
     * @param $nodes
     */
    protected function gather($type, $nodes)
    {
        foreach($nodes as $node)
        {
            if(!isset($this->stats['total'][$type]))
            {
                $this->stats['total'][$type] = 0;
            }
            $this->stats['total'][$type]++;

            if(!isset($this->stats['wheelchair'][$node->wheelchair][$type]))
            {
                $this->stats['wheelchair'][$node->wheelchair][$type] = 0;
            }
            $this->stats['wheelchair'][$node->wheelchair][$type]++;

            if(!isset($this->stats['wheelchair_toilet'][$node->wheelchair_toilet][$type]))
            {
                $this->stats['wheelchair_toilet'][$node->wheelchair_toilet][$type] = 0;
            }
            $this->stats['wheelchair_toilet'][$node->wheelchair_toilet][$type]++;

            if(!isset($this->stats['category'][$node->category_name][$type]))
            {
                $this->stats['category'][$node->category_name][$type] = 0;
            }
            $this->stats['category'][$node->category_name][$type]++;

            if(!isset($this->stats['category'][$node->category_name]['wheelchair'][$node->wheelchair][$type]))
            {
                $this->stats['category'][$node->category_name]['wheelchair'][$node->wheelchair][$type] = 0;
            }
            $this->stats['category'][$node->category_name]['wheelchair'][$node->wheelchair][$type]++;

            if(!isset($this->stats['category'][$node->category_name]['wheelchair_toilet'][$node->wheelchair_toilet][$type]))
            {
                $this->stats['category'][$node->category_name]['wheelchair_toilet'][$node->wheelchair_toilet][$type] = 0;
            }
            $this->stats['category'][$node->category_name]['wheelchair_toilet'][$node->wheelchair_toilet][$type]++;
        }

        ksort($this->stats['category']);
    }
}