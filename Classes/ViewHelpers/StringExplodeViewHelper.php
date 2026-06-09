<?php

namespace ArbkomEKvW\Evangtermine\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class StringExplodeViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('string', 'string', '', true);
        $this->registerArgument('separator', 'string', '', false);
    }

    public function render(): array
    {
        return explode($this->arguments['separator'] ?: ',', (string) $this->arguments['string']);
    }
}
