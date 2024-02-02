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

namespace ArbkomEKvW\Evangtermine\ViewHelpers;

use ArbkomEKvW\Evangtermine\Util\FieldMapping;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

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

class LabelViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('key', 'string', '', true);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): string
    {
        $eventTCA = $GLOBALS['TCA']['tx_evangtermine_domain_model_event']['columns'] ?? null;
        if (empty($eventTCA)) {
            return '';
        }

        $fieldMapping = GeneralUtility::makeInstance(FieldMapping::class);
        $fields = $fieldMapping->getFields();

        $key = array_search($arguments['key'] ?? '', $fields);
        $label = $eventTCA[$key]['label'] ?? '';
        if (empty($label)) {
            return '';
        }
        return $label;
    }
}
