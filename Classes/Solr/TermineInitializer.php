<?php

namespace ArbkomEKvW\Evangtermine\Solr;

use ApacheSolrForTypo3\Solr\IndexQueue\Initializer\AbstractInitializer;

class TermineInitializer extends AbstractInitializer
{
    // The ImportEventsCommand saves the events with pid=0 therefore we return always 0 as the only pid
    protected function getPages(): array
    {
        return [0];
    }
}
