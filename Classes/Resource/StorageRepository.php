<?php

namespace ArbkomEKvW\Evangtermine\Resource;

use TYPO3\CMS\Core\Resource\ResourceStorage;

class StorageRepository extends \TYPO3\CMS\Core\Resource\StorageRepository
{
    /**
     * From TYPO3 11
     *
     * @return ResourceStorage|null
     */
    public function getDefaultStorage(): ?ResourceStorage
    {
        $allStorages = $this->findAll();
        foreach ($allStorages as $storage) {
            if ($storage->isDefault()) {
                return $storage;
            }
        }
        return null;
    }
}
