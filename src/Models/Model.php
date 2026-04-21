<?php

namespace Worksection\Api\Models;

use ReflectionClass;

abstract class Model
{

	/**
	 * @param array $data
	 * @return static
	 */
	public static function fromArray(array $data): self
	{
		$ref = new ReflectionClass(static::class);
		$obj = new static();

		foreach ($ref->getProperties() as $property) {
			$name = $property->getName();

			if (!array_key_exists($name, $data)) {
				continue;
			}

			$value = $data[$name];
			$type = $property->getType();

			if ($type && !$type->isBuiltin()) {
				$className = $type->getName();
				if (is_array($value) && method_exists($className, 'fromArray')) {
					$value = $className::fromArray($value);
				}
			} elseif ($type) {
				settype($value, $type->getName());
			}

			$property->setValue($obj, $value);
		}

		return $obj;
	}

}
