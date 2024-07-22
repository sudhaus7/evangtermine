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

use ArbkomEKvW\Evangtermine\Domain\Model\EtKeys;

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
 * Settings utility
 */
class SettingsUtility
{
    /**
     * fetch etkey params from TypoScript/Flexform settings
     * @param array $settingsArray
     * @param EtKeys $etks
     */
    public function fetchParamsFromSettings(array $settingsArray, EtKeys $etks): void
    {
        foreach ($settingsArray as $key => $value) {
            if (substr($key, 0, 6) == 'etkey_' && $value != '') {
                $targetMethod = 'set' . ucfirst(substr($key, 6));
                if (method_exists($etks, $targetMethod)) {
                    $etks->{$targetMethod}($value);
                }
            }
        }

        // evaluate additional params field in flexform
        if (isset($settingsArray['evt_addprms']) && $settingsArray['evt_addprms'] != '') {
            $addprms = explode('&', $settingsArray['evt_addprms']);
            foreach ($addprms as $keyval) {
                if (str_contains($keyval, '=')) {
                    [$key, $value] = explode('=', trim($keyval));
                    $targetMethod = 'set' . ucfirst(trim($key));
                    if (method_exists($etks, $targetMethod)) {
                        $etks->{$targetMethod}(trim($value));
                    }
                }
            }
        }
    }

    /**
     * fetch etkey params from Request
     * @param array $requestParams
     * @param EtKeys $etks
     */
    public function fetchParamsFromRequest(array $requestParams, EtKeys $etks): void
    {
        foreach ($requestParams as $key => $value) {
            if (empty($value) || $value == '0' || $value == 'all') {
                continue;
            }
            $targetMethod = 'set' . ucfirst($key);
            if (method_exists($etks, $targetMethod)) {
                $etks->{$targetMethod}($value);
            }
        }
    }
}
