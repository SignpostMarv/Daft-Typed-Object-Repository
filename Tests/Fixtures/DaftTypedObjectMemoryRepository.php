<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftTypedObject\Fixtures;

use RuntimeException;
use SignpostMarv\DaftRelaxedObjectRepository\ConvertingRepository;
use SignpostMarv\DaftTypedObject\AbstractDaftTypedObjectRepository;
use SignpostMarv\DaftTypedObject\AppendableTypedObjectRepository;
use SignpostMarv\DaftTypedObject\DaftTypedObjectForRepository;
use SignpostMarv\DaftTypedObject\PatchableObjectRepository;
use Throwable;

/**
 * @template T1 as MutableForRepository
 * @template T2 as array{id:int}
 * @template S1 as array{name:string}
 * @template S2 as array{id:int, name:string}
 *
 * @template-extends AbstractDaftTypedObjectRepository<T1, T2>
 *
 * @template-implements AppendableTypedObjectRepository<T1, T2, S1>
 * @template-implements ConvertingRepository<T1, S2, T2>
 * @template-implements PatchableObjectRepository<T1, T2, S1>
 */
class DaftTypedObjectMemoryRepository extends AbstractDaftTypedObjectRepository implements
		AppendableTypedObjectRepository,
		ConvertingRepository,
		PatchableObjectRepository
{
	const MIN_BASE_ID = 0;

	const INCREMENT_NEW_ID_BY = 1;

	/**
	 * @var array<string, S2>
	 */
	protected array $data = [];

	/**
	 * @var array<string, T1>
	 */
	protected array $memory = [];

	/**
	 * @param T1 $object
	 *
	 * @return T1
	 */
	public function AppendTypedObject(
		DaftTypedObjectForRepository $object
	) : DaftTypedObjectForRepository {
		/**
		 * @var T1
		 */
		return $this->AppendTypedObjectFromArray([
			'name' => $object->name,
		]);
	}

	public function AppendTypedObjectFromArray(
		array $data
	) : DaftTypedObjectForRepository {
		$new_id = max(self::MIN_BASE_ID, count($this->data)) + self::INCREMENT_NEW_ID_BY;

		/** @var S2 */
		$data = [
			'id' => $new_id,
			'name' => $data['name'],
		];

		$hash = static::RelaxedObjectHash(['id' => $new_id]);

		$this->data[$hash] = $data;

		$object = $this->ConvertSimpleArrayToObject($data);

		$this->memory[$hash] = $object;

		/**
		 * @var T1
		 */
		return $object;
	}

	public function UpdateTypedObject(
		DaftTypedObjectForRepository $object
	) : void {
		/**
		 * @var T2
		 */
		$id = $object->ObtainId();

		$hash = static::RelaxedObjectHash($id);

		parent::UpdateTypedObject($object);

		$this->data[$hash] = $this->ConvertObjectToSimpleArray($object);
	}

	/**
	 * @param T2 $id
	 */
	public function RemoveObject(array $id) : void
	{
		$hash = static::RelaxedObjectHash($id);

		$this->ForgetTypedObject($id);
		unset($this->data[$hash]);
	}

	/**
	 * @return T1|null
	 */
	public function MaybeRecallObject(
		array $id
	) : ? object {
		$maybe = parent::MaybeRecallObject($id);

		if (is_null($maybe)) {
			$hash = static::RelaxedObjectHash($id);

			$row = $this->data[$hash] ?? null;

			if (null !== $row) {
				$object = $this->ConvertSimpleArrayToObject($row);

				$this->UpdateTypedObject($object);

				return $object;
			}

			return null;
		}

		return $maybe;
	}

	/**
	 * @param T2 $id
	 * @param S1 $data
	 */
	public function PatchTypedObjectData(array $id, array $data) : void
	{
		/**
		 * @var array<string, scalar|null>
		 */
		$id = $id;

		/**
		 * @var array<string, scalar|null>
		 */
		$data = $data;

		/** @var S2 */
		$from_array_args = $id + $data;

		$object = $this->ConvertSimpleArrayToObject($from_array_args);

		$this->UpdateTypedObject($object);
	}

	/**
	 * @param S2 $array
	 */
	public function ConvertSimpleArrayToObject(array $array) : object
	{
		/** @var T1 */
		return MutableForRepository::__fromArray($array);
	}

	/**
	 * @param T1 $object
	 *
	 * @return S2
	 */
	public function ConvertObjectToSimpleArray(object $object) : array
	{
		/** @var S2 */
		return $object->__toArray();
	}
}
