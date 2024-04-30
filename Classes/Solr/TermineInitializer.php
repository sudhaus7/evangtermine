<?php

namespace ArbkomEKvW\Evangtermine\Solr;

use ApacheSolrForTypo3\Solr\IndexQueue\Initializer\AbstractInitializer;

class TermineInitializer extends AbstractInitializer
{
    public function initialize(): bool
    {
        return false;
    }
}
