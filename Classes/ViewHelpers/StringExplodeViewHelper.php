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

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class StringExplodeViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('string', 'string', '', true);
        $this->registerArgument('separator', 'string', '', false);
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): array
    {
        return explode($arguments['separator'] ?: ',', $arguments['string']);
    }
}
