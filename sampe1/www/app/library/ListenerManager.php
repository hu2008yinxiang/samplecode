<?php

class ListenerManager extends \Phalcon\Mvc\User\Component
{

    protected function getListeners()
    {
        return array(
            '\Listeners\UserListener'
        );
    }

    public function setEventsManager($eventsManager)
    {
        parent::setEventsManager($eventsManager);
        $this->registerListeners();
    }

    protected function registerListeners()
    {
        foreach ($this->getListeners() as $name) {
            $listener = new $name();
            $listener->setDI($this->getDI());
            $listener->setEventsManager($this->getEventsManager());
            $this->getEventsManager()->attach($listener->getCatalog(), $listener);
        }
    }
}