<?php

namespace ArbkomEKvW\Evangtermine\Event;

use ArbkomEKvW\Evangtermine\Domain\Model\Event;
use TYPO3Fluid\Fluid\View\ViewInterface;

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
