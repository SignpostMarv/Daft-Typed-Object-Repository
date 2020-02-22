<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftTypedObject;

use Throwable;

/**
 * @template T1 as DaftTypedObjectForRepository
 * @template T2 as array<string, scalar>
 * @template T3 as array{type:class-string<DaftTypedObjectForRepository>}
 *
 * @property-read class-string<T1> $type
 */
interface DaftTypedObjectRepository
{
	/**
	 * @param T3 $options
	 */
	public function __construct(array $options);

	/**
	 * @param T1 $object
	 */
	public function UpdateTypedObject(
		DaftTypedObjectForRepository $object
	) : void;

	/**
	 * @param T2 $id
	 */
	public function ForgetTypedObject(
		array $id
	) : void;

	/**
	 * @param T2 $id
	 */
	public function RemoveTypedObject(
		array $id
	) : void;

	/**
	 * @param T2 $id
	 *
	 * @return T1
	 */
	public function RecallTypedObject(
		array $id,
		Throwable $not_found = null
	) : DaftTypedObjectForRepository;

	/**
	 * @param T2 $id
	 *
	 * @return T1|null
	 */
	public function MaybeRecallTypedObject(
		array $id
	) : ? DaftTypedObjectForRepository;
}
