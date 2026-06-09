<?php

declare(strict_types=1);

namespace ArbkomEKvW\Evangtermine\Backend\FieldControl;

use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\Page\PageRenderer;

class ToggleAllFilters extends AbstractNode
{
    public function __construct(private readonly PageRenderer $pageRenderer)
    {
    }
    public function render(): array
    {
        $pageRenderer = $this->pageRenderer;

        if (!empty($pageRenderer)) {
            $pageRenderer->addJsFile('EXT:evangtermine/Resources/Public/JavaScript/Backend/ToggleAllFilters.js');
        }

        return [
            'html' => '',
        ];
    }
}
