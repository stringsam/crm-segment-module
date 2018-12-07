<?php

namespace Crm\SegmentModule;

class VisualSegmenterConfig
{
    private $host;

    private $key;

    public function __construct($host, $key)
    {
        $this->host = $host;
        $this->key = $key;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getKey()
    {
        return $this->key;
    }
}
