<?php

namespace Laminas\EventManager;

use ArrayObject;

use function array_keys;
use function array_merge;
use function array_unique;
use function get_debug_type;
use function is_callable;
use function is_string;
use function krsort;
use function sprintf;

/**
 * Event manager: notification system
 *
 * Use the EventManager when you want to create a per-instance notification
 * system for your objects.
 */
class EventManager implements EventManagerInterface
{
    /**
     * Subscribed events and their listeners
     *
     * STRUCTURE:
     * [
     *     <string name> => [
     *         <int priority> => [
     *             0 => [<callable listener>, ...]
     *         ],
     *         ...
     *     ],
     *     ...
     * ]
     *
     * NOTE:
     * This structure helps us to reuse the list of listeners
     * instead of first iterating over it and generating a new one
     * -> In result it improves performance by up to 25% even if it looks a bit strange
     *
     * @var array<string, array<int, array{0: list<callable>}>>
     */
    protected $events = [];

    /** @var EventInterface Prototype to use when creating an event at trigger(). */
    protected $eventPrototype;

    /**
     * Identifiers, used to pull shared signals from SharedEventManagerInterface instance
     *
     * @var array
     */
    protected $identifiers = [];

    /**
     * Shared event manager
     *
     * @var null|SharedEventManagerInterface
     */
    protected $sharedManager;

    /**
     * Constructor
     *
     * Allows optionally specifying identifier(s) to use to pull signals from a
     * SharedEventManagerInterface.
     */
    public function __construct(?SharedEventManagerInterface $sharedEventManager = null, array $identifiers = [])
    {
        if ($sharedEventManager) {
            $this->sharedManager = $sharedEventManager;
            $this->setIdentifiers($identifiers);
        }

        $this->eventPrototype = new Event();
    }

    /**
     * @inheritDoc
     */
    public function setEventPrototype(EventInterface $prototype)
    {
        $this->eventPrototype = $prototype;
    }

    /**
     * Retrieve the shared event manager, if composed.
     *
     * @return null|SharedEventManagerInterface $sharedEventManager
     */
    public function getSharedManager()
    {
        return $this->sharedManager;
    }

    /**
     * @inheritDoc
     */
    public function getIdentifiers()
    {
        return $this->identifiers;
    }

    /**
     * @inheritDoc
     */
    public function setIdentifiers(array $identifiers)
    {
        $this->identifiers = array_unique($identifiers);
    }

    /**
     * @inheritDoc
     */
    public function addIdentifiers(array $identifiers)
    {
        $this->identifiers = array_unique(array_merge(
            $this->identifiers,
            $identifiers
        ));
    }

    /**
     * @inheritDoc
     */
    public function trigger($eventName, $target = null, $argv = [])
    {
        $event = clone $this->eventPrototype;
        $event->setName($eventName);

        if ($target !== null) {
            $event->setTarget($target);
        }

        if ($argv !== []) {
            $event->setParams($argv);
        }

        return $this->triggerListeners($event);
    }

    /**
     * @inheritDoc
     */
    public function triggerUntil(callable $callback, $eventName, $target = null, $argv = [])
    {
        $event = clone $this->eventPrototype;
        $event->setName($eventName);

        if ($target !== null) {
            $event->setTarget($target);
        }

        if ($argv !== []) {
            $event->setParams($argv);
        }

        return $this->triggerListeners($event, $callback);
    }

    /**
     * @inheritDoc
     */
    public function triggerEvent(EventInterface $event)
    {
        return $this->triggerListeners($event);
    }

    /**
     * @inheritDoc
     */
    public function triggerEventUntil(callable $callback, EventInterface $event)
    {
        return $this->triggerListeners($event, $callback);
    }

    /**
     * @inheritDoc
     */
    public function attach($eventName, callable $listener, $priority = 1)
    {
        if (! is_string($eventName)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects a string for the event; received %s',
                __METHOD__,
                get_debug_type($eventName),
            ));
        }

        $this->events[$eventName][(int) $priority][0][] = $listener;
        return $listener;
    }

    /**
     * @inheritDoc
     * @throws Exception\InvalidArgumentException For invalid event types.
     */
    public function detach(callable $listener, $eventName = null, $force = false)
    {
        // If event is wildcard, we need to iterate through each listeners
        if (null === $eventName || ('*' === $eventName && ! $force)) {
            foreach (array_keys($this->events) as $eventName) {
                $this->detach($listener, $eventName, true);
            }
            return;
        }

        if (! is_string($eventName)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects a string for the event; received %s',
                __METHOD__,
                get_debug_type($eventName),
            ));
        }

        if (! isset($this->events[$eventName])) {
            return;
        }

        foreach ($this->events[$eventName] as $priority => $listeners) {
            foreach ($listeners[0] as $index => $evaluatedListener) {
                if ($evaluatedListener !== $listener) {
                    continue;
                }

                // Found the listener; remove it.
                unset($this->events[$eventName][$priority][0][$index]);

                // If the queue for the given priority is empty, remove it.
                if (empty($this->events[$eventName][$priority][0])) {
                    unset($this->events[$eventName][$priority]);
                    break;
                }
            }
        }

        // If the queue for the given event is empty, remove it.
        if (empty($this->events[$eventName])) {
            unset($this->events[$eventName]);
        }
    }

    /**
     * @inheritDoc
     */
    public function clearListeners($eventName)
    {
        if (isset($this->events[$eventName])) {
            unset($this->events[$eventName]);
        }
    }

    /**
     * Prepare arguments
     *
     * Use this method if you want to be able to modify arguments from within a
     * listener. It returns an ArrayObject of the arguments, which may then be
     * passed to trigger().
     *
     * @template Tk of array-key
     * @template Tv
     * @param  array<Tk, Tv> $args
     * @return ArrayObject<Tk, Tv>
     */
    public function prepareArgs(array $args)
    {
        return new ArrayObject($args);
    }

    /**
     * Trigger listeners
     *
     * Actual functionality for triggering listeners, to which trigger() delegate.
     *
     * @return ResponseCollection
     */
    protected function triggerListeners(EventInterface $event, ?callable $callback = null)
    {
        $name = $event->getName();

        if ($name === null || $name === '' || $name === '0') {
            throw new Exception\RuntimeException('Event is missing a name; cannot trigger!');
        }

        if (isset($this->events[$name])) {
            $listOfListenersByPriority = $this->events[$name];

            if (isset($this->events['*'])) {
                foreach ($this->events['*'] as $priority => $listOfListeners) {
                    $listOfListenersByPriority[$priority][] = $listOfListeners[0];
                }
            }
        } elseif (isset($this->events['*'])) {
            $listOfListenersByPriority = $this->events['*'];
        } else {
            $listOfListenersByPriority = [];
        }

        if ($this->sharedManager) {
            foreach ($this->sharedManager->getListeners($this->identifiers, $name) as $priority => $listeners) {
                $listOfListenersByPriority[$priority][] = $listeners;
            }
        }

        // Sort by priority in reverse order
        krsort($listOfListenersByPriority);

        // Initial value of stop propagation flag should be false
        $event->stopPropagation(false);

        // Execute listeners
        $responses = new ResponseCollection();
        foreach ($listOfListenersByPriority as $listOfListeners) {
            foreach ($listOfListeners as $listeners) {
                foreach ($listeners as $listener) {
                    $response = $listener($event);
                    $responses->push($response);

                    // If the event was asked to stop propagating, do so
                    if ($event->propagationIsStopped()) {
                        $responses->setStopped(true);
                        return $responses;
                    }

                    // If the result causes our validation callback to return true,
                    // stop propagation
                    if (is_callable($callback) && $callback($response)) {
                        $responses->setStopped(true);
                        return $responses;
                    }
                }
            }
        }

        return $responses;
    }
}
