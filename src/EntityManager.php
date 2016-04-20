<?php

/**
 * This software package is licensed under AGPL or Commercial license.
 *
 * @package maslosoft/mangan
 * @licence AGPL or Commercial
 * @copyright Copyright (c) Piotr Masełkowski <pmaselkowski@gmail.com>
 * @copyright Copyright (c) Maslosoft
 * @copyright Copyright (c) Others as mentioned in code
 * @link http://maslosoft.com/mangan/
 */

namespace Maslosoft\Mangan;

use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\Mangan\Events\Event;
use Maslosoft\Mangan\Events\EventDispatcher;
use Maslosoft\Mangan\Events\ModelEvent;
use Maslosoft\Mangan\Exceptions\ManganException;
use Maslosoft\Mangan\Helpers\CollectionNamer;
use Maslosoft\Mangan\Helpers\PkManager;
use Maslosoft\Mangan\Interfaces\CriteriaInterface;
use Maslosoft\Mangan\Interfaces\EntityManagerInterface;
use Maslosoft\Mangan\Interfaces\ScenariosInterface;
use Maslosoft\Mangan\Meta\ManganMeta;
use Maslosoft\Mangan\Options\EntityOptions;
use Maslosoft\Mangan\Signals\AfterDelete;
use Maslosoft\Mangan\Signals\AfterSave;
use Maslosoft\Mangan\Signals\BeforeDelete;
use Maslosoft\Mangan\Signals\BeforeSave;
use Maslosoft\Mangan\Transformers\RawArray;
use Maslosoft\Mangan\Transformers\SafeArray;
use Maslosoft\Signals\Signal;
use MongoCollection;

/**
 * EntityManager
 * 
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class EntityManager implements EntityManagerInterface
{

	/**
	 * Model
	 * @var AnnotatedInterface
	 */
	public $model = null;

	/**
	 *
	 * @var EventDispatcher
	 */
	public $ed = null;

	/**
	 *
	 * @var ScopeManager
	 */
	private $sm = null;

	/**
	 *
	 * @var
	 */
	public $meta = null;

	/**
	 * Options
	 * @var EntityOptions
	 */
	public $options = null;

	/**
	 * Current collection name
	 * @var string
	 */
	public $collectionName = '';

	/**
	 * Validator instance
	 * @var Validator
	 */
	private $validator = null;

	/**
	 * Current collection
	 * @var MongoCollection
	 */
	private $_collection = null;

	/**
	 * Create entity manager
	 * @param AnnotatedInterface $model
	 * @param Mangan $mangan
	 * @throws ManganException
	 */
	public function __construct(AnnotatedInterface $model, Mangan $mangan = null)
	{
		$this->model = $model;
		$this->sm = new ScopeManager($model);
		$this->options = new EntityOptions($model);
		$this->collectionName = CollectionNamer::nameCollection($model);
		$this->meta = ManganMeta::create($model);
		$this->validator = new Validator($model);
		if (null === $mangan)
		{
			$mangan = Mangan::fromModel($model);
		}
		if (!$this->collectionName)
		{
			throw new ManganException(sprintf('Invalid collection name for model: `%s`', $this->meta->type()->name));
		}
		$this->_collection = new MongoCollection($mangan->getDbInstance(), $this->collectionName);
	}

	/**
	 * Create model related entity manager.
	 * This will create customized entity manger if defined in model with EntityManager annotation.
	 * If no custom entity manager is defined this will return default EntityManager.
	 * @param AnnotatedInterface $model
	 * @param Mangan $mangan
	 * @return EntityManagerInterface
	 */
	public static function create($model, Mangan $mangan = null)
	{
		$emClass = ManganMeta::create($model)->type()->entityManager ? : static::class;
		return new $emClass($model, $mangan);
	}

	/**
	 * Set attributes en masse.
	 * Attributes will be filtered according to SafeAnnotation.
	 * Only attributes marked as safe will be set, other will be ignored.
	 *
	 * @param mixed[] $atributes
	 */
	public function setAttributes($atributes)
	{
		SafeArray::toModel($atributes, $this->model, $this->model);
	}

	/**
	 * Inserts a row into the table based on this active record attributes.
	 * If the table's primary key is auto-incremental and is null before insertion,
	 * it will be populated with the actual value after insertion.
	 *
	 * Note, validation is not performed in this method. You may call {@link validate} to perform the validation.
	 * After the record is inserted to DB successfully, its {@link isNewRecord} property will be set false,
	 * and its {@link scenario} property will be set to be 'update'.
	 *
	 * @param AnnotatedInterface $model if want to insert different model than set in constructor
	 *
	 * @return boolean whether the attributes are valid and the record is inserted successfully.
	 * @throws ManganException if the record is not new
	 * @throws ManganException on fail of insert or insert of empty document
	 * @throws ManganException on fail of insert, when safe flag is set to true
	 * @throws ManganException on timeout of db operation , when safe flag is set to true
	 * @since v1.0
	 */
	public function insert(AnnotatedInterface $model = null)
	{
		$model = $model ? : $this->model;
		if ($this->_beforeSave($model, EntityManagerInterface::EventBeforeInsert))
		{
			$rawData = RawArray::fromModel($model);
			$rawResult = $this->_collection->insert($rawData, $this->options->getSaveOptions());
			$result = $this->_result($rawResult, true);

			if ($result)
			{
				$this->_afterSave($model, EntityManagerInterface::EventAfterInsert);
				return true;
			}
			throw new ManganException('Can\t save the document to disk, or attempting to save an empty document.');
		}
		return false;
	}

	/**
	 * Updates the row represented by this active document.
	 * All loaded attributes will be saved to the database.
	 * Note, validation is not performed in this method. You may call {@link validate} to perform the validation.
	 *
	 * @param array $attributes list of attributes that need to be saved. Defaults to null,
	 * meaning all attributes that are loaded from DB will be saved.

	 * @return boolean whether the update is successful
	 * @throws ManganException if the record is new
	 * @throws ManganException on fail of update
	 * @throws ManganException on timeout of db operation , when safe flag is set to true
	 * @since v1.0
	 */
	public function update(array $attributes = null)
	{
		if ($this->_beforeSave($this->model, EntityManagerInterface::EventBeforeUpdate))
		{
			$criteria = PkManager::prepareFromModel($this->model);
			$result = $this->updateOne($criteria, $attributes);
			if ($result)
			{
				$this->_afterSave($this->model, EntityManagerInterface::EventAfterUpdate);
				return true;
			}
			throw new ManganException('Can\t save the document to disk, or attempting to save an empty document.');
		}
		return false;
	}

	/**
	 * Updates one document with the specified criteria and attributes
	 *
	 * This is more *raw* update:
	 *
	 * * Does not raise any events or signals
	 * * Does not perform any validation
	 *
	 * @param array|CriteriaInterface $criteria query criteria.
	 * @param array $attributes list of attributes that need to be saved. Defaults to null,
	 * @param bool Whether tu force update/upsert document
	 * meaning all attributes that are loaded from DB will be saved.
	 * @since v1.0
	 */
	public function updateOne($criteria = null, array $attributes = null, $modify = false)
	{
		$criteria = $this->sm->apply($criteria);
		$rawData = RawArray::fromModel($this->model);

		// filter attributes if set in param
		if ($attributes !== null)
		{
			$modify = true;
			foreach ($rawData as $key => $value)
			{
				if (!in_array($key, $attributes))
				{
					unset($rawData[$key]);
				}
			}
		}
		else
		{
			$fields = array_keys(ManganMeta::create($this->model)->fields());
			$setFields = array_keys($rawData);
			$diff = array_diff($fields, $setFields);

			if (!empty($diff))
			{
				$modify = true;
			}
		}
		if ($modify)
		{
			// Id could be altered, so skip it as it cannot be changed
			unset($rawData['_id']);
			$data = ['$set' => $rawData];
		}
		else
		{
			$data = $rawData;
		}

		$result = $this->getCollection()->update($criteria->getConditions(), $data, $this->options->getSaveOptions(['multiple' => false, 'upsert' => true]));
		return $this->_result($result);
	}

	/**
	 * Atomic, in-place update method.
	 *
	 * @since v1.3.6
	 * @param Modifier $modifier updating rules to apply
	 * @param CriteriaInterface $criteria condition to limit updating rules
	 * @return boolean
	 */
	public function updateAll(Modifier $modifier, CriteriaInterface $criteria = null)
	{
		if ($modifier->canApply())
		{
			$criteria = $this->sm->apply($criteria);
			$result = $this->getCollection()->update($criteria->getConditions(), $modifier->getModifiers(), $this->options->getSaveOptions([
						'upsert' => false,
						'multiple' => true
			]));
			return $this->_result($result);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Replaces the current document.
	 *
	 * **NOTE: This will overwrite entire document.**
	 * Any filtered out properties will be removed as well.
	 *
	 * The record is inserted as a documnent into the database collection, if exists it will be replaced.
	 *
	 * Validation will be performed before saving the record. If the validation fails,
	 * the record will not be saved. You can call {@link getErrors()} to retrieve the
	 * validation errors.
	 *
	 * @param boolean $runValidation whether to perform validation before saving the record.
	 * If the validation fails, the record will not be saved to database.
	 * @param AnnotatedInterface $model if want to insert different model than set in constructor
	 * @return boolean whether the saving succeeds
	 * @since v1.0
	 */
	public function replace($runValidation = true)
	{
		if (!$runValidation || $this->validator->validate())
		{
			$model = $this->model;
			if ($this->_beforeSave($model))
			{
				$data = RawArray::fromModel($model);
				$rawResult = $this->_collection->save($data, $this->options->getSaveOptions());
				$result = $this->_result($rawResult, true);

				if ($result)
				{
					$this->_afterSave($model);
					return true;
				}
				throw new ManganException("Can't save the document to disk, or attempting to save an empty document");
			}
			return false;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Saves the current document.
	 *
	 * The record is inserted as a document into the database collection or updated if exists.
	 *
	 * Filtered out properties will remain in database - it is partial safe.
	 *
	 * Validation will be performed before saving the record. If the validation fails,
	 * the record will not be saved. You can call {@link getErrors()} to retrieve the
	 * validation errors.
	 *
	 * @param boolean $runValidation whether to perform validation before saving the record.
	 * If the validation fails, the record will not be saved to database.
	 * @param AnnotatedInterface $model if want to insert different model than set in constructor
	 * @return boolean whether the saving succeeds
	 * @since v1.0
	 */
	public function save($runValidation = true)
	{
		return $this->upsert($runValidation);
	}

	/**
	 * Updates or inserts the current document. This will try to update existing fields.
	 * Will keep already stored data if present in document.
	 *
	 * If document does not exist, a new one will be inserted.
	 *
	 * @param boolean $runValidation
	 * @return boolean
	 * @throws ManganException
	 */
	public function upsert($runValidation = true)
	{
		if (!$runValidation || $this->validator->validate())
		{
			$model = $this->model;
			if ($this->_beforeSave($model))
			{
				$criteria = PkManager::prepareFromModel($this->model);
				foreach ($criteria->getConditions() as $field => $value)
				{
					if (empty($this->model->$field))
					{
						$this->model->$field = $value;
					}
				}
				$result = $this->updateOne($criteria);

				if ($result)
				{
					$this->_afterSave($model);
					return true;
				}
				throw new ManganException("Can't save the document to disk, or attempting to save an empty document");
			}
			return false;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Reloads document from database.
	 * It return true if document is reloaded and false if it's no longer exists.
	 *
	 * @return boolean
	 */
	public function refresh()
	{
		$conditions = PkManager::prepareFromModel($this->model)->getConditions();
		$data = $this->getCollection()->findOne($conditions);
		if (null !== $data)
		{
			RawArray::toModel($data, $this->model, $this->model);
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Deletes the document from database.
	 * @return boolean whether the deletion is successful.
	 * @throws ManganException if the record is new
	 */
	public function delete()
	{
		if ($this->_beforeDelete())
		{
			$result = $this->deleteOne(PkManager::prepareFromModel($this->model));

			if ($result !== false)
			{
				$this->_afterDelete();
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Deletes one document with the specified primary keys.
	 * <b>Does not raise beforeDelete</b>
	 * See {@link find()} for detailed explanation about $condition and $params.
	 * @param array|CriteriaInterface $criteria query criteria.
	 * @since v1.0
	 */
	public function deleteOne($criteria = null)
	{
		$criteria = $this->sm->apply($criteria);

		$result = $this->getCollection()->remove($criteria->getConditions(), $this->options->getSaveOptions([
					'justOne' => true
		]));
		return $this->_result($result);
	}

	/**
	 * Deletes document with the specified primary key.
	 * See {@link find()} for detailed explanation about $condition and $params.
	 * @param mixed $pkValue primary key value(s). Use array for multiple primary keys. For composite key, each key value must be an array (column name=>column value).
	 * @param array|CriteriaInterface $criteria query criteria.
	 * @since v1.0
	 */
	public function deleteByPk($pkValue, $criteria = null)
	{
		if ($this->_beforeDelete())
		{
			$criteria = $this->sm->apply($criteria);
			$criteria->mergeWith(PkManager::prepare($this->model, $pkValue));

			$result = $this->getCollection()->remove($criteria->getConditions(), $this->options->getSaveOptions([
						'justOne' => true
			]));
			return $this->_result($result);
		}
		return false;
	}

	/**
	 * Deletes documents with the specified primary keys.
	 * See {@link find()} for detailed explanation about $condition and $params.
	 * @param mixed[] $pkValues Primary keys array
	 * @param array|CriteriaInterface $criteria query criteria.
	 * @since v1.0
	 */
	public function deleteAllByPk($pkValues, $criteria = null)
	{
		if ($this->_beforeDelete())
		{
			$criteria = $this->sm->apply($criteria);
			$criteria->mergeWith(PkManager::prepareAll($this->model, $pkValues, $criteria));
			$result = $this->getCollection()->remove($criteria->getConditions(), $this->options->getSaveOptions([
						'justOne' => false
			]));
			return $this->_result($result);
		}
		return false;
	}

	/**
	 * Deletes documents with the specified primary keys.
	 *
	 * **Does not raise beforeDelete event and does not emit signals**
	 *
	 * See {@link find()} for detailed explanation about $condition and $params.
	 *
	 * @param array|CriteriaInterface $criteria query criteria.
	 * @since v1.0
	 */
	public function deleteAll($criteria = null)
	{
		$criteria = $this->sm->apply($criteria);

		$result = $this->getCollection()->remove($criteria->getConditions(), $this->options->getSaveOptions([
					'justOne' => false
		]));
		return $this->_result($result);
	}

	public function getCollection()
	{
		return $this->_collection;
	}

	/**
	 * Make status uniform
	 * @param bool|array $result
	 * @param bool $insert Set to true for inserts
	 * @return bool Return true if secceed
	 */
	private function _result($result, $insert = false)
	{
		if (is_array($result))
		{
			if ($insert)
			{
				return (bool) $result['ok'];
			}
			return (bool) $result['n'];
		}
		return $result;
	}

// <editor-fold defaultstate="collapsed" desc="Event and Signal handling">

	/**
	 * Take care of EventBeforeSave
	 * @see EventBeforeSave
	 * @return boolean
	 */
	private function _beforeSave($model, $event = null)
	{
		$result = Event::Valid($model, EntityManagerInterface::EventBeforeSave);
		if ($result)
		{
			if (!empty($event))
			{
				Event::trigger($model, $event);
			}
			(new Signal)->emit(new BeforeSave($model));
		}
		return $result;
	}

	/**
	 * Take care of EventAfterSave
	 * @see EventAfterSave
	 */
	private function _afterSave($model, $event = null)
	{
		Event::trigger($model, EntityManagerInterface::EventAfterSave);
		if (!empty($event))
		{
			Event::trigger($model, $event);
		}
		(new Signal)->emit(new AfterSave($model));
		ScenarioManager::setScenario($model, ScenariosInterface::Update);
	}

	/**
	 * This method is invoked before deleting a record.
	 * The default implementation raises the {@link onBeforeDelete} event.
	 * You may override this method to do any preparation work for record deletion.
	 * Make sure you call the parent implementation so that the event is raised properly.
	 * @return boolean whether the record should be deleted. Defaults to true.
	 * @since v1.0
	 */
	private function _beforeDelete()
	{
		$result = Event::valid($this->model, EntityManagerInterface::EventBeforeDelete);
		if ($result)
		{
			(new Signal)->emit(new BeforeDelete($this->model));
			ScenarioManager::setScenario($this->model, ScenariosInterface::Delete);
		}
		return $result;
	}

	/**
	 * This method is invoked after deleting a record.
	 * The default implementation raises the {@link onAfterDelete} event.
	 * You may override this method to do postprocessing after the record is deleted.
	 * Make sure you call the parent implementation so that the event is raised properly.
	 * @since v1.0
	 */
	private function _afterDelete()
	{
		Event::trigger($this->model, EntityManagerInterface::EventAfterDelete, new ModelEvent($this->model));
		(new Signal)->emit(new AfterDelete($this->model));
	}

// </editor-fold>
}
