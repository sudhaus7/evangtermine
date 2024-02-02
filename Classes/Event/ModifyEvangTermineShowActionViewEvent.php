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

namespace ArbkomEKvW\Evangtermine\Event;

use ArbkomEKvW\Evangtermine\Domain\Model\Event;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

final class ModifyEvangTermineShowActionViewEvent
{
    protected ViewInterface $view;
    protected Event $event;

    public function __construct(ViewInterface $view, Event $event)
    {
        $this->view = $view;
        $this->event = $event;
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
