<?php

namespace ArbkomEKvW\Evangtermine\Solr;

use ApacheSolrForTypo3\Solr\Domain\Index\Queue\GarbageRemover\AbstractStrategy;
use ApacheSolrForTypo3\Solr\System\Solr\SolrConnection;

class EventStrategy extends AbstractStrategy
{
    protected bool $myEnableCommitsSetting = true;

    /**
     * @var SolrConnection[]
     */
    protected array  $mySolrConnections = [];
    protected string $mySiteHash = '';

    protected function removeGarbageOfByStrategy(string $table, int $uid): void
    {
        $this->deleteRecordInAllSolrConnections(
            'tx_evangtermine_domain_model_event',
            $uid,
            $this->mySolrConnections,
            $this->mySiteHash,
            $this->myEnableCommitsSetting
        );
    }

    public function setMyEnableCommitsSetting(bool $myEnableCommitsSetting): void
    {
        $this->myEnableCommitsSetting = $myEnableCommitsSetting;
    }

    public function setMySolrConnections(array $mySolrConnections): void
    {
        $this->mySolrConnections = $mySolrConnections;
    }

    public function setMySiteHash(string $mySiteHash): void
    {
        $this->mySiteHash = $mySiteHash;
    }
}
