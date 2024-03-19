<?php

/*
 * This file is part of the TYPO3 project.
 * (c) 2022 B-Factor GmbH
 *          Sudhaus7
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 * The TYPO3 project - inspiring people to share!
 * @copyright 2022 B-Factor GmbH https://b-factor.de/
 * @author Frank Berger <fberger@b-factor.de>
 * @author Daniel Simon <dsimon@b-factor.de>
 */

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
