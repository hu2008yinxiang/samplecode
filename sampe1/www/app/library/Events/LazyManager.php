<?php
namespace Events
{

    class LazyManager extends \Phalcon\Mvc\User\Component implements \Phalcon\Events\ManagerInterface
    {

        private $orgEventsManager = null;

        private $lazyListeners = array();

        public function __construct()
        {
            $this->orgEventsManager = new \Phalcon\Events\Manager();
        }

        /**
         * Attach a listener to the events manager
         *
         * @param string $eventType            
         * @param object|callable $handler            
         * @param int $priority            
         */
        public function attach($eventType, $handler)
        {
            $eventsManager = $this;
            if (is_array($handler)) {
                // $lz = function (\Phalcon\Events\Event $event, $component, $data = null, $cancelable = false) use($eventsManager, $handler)
                // {
                // $eventsManager->eventFired($handler, $event, $component, $data, $cancelable);
                // };
                $this->lazyListeners[$eventType][] = $handler;
                // $this->orgEventsManager->attach($eventType, $handler);
                // $di = $this->getDI();
                /*
                 * $lz = null;
                 * switch ($handler['type']) {
                 * case 'service':
                 * $name = $handler['name'];
                 * $lz = function (\Phalcon\Events\Event $event, $component, $data) use($di, $name)
                 * {
                 * $
                 * };
                 * break;
                 * case 'compoent':
                 * break;
                 * case 'instance':
                 * break;
                 * case 'method':
                 * break;
                 * case 'function':
                 * break;
                 * case 'static':
                 * break;
                 * }
                 * if (! is_null($lz)) {
                 * $this->orgEventsManager->attach($eventType, $lz);
                 * }
                 */
                return;
            }
            $this->orgEventsManager->attach($eventType, $handler);
        }

        /**
         * Set if priorities are enabled in the EventsManager
         *
         * @param boolean $enablePriorities            
         */
        public function enablePriorities($enablePriorities)
        {
            $this->orgEventsManager->enablePriorities($enablePriorities);
        }

        /**
         * Returns if priorities are enabled
         *
         * @return boolean
         */
        public function arePrioritiesEnabled()
        {
            return $this->orgEventsManager->arePrioritiesEnabled();
        }

        /**
         * Tells the event manager if it needs to collect all the responses returned by every
         * registered listener in a single fire
         *
         * @param boolean $collect            
         */
        public function collectResponses($collect)
        {
            $this->orgEventsManager->collectResponses($collect);
        }

        /**
         * Check if the events manager is collecting all all the responses returned by every
         * registered listener in a single fire
         */
        public function isCollecting()
        {
            return $this->orgEventsManager->isCollecting();
        }

        /**
         * Returns all the responses returned by every handler executed by the last 'fire' executed
         *
         * @return array
         */
        public function getResponses()
        {
            return $this->orgEventsManager->getResponses();
        }

        /**
         * Removes all events from the EventsManager
         *
         * @param string $type            
         */
        public function detachAll($type = null)
        {
            $this->orgEventsManager->detachAll($type);
        }

        /**
         * Internal handler to call a queue of events
         *
         * @param \SplPriorityQueue $queue            
         * @param \Phalcon\Events\Event $event            
         * @return mixed
         */
        public function fireQueue($queue, $event)
        {
            return $this->orgEventsManager->fireQueue($queue, $event);
        }

        /**
         * Fires an event in the events manager causing that active listeners be notified about it
         *
         * <code>
         * $eventsManager->fire('db', $connection);
         * </code>
         *
         * @param string $eventType            
         * @param object $source            
         * @param mixed $data            
         * @param int $cancelable            
         * @return mixed
         */
        public function fire($eventType, $source, $data = null)
        {
            $eventParts = explode(':', $eventType);
            $eventParts = $eventParts[0];
            // if ($eventParts != $eventType || isset($this->lazyListeners[$eventType]) && is_array($this->lazyListeners[$eventType])) {
            $handler = null;
            while (($handler = @array_shift($this->lazyListeners[$eventType]))) {
                switch ($handler['type']) {
                    case 'service':
                        $this->orgEventsManager->attach($eventType, $this->getDI()
                            ->get($handler['name']));
                        break;
                    case 'instance':
                        $this->orgEventsManager->attach($eventType, new $handler['className']());
                        break;
                }
            }
            // }
            
            $handler = null;
            while (($handler = @array_shift($this->lazyListeners[$eventParts]))) {
                switch ($handler['type']) {
                    case 'service':
                        $this->orgEventsManager->attach($eventParts, $this->getDI()
                            ->get($handler['name']));
                        break;
                    case 'instance':
                        $this->orgEventsManager->attach($eventParts, new $handler['className']());
                        break;
                }
            }
            
            return $this->orgEventsManager->fire($eventType, $source, $data);
        }

        /**
         * Check whether certain type of event has listeners
         *
         * @param string $type            
         * @return boolean
         */
        public function hasListeners($type)
        {
            return $this->orgEventsManager->hasListeners($type);
        }

        /**
         * Returns all the attached listeners of a certain type
         *
         * @param string $type            
         * @return array
         */
        public function getListeners($type)
        {
            return $this->orgEventsManager->getListeners($type);
        }

        public function dettachAll($type = null)
        {
            $this->orgEventsManager->detachAll($type);
        }
    }
}