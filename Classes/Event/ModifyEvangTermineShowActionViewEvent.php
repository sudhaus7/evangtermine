<?php

declare(strict_types=1);

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
namespace ArbkomEKvW\Evangtermine\Event;

use ArbkomEKvW\Evangtermine\Domain\Model\Event;
use TYPO3\CMS\Core\View\ViewInterface;

final class ModifyEvangTermineShowActionViewEvent
{
    public function __construct(protected ViewInterface $view, protected Event $event)
    {
    }

    public function getView(): ViewInterface
    {
        return $this->view;
    }

    public function setView(ViewInterface $view): void
    {
        $this->view = $view;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }
}
