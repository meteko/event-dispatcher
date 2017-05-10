<?php

namespace Meteko\Events\EventStore;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class AppliedEvent {

	/**
	 * @var string
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="bigint", options={"unsigned"=true})
	 */
	protected $id;

	/**
	 * @var \DateTime
	 */
	protected $recorded;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var array
	 * @ORM\Column(type="flow_json_array")
	 */
	protected $payload = [];

	/**
	 * @var array
	 * @ORM\Column(type="flow_json_array")
	 */
	protected $metadata = [];

	/**
	 * AppliedEvent constructor.
	 * @param string $type
	 * @param array $payload
	 * @param array $metadata
	 */
	public function __construct($type, array $payload, array $metadata)
	{
		$this->recorded = new \DateTime();
		$this->type = $type;
		$this->payload = $payload;
		$this->metadata = $metadata;
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return \DateTime
	 */
	public function getRecorded()
	{
		return $this->recorded;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @return []
	 */
	public function getPayload()
	{
		return $this->payload;
	}

	/**
	 * @return []
	 */
	public function getMetadata()
	{
		return $this->metadata;
	}




}