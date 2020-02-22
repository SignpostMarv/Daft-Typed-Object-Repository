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

/**
 * @template S as array<string, scalar|null>
 * @template S2 as array<string, scalar|null>
 * @template S3 as array<string, scalar|null>
 * @template T as array<string, scalar|array|object|null>
 * @template T1 as DaftTypedObjectForRepository
 * @template T2 as AppendableTypedObjectRepository
 */
abstract class DaftTypedObjectRepositoryTest extends Base
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
	abstract public function dataProviderAppendTypedObject() : array;

	/**
	 * @template K as key-of<S>
	 *
	 * @dataProvider dataProviderAppendTypedObject
	 *
	 * @param class-string<T2> $repo_type
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

			/** @var T1 */
			$fresh2 = $fresh2;

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
	 * @param class-string<T2> $repo_type
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
	 * @param class-string<T2> $repo_type
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
	 *		0:class-string<T2&PatchableObjectRepository>,
	 *		1:array{type:class-string<T1>},
	 *		2:array<string, scalar|null>,
	 *		3:S3,
	 *		4:array<string, scalar|null>
	 *	}
	 * >
	 */
	abstract public function dataProviderPatchObject() : array;

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
		/** @var T2&PatchableObjectRepository */
		$repo = new $repo_type(
			$repo_args
		);

		$object_type = $repo_args['type'];

		$object = $object_type::__fromArray($append_this);

		$fresh = $repo->AppendTypedObject($object);

		$id = $fresh->ObtainId();

		$repo->PatchTypedObjectData($id, $patch_this);

		static::assertSame(
			$expect_this,
			$repo->RecallTypedObject($fresh->ObtainId())->__toArray()
		);
	}
}
