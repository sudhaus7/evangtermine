<?php

namespace ArbkomEKvW\Evangtermine\Solr;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class DummyIndexService implements IndexServiceInterface, LoggerAwareInterface
{
    public function setLogger(LoggerInterface $logger): void
    {
    }

    public function indexForAllSites(): void
    {
    }
}
