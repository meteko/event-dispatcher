<?php

namespace Meteko\Events\Event;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Reflection\ReflectionService;
use Meteko\Events\Event\EventInterface;
use Meteko\Events\Exception\Exception;
use Neos\Utility\TypeHandling;

/**
 * @Flow\Scope("singleton")
 */
class EventTypeResolver {

	/**
	 * @var ObjectManagerInterface
	 * @Flow\Inject
	 */
	protected $objectManager;

	/**
	 * @var []
	 */
	protected $mapping = [];

	/**
	 * @var array
	 */
	protected $reversedMapping = [];

	public function initializeObject() {
		$this->mapping = self::mapEventTypes($this->objectManager);
		$this->reversedMapping = array_flip($this->mapping);

	}

	/**
	 * Return the event type for the given Event object
	 *
	 * @param EventInterface $event
	 * @return string
	 */
	public function getEventType(EventInterface $event): string
	{
		$className = TypeHandling::getTypeForValue($event);
		return $this->getEventTypeByClassName($className);
	}
	/**
	 * Return the event type for the given Event classname
	 *
	 * @param string $className
	 * @return string
	 * @throws Exception
	 */
	public function getEventTypeByClassName(string $className): string
	{
		if (!isset($this->mapping[$className])) {
			throw new Exception(sprintf('Event Type not found for class name "%s"', $className), 1476249954);
		}
		return $this->mapping[$className];
	}

	/**
	 * Return the event classname for the given event type
	 *
	 * @param string $eventType
	 * @return string
	 */
	public function getEventClassNameByType(string $eventType): string
	{
		return $this->reversedMapping[$eventType];
	}

	/**
	 * Return the event short name for the given Event object
	 *
	 * @param EventInterface $event
	 * @return string
	 */

	public function getEventShortType(EventInterface $event): string
	{
		$type = explode(':', $this->getEventType($event));
		return end($type);
	}

	/**
	 * Return the event short name for the given Event classname
	 *
	 * @param string $className
	 * @return string
	 */
	public function getEventShortTypeByClassName(string $className): string
	{
		$type = explode(':', $this->getEventTypeByClassName($className));
		return end($type);
	}


	/**
	 * @param ObjectManagerInterface $objectManager
	 * @return array
	 * @throws Exception
	 * @Flow\CompileStatic
	 */
	protected static function mapEventTypes(ObjectManagerInterface $objectManager) {
		$buildEventType = function ($eventClassName) use ($objectManager) {
			$packageKey = $objectManager->getPackageKeyByObjectName($eventClassName);
			if ($packageKey === false) {
				throw new Exception(sprintf('Could not determine package key from object name "%s"', $eventClassName), 1478088597);
			}
			$shortEventClassName = (new \ReflectionClass($eventClassName))->getShortName();
			return $packageKey . ':' . $shortEventClassName;
		};

		$mapping = [];
		/** @var ReflectionService $reflectionService */
		$reflectionService = $objectManager->get(ReflectionService::class);
		foreach ($reflectionService->getAllImplementationClassNamesForInterface(EventInterface::class) as $eventClassName) {
			$eventTypeIdentifier = $buildEventType($eventClassName);
			if (in_array($eventTypeIdentifier, $mapping)) {
				throw new Exception(sprintf('Duplicate event type "%s" mapped from "%s".', $eventTypeIdentifier, $eventClassName), 1474710799);
			}
			$mapping[$eventClassName] = $eventTypeIdentifier;
		}

		return $mapping;

	}

}