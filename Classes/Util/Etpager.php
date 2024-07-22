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

namespace ArbkomEKvW\Evangtermine\Util;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015,2021 Christoph Roth <christoph.roth@ekvw.de>, Evangelische Kirche von Westfalen
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
 * Etpager
 */
class Etpager
{
    // pager bar size (number of page fields to click on)
    public const pgrbSize = 5;

    // Steps between fields in self::pgrbSize
    public const pgrbSteps = 4;

    // Steps from pagebar border to pagebar center
    public const pgrbBorderToCenter = 2;

    /**
     * pager data for display in view
     * @var array
     */
    protected array $pgr;

    /**
     * Build Pager datastructure
     * @param mixed $totalItems
     * @param mixed $itemsPerPage
     * @param $currentPage
     */
    public function up($totalItems, $itemsPerPage, $currentPage): void
    {
        // Number of Events in result
        if (is_object($totalItems)) {
            $totalItems = (int)($totalItems->__toString());
        } else {
            $totalItems = (int)$totalItems;
        }

        $itemsPerPage = (int)$itemsPerPage ?: 20;

        // Number of pages in pager
        $this->pgr['pages'] = ceil($totalItems / $itemsPerPage);

        // Current page
        $this->pgr['current'] = $currentPage ?? 1;

        $this->getPagerBarLimits();
        $this->getBrowserTriggers();

        // make values for pager bar
        $this->pgr['pgrBarValues'] = [];
        for ($i = $this->pgr['pgrBarBegin']; $i <= $this->pgr['pgrBarEnd']; $i++) {
            $this->pgr['pgrBarValues'][] = $i;
        }
    }

    private function getPagerBarLimits(): void
    {
        // current page is near end of pagelist
        if (($this->pgr['pages'] - self::pgrbSize) < $this->pgr['current']) {
            $this->pgr['pgrBarEnd'] = $this->pgr['pages'];

            // current page is near beginning of pagelist
        } elseif (((int)$this->pgr['current'] + self::pgrbBorderToCenter) <= self::pgrbSize) {
            $this->pgr['pgrBarEnd'] = self::pgrbSize;

            // current page is somewhere in the middle
        } else {
            $this->pgr['pgrBarEnd'] = (int)$this->pgr['current'] + self::pgrbBorderToCenter;
        }

        // now the begin limit
        if (($this->pgr['pgrBarEnd'] - self::pgrbSteps) <= 1) {
            $this->pgr['pgrBarBegin'] = 1;
        } else {
            $this->pgr['pgrBarBegin'] = (int)$this->pgr['pgrBarEnd'] - self::pgrbSteps;
        }
    }

    private function getBrowserTriggers(): void
    {
        if ($this->pgr['pgrBarBegin'] >= self::pgrbBorderToCenter) {
            $this->pgr['lBrowser'] = 1;
            $this->pgr['lBrowserNext'] = ($this->pgr['current'] > 1) ? $this->pgr['current'] - 1 : 1;
        } else {
            $this->pgr['lBrowser'] = 0;
        }

        if ($this->pgr['current'] < $this->pgr['pgrBarEnd']) {
            $this->pgr['rBrowser'] = 1;
            $this->pgr['rBrowserNext'] = (int)$this->pgr['current'] + 1;
        } else {
            $this->pgr['rBrowser'] = 0;
        }
    }

    /**
     * @return array
     */
    public function getPgr(): array
    {
        return $this->pgr;
    }
}
