<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftTypedObject;

use Exception;
use PHPUnit\Framework\TestCase as Base;
use function random_bytes;
use RuntimeException;
use SignpostMarv\DaftRelaxedObjectRepository\ConvertingRepository;

/**
 * @template S as array{id:int, name:string}
 * @template S2 as array{id:int|string, name:string}
 * @template T as array<string, scalar|array|object|null>
 * @template T1 as Fixtures\MutableForRepository
 */
class DaftTypedObjectRepositoryTest extends Base
{
	/**
	 * @return list<
	 *	array{
	 *		0:class-string<AppendableTypedObjectRepository>,
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
		 *		0:class-string<AppendableTypedObjectRepository>,
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
	 * @template K as key-of<S>
	 *
	 * @dataProvider dataProviderAppendTypedObject
	 *
	 * @param class-string<AppendableTypedObjectRepository> $repo_type
	 * @param array{type:class-string<T1>} $repo_args
	 * @param list<S> $append_these
	 * @param list<S2> $expect_these
	 */
	public function test_append_typed_object(
		string $repo_type,
		array $repo_args,
		array $append_these,
		array $expect_these
	) : void {
		$repo = new $repo_type(
			$repo_args
		);

		$object_type = $repo_args['type'];

		static::assertGreaterThan(0, count($append_these));
		static::assertCount(count($append_these), $expect_these);

		/**
		 * @var array<int, T1>
		 */
		$testing = [];

		foreach ($append_these as $i => $data) {
			$object = $object_type::__fromArray($data);

			$testing[$i] = $repo->AppendTypedObject($object);
		}

		foreach ($testing as $i => $object) {
			static::assertSame(
				$expect_these[$i],
				$object->__toArray()
			);

			static::assertSame(
				$object,
				$repo->RecallTypedObject($object->ObtainId())
			);

			$repo->ForgetTypedObject($object->ObtainId());

			$fresh1 = $repo->MaybeRecallTypedObject($object->ObtainId());

			static::assertNotNull($fresh1);

			$fresh2 = $repo->RecallTypedObject($object->ObtainId());

			static::assertNotSame($object, $fresh1);
			static::assertNotSame($object, $fresh2);
			static::assertSame($fresh1, $fresh2);

			static::assertSame($expect_these[$i], $object->jsonSerialize());
			static::assertSame($expect_these[$i], $fresh1->jsonSerialize());
			static::assertSame($expect_these[$i], $fresh2->jsonSerialize());

			$repo->RemoveTypedObject($object->ObtainId());

			static::assertNull(
				$repo->MaybeRecallTypedObject($object->ObtainId())
			);
		}
	}

	/**
	 * @template K as key-of<S>
	 *
	 * @dataProvider dataProviderAppendTypedObject
	 *
	 * @depends test_append_typed_object
	 *
	 * @param class-string<AppendableTypedObjectRepository> $repo_type
	 * @param array{type:class-string<T1>} $repo_args
	 * @param list<S> $_append_these
	 * @param list<S2> $expect_these
	 */
	public function test_default_failure(
		string $repo_type,
		array $repo_args,
		array $_append_these,
		array $expect_these
	) : void {
		$repo = new $repo_type(
			$repo_args
		);

		$object_type = $repo_args['type'];

		$data = current($expect_these);

		/**
		 * @var array<int, K>
		 */
		$data_keys = array_keys($data);

		/**
		 * @var T
		 */
		$object_args = array_combine($data_keys, array_map(
			/**
			 * @param K $property
			 * @param S[K] $value
			 *
			 * @return T[K]
			 */
			static function ($property, $value) use ($object_type) {
				/**
				 * @var T[K]
				 */
				return $object_type::PropertyScalarOrNullToValue(
					$property,
					$value
				);
			},
			$data_keys,
			$data
		));

		$object = new $object_type($object_args);

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage(
			'Object could not be found for the specified id!'
		);

		$repo->RecallTypedObject($object->ObtainId());
	}

	/**
	 * @template K as key-of<S>
	 *
	 * @dataProvider dataProviderAppendTypedObject
	 *
	 * @depends test_append_typed_object
	 *
	 * @param class-string<AppendableTypedObjectRepository> $repo_type
	 * @param array{type:class-string<T1>} $repo_args
	 * @param list<S> $_append_these
	 * @param list<S2> $expect_these
	 */
	public function test_custom_failure(
		string $repo_type,
		array $repo_args,
		array $_append_these,
		array $expect_these
	) : void {
		$repo = new $repo_type(
			$repo_args
		);

		$object_type = $repo_args['type'];

		$data = current($expect_these);

		/**
		 * @var array<int, K>
		 */
		$data_keys = array_keys($data);

		/**
		 * @var T
		 */
		$object_args = array_combine($data_keys, array_map(
			/**
			 * @param K $property
			 * @param S[K] $value
			 *
			 * @return scalar|array|object|null
			 */
			static function ($property, $value) use ($object_type) {
				/**
				 * @var scalar|array|object|null
				 */
				return $object_type::PropertyScalarOrNullToValue(
					$property,
					$value
				);
			},
			$data_keys,
			$data
		));

		$object = new $object_type($object_args);

		$random = bin2hex(random_bytes(16));

		$this->expectException(Exception::class);
		$this->expectExceptionMessage($random);

		$repo->RecallTypedObject($object->ObtainId(), new Exception($random));
	}

	/**
	 * @return list<
	 *	array{
	 *		0:class-string<AppendableTypedObjectRepository&PatchableObjectRepository&ConvertingRepository>,
	 *		1:array{type:class-string<T1>},
	 *		2:array<string, scalar|null>,
	 *		3:array<string, scalar|null>,
	 *		4:array<string, scalar|null>
	 *	}
	 * >
	 */
	public function dataProviderPatchObject() : array
	{
		/**
		 * @var list<
		 *	array{
		 *		0:class-string<AppendableTypedObjectRepository&PatchableObjectRepository&ConvertingRepository>,
		 *		1:array{type:class-string<T1>},
		 *		2:array<string, scalar|null>,
		 *		3:array<string, scalar|null>,
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
	 * @param class-string<AppendableTypedObjectRepository&PatchableObjectRepository&ConvertingRepository> $repo_type
	 * @param array{type:class-string<T1>} $repo_args
	 * @param array<string, scalar|null> $append_this
	 * @param array<string, scalar|null> $patch_this
	 * @param array<string, scalar|null> $expect_this
	 */
	public function test_patch_object(
		string $repo_type,
		array $repo_args,
		array $append_this,
		array $patch_this,
		array $expect_this
	) : void {
		$repo = new $repo_type(
			$repo_args
		);

		$object_type = $repo_args['type'];

		$object = $object_type::__fromArray($append_this);

		/** @var Fixtures\MutableForRepository */
		$fresh = $repo->AppendTypedObject($object);

		/** @var array{id:int} */
		$id = $repo->ObtainIdFromObject($fresh);

		$repo->PatchTypedObjectData($id, $patch_this);

		static::assertSame(
			$expect_this,
			$repo->RecallTypedObject($repo->ObtainIdFromObject($fresh))->__toArray()
		);

		$fresh->name = strrev($fresh->name);

		$repo->UpdateTypedObject($fresh);

		$repo->ForgetTypedObject($repo->ObtainIdFromObject($fresh));

		/** @var Fixtures\MutableForRepository */
		$fresh2 = $repo->RecallTypedObject($repo->ObtainIdFromObject($fresh));

		static::assertNotSame($fresh, $fresh2);

		static::assertSame(strrev($object->name), $fresh2->name);
	}
}
