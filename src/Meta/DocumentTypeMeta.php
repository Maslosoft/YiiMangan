<?php

/**
 * This software package is licensed under New BSD license.
 *
 * @package maslosoft/mangan
 * @licence New BSD
 * @copyright Copyright (c) Piotr Masełkowski <pmaselkowski@gmail.com>
 * @copyright Copyright (c) Maslosoft
 * @copyright Copyright (c) Others as mentioned in code
 * @link http://maslosoft.com/mangan/
 */

namespace Maslosoft\Mangan\Meta;

use Maslosoft\Addendum\Collections\MetaType;
use Maslosoft\Mangan\Annotations\AliasAnnotation;
use Maslosoft\Mangan\Annotations\CollectionNameAnnotation;
use Maslosoft\Mangan\Annotations\ConnectionIdAnnotation;
use Maslosoft\Mangan\Annotations\EntityManagerAnnotation;
use Maslosoft\Mangan\Annotations\FinderAnnotation;
use Maslosoft\Mangan\Annotations\HomogenousAnnotation;
use Maslosoft\Mangan\Annotations\LabelAnnotation;
use Maslosoft\Mangan\Annotations\PrimaryKeyAnnotation;
use Maslosoft\Mangan\EntityManager;
use Maslosoft\Mangan\Exceptions\ManganException;
use Maslosoft\Mangan\Helpers\PropertyMaker;
use Maslosoft\Mangan\Mangan;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

/**
 * Model meta container
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class DocumentTypeMeta extends MetaType
{

	use \Maslosoft\Mangan\Traits\Defaults\MongoClientOptions,
	  \Maslosoft\Mangan\Traits\Access\GetSet;

	/**
	 * Field label
	 * @see LabelAnnotation
	 * @var string
	 */
	public $label = '';

	/**
	 * Description
	 * @var string
	 */
	public $description = '';

	/**
	 * Collection name
	 * @see CollectionNameAnnotation
	 * @var string
	 */
	public $collectionName = '';

	/**
	 * Connection ID
	 * @see ConnectionIdAnnotation
	 * @var string
	 */
	public $connectionId = Mangan::DefaultConnectionId;

	/**
	 * Primary key field or fields
	 * @see PrimaryKeyAnnotation
	 * @var string|array
	 */
	public $primaryKey = null;

	/**
	 * Whenever to use cursors
	 * @var bool
	 */
	public $useCursor = false;

	/**
	 * Whenever colleciton is homogenous
	 * @see HomogenousAnnotation
	 * @var bool
	 */
	public $homogenous = true;

	/**
	 * Finder class name to return by Finder from create method
	 * @see Finder::create()
	 * @see FinderAnnotation
	 * @var string
	 */
	public $finder = null;

	/**
	 * Entity Manager class name to return by EntityManager from create method
	 * @see EntityManager::create()
	 * @see EntityManagerAnnotation
	 * @var string
	 */
	public $entityManager = null;

	/**
	 * Property aliases. This consists of source property name as key, and destination property as value.
	 * @var string[]
	 * @see AliasAnnotation
	 */
	public $aliases = [];

	/**
	 * Values of properties
	 * @var mixed
	 */
	private $_values = [];

	public function __construct(ReflectionClass $info = null)
	{
		// Client Options must be unset to allow cascading int EntityOptions
		parent::__construct($info);
		foreach ($this->_getOptionNames() as $name)
		{
			PropertyMaker::defineProperty($this, $name);
		}
//		foreach (['collectionName', 'connectionId'] as $name)
//		{
//			PropertyMaker::defineProperty($this, $name, $this->_values);
//		}
	}

	public function __get($name)
	{
		if ($this->_hasGetter($name))
		{
			return parent::__get($name);
		}
		if (!array_key_exists($name, $this->_values))
		{
			throw new ManganException(sprintf('Trying to read unitialized property `%s`', $name));
		}
		return $this->_values[$name];
	}

	public function __set($name, $value)
	{
		if ($this->_hasSetter($name))
		{
			return parent::__set($name);
		}
		$this->_values[$name] = $value;
	}

	public function __isset($name)
	{
		return array_key_exists($name, $this->_values);
	}

//	public function getCollectionName()
//	{
//		if ($this->_values['collectionName'])
//		{
//			return $this->_values['collectionName'];
//		}
//		return str_replace('\\', '.', $this->name);
//	}
//
//	public function setCollectionName($name)
//	{
//		$this->_values['collectionName'] = $name;
//	}
//
//	public function getConnectionId()
//	{
//		if(!$this->_values['connectionId'])
//		{
//			$this->_values['connectionId'] = Mangan::DefaultConnectionId;
//		}
//		return $this->_values['connectionId'];
//	}
//
//	public function setConnectionId($connectionId)
//	{
//		$this->_values['connectionId'] = $connectionId;
//	}
}
