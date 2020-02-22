<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftTypedObject;

/**
 * @template T1 as DaftTypedObjectForRepository
 * @template T2 as array<string, scalar>
 * @template T3 as array<string, scalar|null>
 * @template T4 as array{type:class-string<DaftTypedObjectForRepository>}
 *
 * @template-extends DaftTypedObjectRepository<T1, T2, T4>
 */
interface PatchableObjectRepository extends DaftTypedObjectRepository
{
	/**
	 * @param T2 $id
	 * @param T3 $data
	 */
	public function PatchTypedObjectData(array $id, array $data) : void;
}
