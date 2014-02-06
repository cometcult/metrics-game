<?php

namespace TheCometCult\Metrics;

interface MetricsCollectorInterface
{
    /**
     * @param array $repos - array of author => repos to collect
     *
     * @return array metrics
     */
    public function collect(array $repos);
}