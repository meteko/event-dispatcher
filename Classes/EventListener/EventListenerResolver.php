<?php

namespace Meteko\Events\EventListener;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Reflection\ReflectionService;
use Meteko\Events\Event\EventInterface;
use Meteko\Events\Exception\Exception;
use Neos\Utility\TypeHandling;
use Meteko\Events\Event\EventTypeResolver;

/**
 * @Flow\Scope("singleton")
 */
class EventListenerResolver {

	/**
	 * @var ObjectManagerInterface
	 */
	private $objectManager;

	/**
	 * @var EventTypeResolver
	 */
	private $eventTypeResolver;

	/**
	 * @var array in the format ['<eventClassName>' => ['<listenerClassName>' => '<listenerMethodName>', '<listenerClassName2>' => '<listenerMethodName2>', ...]]
	 */
	private $mapping = [];
	/**
	 * @param ObjectManagerInterface $objectManager
	 * @param EventTypeResolver $eventTypeService
	 */
	public function __construct(ObjectManagerInterface $objectManager, EventTypeResolver $eventTypeService)
	{
		$this->objectManager = $objectManager;
		$this->eventTypeResolver = $eventTypeService;
	}
	/**
	 * Register event listeners based on annotations
	 */
	public function initializeObject()
	{
		$this->mapping = self::mapEventListeners($this->objectManager);
	}

	/**
	 * Returns all known event listeners
	 *
	 * @return \callable[]
	 */
	public function getListeners(): array
	{
		$listeners = [];
		foreach ($this->mapping as $eventClassName => $listenersForEventType) {
			array_walk($this->mapping[$eventClassName], function ($listenerMethodName, $listenerClassName) use (&$listeners) {
				$listeners[] = [$this->objectManager->get($listenerClassName), $listenerMethodName];
			});
		}
		return $listeners;
	}

	/**
	 * Returns event listeners for the given event type
	 *
	 * @param string $eventType
	 * @return \callable[]
	 */
	public function getListenersByEventType(string $eventType): array
	{
		$eventClassName = $this->eventTypeResolver->getEventClassNameByType($eventType);
		if (!isset($this->mapping[$eventClassName])) {
			return [];
		}
		$listeners = [];
		array_walk($this->mapping[$eventClassName], function ($listenerMethodName, $listenerClassName) use (&$listeners) {
			$listeners[] = [$this->objectManager->get($listenerClassName), $listenerMethodName];
		});
		return $listeners;
	}

	/**
	 * Detects and collects all existing event listener classes
	 *
	 * @param ObjectManagerInterface $objectManager
	 * @return array
	 * @throws Exception
	 * @Flow\CompileStatic
	 */
	protected static function mapEventListeners(ObjectManagerInterface $objectManager): array
	{
		$listeners = [];
		/** @var ReflectionService $reflectionService */
		$reflectionService = $objectManager->get(ReflectionService::class);
		foreach ($reflectionService->getAllImplementationClassNamesForInterface(EventListenerInterface::class) as $listenerClassName) {
			foreach (get_class_methods($listenerClassName) as $listenerMethodName) {
				preg_match('/^when[A-Z].*$/', $listenerMethodName, $matches);
				if (!isset($matches[0])) {
					continue;
				}
				$parameters = array_values($reflectionService->getMethodParameters($listenerClassName, $listenerMethodName));
				if (!isset($parameters[0])) {
					throw new Exception(sprintf('Invalid listener in %s::%s the method signature is wrong, must accept an EventInterface and optionally a RawEvent', $listenerClassName, $listenerMethodName), 1472500228);
				}
				$eventClassName = $parameters[0]['class'];
				if (!$reflectionService->isClassImplementationOf($eventClassName, EventInterface::class)) {
					throw new Exception(sprintf('Invalid listener in %s::%s the method signature is wrong, the first parameter should be an implementation of EventInterface but it expects an instance of "%s"', $listenerClassName, $listenerMethodName, $eventClassName), 1472504443);
				}
				$expectedMethodName = 'when' . (new \ReflectionClass($eventClassName))->getShortName();
				if ($expectedMethodName !== $listenerMethodName) {
					throw new Exception(sprintf('Invalid listener in %s::%s the method name is expected to be "%s"', $listenerClassName, $listenerMethodName, $expectedMethodName), 1476442394);
				}
				if (!isset($listeners[$eventClassName])) {
					$listeners[$eventClassName] = [];
				}
				$listeners[$eventClassName][$listenerClassName] = $listenerMethodName;
			}
		}
		return $listeners;
	}

}