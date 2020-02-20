<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftTypedObject;

use SignpostMarv\DaftRelaxedObjectRepository\AbstractObjectRepository;
use Throwable;

/**
 * @template T1 as DaftTypedObjectForRepository
 * @template T2 as array<string, scalar>
 *
 * @template-extends AbstractObjectRepository<T1, T2>
 *
 * @template-implements DaftTypedObjectRepository<T1, T2>
 */
abstract class AbstractDaftTypedObjectRepository extends AbstractObjectRepository implements DaftTypedObjectRepository
{
	public function __construct(array $options)
	{
		parent::__construct($options);
	}

	/**
	 * @param T1 $object
	 */
	public function UpdateTypedObject(
		DaftTypedObjectForRepository $object
	) : void {
		$this->UpdateObject($object);
	}

	/**
	 * @param T2 $id
	 */
	public function ForgetTypedObject(array $id) : void
	{
		$this->ForgetObject($id);
	}

	/**
	 * @param T2 $id
	 *
	 * @return T1|null
	 */
	public function MaybeRecallTypedObject(
		array $id
	) : ? DaftTypedObjectForRepository {
		return $this->MaybeRecallObject($id);
	}

	/**
	 * @param T2 $id
	 */
	public function RecallTypedObject(
		array $id,
		Throwable $not_found = null
	) : DaftTypedObjectForRepository {
		return $this->RecallObject($id, $not_found);
	}

	/**
	 * @param T2 $id
	 */
	public function RemoveTypedObject(
		array $id
	) : void {
		$this->RemoveObject($id);
	}

	/**
	 * @param T1 $object
	 *
	 * @return T2
	 */
	public function ObtainIdFromObject(object $object) : array
	{
		return $object->ObtainId();
	}
}
