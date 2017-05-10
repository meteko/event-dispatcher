<?php

namespace Meteko\Events\Event;

use Meteko\Events\EventListener\EventListenerResolver;
use Meteko\Events\EventStore\AppliedEvent;
use Meteko\Events\EventStore\AppliedEventStorage;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Property\PropertyMapper;

/**
 * @Flow\Scope("singleton")
 */
class EventPublisher {

	/**
	 * @var EventTypeResolver
	 * @Flow\Inject
	 */
	protected $eventTypeResolver;

	/**
	 * @var EventListenerResolver
	 * @Flow\Inject
	 */
	protected $eventListenerResolver;

	/**
	 * @var AppliedEventStorage
	 * @Flow\Inject
	 */
	protected $appliedEventStorage;

	/**
	 * @var PropertyMapper
	 * @Flow\Inject
	 */
	protected $propertyMapper;

	/**
	 * @param EventInterface $event
	 */
	public function publish(EventInterface $event) {
		$eventType = $this->eventTypeResolver->getEventType($event);
		$metadata = [];
		$this->emitBeforePublishingEvent($event, $metadata);
		$payload = $this->propertyMapper->convert($event, 'array');

		$appliedEvent = new AppliedEvent($eventType, $payload, $metadata);
		$this->appliedEventStorage->commit($appliedEvent);

		foreach ($this->eventListenerResolver->getListenersByEventType($eventType) as $listener) {
			call_user_func($listener, $event);
		}

	}

	/**
	 * @Flow\Signal
	 * @param EventInterface $event
	 * @param array $metadata The events metadata, passed as reference so that it can be altered
	 * @return void
	 */
	protected function emitBeforePublishingEvent(EventInterface $event, array &$metadata)
	{
	}
}