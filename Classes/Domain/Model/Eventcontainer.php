<?php

/*
 * This file is part of the TYPO3 project.
 *
 * @author Frank Berger <fberger@sudhaus7.de>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace ArbkomEKvW\Evangtermine\Domain\Model;

use Exception;
use SimpleXMLElement;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015 Christoph Roth <christoph.roth@lka.ekvw.de>, Evangelische Kirche von Westfalen
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Eventcontainer
 */
class Eventcontainer extends AbstractEntity implements EventcontainerInterface
{
    /**
     * itemsInResult
     *
     * @var int
     */
    private int $numberOfItems = 0;

    /**
     * items array of SimpleXML objects
     *
     * @var array
     */
    private array $items = [];

    /**
     * content of the <meta> tag in XML result
     */
    private ?SimpleXMLElement $metaData;

    /**
     * detail-tag, only present in single view
     */
    private $detail;

    /**
     * returns number of items in container
     *
     * @return int
     */
    public function getNumberOfItems(): int
    {
        return $this->numberOfItems;
    }

    /**
     * sets the number of items in result
     * @param int $numItems
     */
    public function setNumberOfItems($numItems)
    {
        $this->numberOfItems = $numItems;
    }

    /**
     * returns items array
     * @return array $items
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * sets items array
     *
     * @param array $items
     */
    public function setItems(array $items)
    {
        $this->items = $items;
    }

    public function getMetaData(): ?SimpleXMLElement
    {
        return $this->metaData;
    }

    public function setMetaData($metaData)
    {
        $this->metaData = $metaData ?? null;
    }

    /**
     * @return mixed
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * @param mixed $detail
     */
    public function setDetail($detail): void
    {
        $this->detail = $detail;
    }

    /**
     * transform XML into array and load item attributes
     * @param string $xmlString
     */
    public function loadXML($xmlString)
    {
        $xmlString = trim($xmlString);

        if (!$xmlString || substr($xmlString, 0, 5) != '<?xml') {
            $this->reset();
        } else {
            try {
                $xmlSimple = new SimpleXMLElement($xmlString);
            } catch (Exception $e) {
                $this->reset();
                return;
            }

            // extract event data
            $this->setItems($xmlSimple->xpath('//Veranstaltung'));
            $this->setNumberOfItems(count($this->getItems()));

            // extract meta data
            $this->setMetaData($xmlSimple->Export->meta);

            // extract detail
            $this->setDetail($xmlSimple->Export->detail->item);
        }
    }

    /**
     * set values to empty
     */
    private function reset()
    {
        $this->setNumberOfItems(0);
        $this->setItems([]);
        $this->setMetaData(null);
    }
}
