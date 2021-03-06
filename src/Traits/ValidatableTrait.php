<?php

/**
 * This software package is licensed under AGPL or Commercial license.
 *
 * @package maslosoft/mangan
 * @licence AGPL or Commercial
 * @copyright Copyright (c) Piotr Masełkowski <pmaselkowski@gmail.com>
 * @copyright Copyright (c) Maslosoft
 * @copyright Copyright (c) Others as mentioned in code
 * @link https://maslosoft.com/mangan/
 */

namespace Maslosoft\Mangan\Traits;

use Maslosoft\Mangan\Interfaces\ValidatableInterface;
use Maslosoft\Mangan\Validator;

/**
 * Validatable trait adds methods for validating model. These are not nessesary
 * to validate, but might be more convenient to validate model by it's own method.
 *
 * Class using this trait should implement `ValidatableInterface`.
 *
 * @see ValidatableInterface
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
trait ValidatableTrait
{

	/**
	 * Validator instance
	 * @var Validator
	 */
	private $_validator = null;

	/**
	 * Get validation errors. To obtain results, first `validate()` method
	 * must be called.
	 * @return string[]
	 * @Ignored
	 */
	public function getErrors()
	{
		return $this->_getValidator()->getErrors();
	}

	/**
	 * Set errors. Usually should not be called manually.
	 * @param string[] $errors
	 */
	public function setErrors($errors)
	{
		$this->_getValidator()->setErrors($errors);
	}

	/**
	 * Validate model attributes according to current scenario (if applicable)
	 *
	 * The `getErrors()` method can be used to obtains validation messages.
	 *
	 * @return bool
	 * @Ignored
	 */
	public function validate()
	{
		return $this->_getValidator()->validate();
	}

	private function _getValidator()
	{
		if (null === $this->_validator)
		{
			$this->_validator = new Validator($this);
		}
		return $this->_validator;
	}

}
