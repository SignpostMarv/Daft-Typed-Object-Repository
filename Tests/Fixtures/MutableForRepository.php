<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftTypedObject\Fixtures;

use SignpostMarv\DaftTypedObject\DaftTypedObjectForRepository;

/**
 * @template T as array{id:int, name:string}
 * @template S as array{id:string, name:string}
 *
 * @template-implements DaftTypedObjectForRepository<array{id:int}>
 */
class MutableForRepository extends Mutable implements DaftTypedObjectForRepository
{
	public function ObtainId() : array
	{
		return [
			'id' => (int) $this->id,
		];
	}

	/**
	 * @template K as key-of<T>
	 *
	 * @param K $property
	 * @param S[K] $value
	 *
	 * @return T[K]
	 */
	public static function PropertyScalarOrNullToValue(
		string $property,
		$value
	) {
		/**
		 * @var 'id'|'name'
		 */
		$property = $property;

		if ('id' === $property) {
			/**
			 * @var T[K]
			 */
			return (int) $value;
		}

		/**
		 * @var string
		 */
		$value = $value;

		/**
		 * @var T[K]
		 */
		return parent::PropertyScalarOrNullToValue($property, $value);
	}

	/**
	 * @template K as key-of<T>
	 *
	 * @param K $property
	 * @param T[K] $value
	 *
	 * @return S[K]
	 */
	public static function PropertyValueToScalarOrNull(
		string $property,
		$value
	) {
		/**
		 * @var 'id'|'name'
		 */
		$property = $property;

		if ('id' === $property) {
			/**
			 * @var scalar
			 */
			$value = $value;

			/**
			 * @var S[K]
			 */
			return (string) $value;
		}

		/**
		 * @var string
		 */
		$value = $value;

		/**
		 * @var S[K]
		 */
		return parent::PropertyValueToScalarOrNull($property, $value);
	}
}
