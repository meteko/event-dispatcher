<?php

namespace Meteko\Events\EventStore;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\PersistenceManagerInterface;

/**
 * @Flow\Scope("singleton")
 */
class AppliedEventStorage {
	/**
	 * @var PersistenceManagerInterface
	 * @Flow\Inject
	 */
	protected $persistenceManager;

	/**
	 * @param AppliedEvent $appliedEvent
	 */
	public function commit(AppliedEvent $appliedEvent) {
		/*$this->persistenceManager->add($appliedEvent);
		$this->persistenceManager->whitelistObject($appliedEvent);
		$this->persistenceManager->persistAll();*/
	}
}