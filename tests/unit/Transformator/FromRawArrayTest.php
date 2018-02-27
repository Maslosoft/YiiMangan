<?php

namespace Transformator;

use Codeception\Test\Unit;
use Maslosoft\Mangan\Transformers\RawArray;
use Maslosoft\ManganTest\Models\Plain\PlainWithBasicAttributes;
use UnitTester;

class RawArrayTest extends Unit
{

	/**
	 * @var UnitTester
	 */
	protected $tester;

	protected function _before()
	{

	}

	protected function _after()
	{
		
	}

	// tests
	public function testIfWillPopulateSimplePlainModel()
	{
		$data = [
			'_class' => PlainWithBasicAttributes::class,
			'int' => 12345,
			'string' => 'foo',
			'bool' => false,
			'float' => 110.23,
			'array' => ['new', 'array'],
			'null' => null,
		];

		$model = RawArray::toModel($data);
		$this->assertTrue($model instanceof PlainWithBasicAttributes);
		unset($data['_class']);
		foreach ($data as $field => $value)
		{
			$this->assertSame($value, $model->$field);
		}
	}

}
