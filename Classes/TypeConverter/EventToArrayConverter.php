<?php
namespace Meteko\Events\TypeConverter;

use Meteko\Events\Event\EventInterface;
use Neos\Flow\Property\PropertyMappingConfigurationInterface;
use Neos\Flow\Property\TypeConverter\AbstractTypeConverter;
use Neos\Utility\ObjectAccess;
use Neos\Utility\TypeHandling;

/**
 * Simple TypeConverter that can turn instances of EventInterface to an array that contains all properties recursively
 */
class EventToArrayConverter extends AbstractTypeConverter
{

	/**
	 * @var array<string>
	 */
	protected $sourceTypes = [EventInterface::class];

	/**
	 * @var string
	 */
	protected $targetType = 'array';

	/**
	 * @var integer
	 */
	protected $priority = 1;

	/**
	 * @param mixed $source
	 * @param string $targetType
	 * @param array $convertedChildProperties
	 * @param PropertyMappingConfigurationInterface $configuration
	 * @return array|\Neos\Error\Messages\Error
	 */
	public function convertFrom($source, $targetType, array $convertedChildProperties = [], PropertyMappingConfigurationInterface $configuration = null)
	{
		return $this->convertObject($source);
	}

	/**
	 * @param mixed $object
	 * @return array
	 */
	protected function convertObject($object)
	{
		$properties = ObjectAccess::getGettableProperties($object);
		foreach ($properties as $propertyName => &$propertyValue) {
			if (TypeHandling::isSimpleType(gettype($propertyValue))) {
				continue;
			}
			if ($propertyValue instanceof \DateTimeZone) {
				$propertyValue = $propertyValue->getName();
			} elseif ($propertyValue instanceof \DateTimeInterface) {
				$propertyValue = $propertyValue->format(DATE_ISO8601);
			} elseif (is_object($propertyValue)) {
				$propertyValue = $this->convertObject($propertyValue);
			}
		}
		return $properties;
	}
}