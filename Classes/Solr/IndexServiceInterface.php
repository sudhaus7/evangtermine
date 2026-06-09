<?php

declare(strict_types=1);

namespace ArbkomEKvW\Evangtermine\Solr;

interface IndexServiceInterface
{
    public function indexForAllSites(): void;
}
