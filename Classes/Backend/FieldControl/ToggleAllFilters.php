<?php

namespace ArbkomEKvW\Evangtermine\Backend\FieldControl;

use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ToggleAllFilters extends AbstractNode
{

    public function render(): array
    {
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);

        if (!empty($pageRenderer)) {
            $pageRenderer->loadRequireJsModule('TYPO3/CMS/Evangtermine/Backend/ToggleAllFilters');
        }

        return [
            'html' => ''
        ];
    }
}
