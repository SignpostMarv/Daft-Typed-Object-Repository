<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftTypedObject;

/**
 * @template S as array{id:int, name:string}
 * @template S2 as array{id:int|string, name:string}
 * @template S3 as array{name:string}
 * @template T as array<string, scalar|array|object|null>
 * @template T1 as Fixtures\MutableForRepository
 * @template T2 as Fixtures\DaftTypedObjectMemoryRepository
 *
 * @template-extends DaftTypedObjectRepositoryTest<S, S2, S3, T, T1, T2>
 */
class DaftTypedObjectMemoryRepositoryTest extends DaftTypedObjectRepositoryTest
{
	/**
	 * @return list<
	 *	array{
	 *		0:class-string<T2>,
	 *		1:array{type:class-string<T1>},
	 *		2:list<S>,
	 *		3:list<S2>
	 *	}
	 * >
	 */
	public function dataProviderAppendTypedObject() : array
	{
		/**
		 * @var list<
		 *	array{
		 *		0:class-string<T2>,
		 *		1:array{type:class-string<T1>},
		 *		2:list<S>,
		 *		3:list<S2>
		 *	}
		 * >
		 */
		return [
			[
				Fixtures\DaftTypedObjectMemoryRepository::class,
				[
					'type' => Fixtures\MutableForRepository::class,
				],
				[
					[
						'id' => 0,
						'name' => 'foo',
					],
				],
				[
					[
						'id' => '1',
						'name' => 'foo',
					],
				],
			],
		];
	}

	/**
	 * @return list<
	 *	array{
	 *		0:class-string<T2&PatchableObjectRepository>,
	 *		1:array{type:class-string<T1>},
	 *		2:array<string, scalar|null>,
	 *		3:S3,
	 *		4:array<string, scalar|null>
	 *	}
	 * >
	 */
	public function dataProviderPatchObject() : array
	{
		/**
		 * @var list<
		 *	array{
		 *		0:class-string<T2&PatchableObjectRepository>,
		 *		1:array{type:class-string<T1>},
		 *		2:array<string, scalar|null>,
		 *		3:S3,
		 *		4:array<string, scalar|null>
		 *	}
		 * >
		 */
		return [
			[
				Fixtures\DaftTypedObjectMemoryRepository::class,
				[
					'type' => Fixtures\MutableForRepository::class,
				],
				[
					'id' => 0,
					'name' => 'foo',
				],
				[
					'name' => 'bar',
				],
				[
					'id' => '1',
					'name' => 'bar',
				],
			],
		];
	}

	/**
	 * @template K as key-of<S>
	 *
	 * @dataProvider dataProviderPatchObject
	 *
	 * @depends test_append_typed_object
	 *
	 * @param class-string<T2&PatchableObjectRepository> $repo_type
	 * @param array{type:class-string<T1>} $repo_args
	 * @param array<string, scalar|null> $append_this
	 * @param S3 $patch_this
	 * @param array<string, scalar|null> $expect_this
	 */
	public function test_patch_object(
		string $repo_type,
		array $repo_args,
		array $append_this,
		array $patch_this,
		array $expect_this
	) : void {
		parent::test_patch_object(
			$repo_type,
			$repo_args,
			$append_this,
			$patch_this,
			$expect_this
		);

		$repo = new $repo_type(
			$repo_args
		);

		$object_type = $repo_args['type'];

		$object = $object_type::__fromArray($append_this);

		$fresh = $repo->AppendTypedObject($object);

		$fresh->name = strrev($fresh->name);

		$repo->UpdateTypedObject($fresh);

		$repo->ForgetTypedObject($repo->ObtainIdFromObject($fresh));

		$fresh2 = $repo->RecallTypedObject($repo->ObtainIdFromObject($fresh));

		static::assertNotSame($fresh, $fresh2);

		static::assertSame(strrev($object->name), $fresh2->name);
	}
}
