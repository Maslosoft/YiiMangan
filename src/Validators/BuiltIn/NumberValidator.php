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

namespace Maslosoft\Mangan\Validators\BuiltIn;

use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\Mangan\Interfaces\Validators\ValidatorInterface;
use Maslosoft\Mangan\Meta\ManganMeta;
use Maslosoft\Mangan\Validators\Traits\AllowEmpty;
use Maslosoft\Mangan\Validators\Traits\Messages;
use Maslosoft\Mangan\Validators\Traits\OnScenario;
use Maslosoft\Mangan\Validators\Traits\Safe;
use Maslosoft\Mangan\Validators\Traits\SkipOnError;

/**
 * NumberValidator
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class NumberValidator implements ValidatorInterface
{

	use AllowEmpty,
	  Messages,
	  OnScenario,
	  Safe,
	  SkipOnError;

	/**
	 * Whether the attribute value can only be an integer. Defaults to false.
	 * @var boolean
	 */
	public $integerOnly = false;

	/**
	 * Upper limit of the number. Defaults to null, meaning no upper limit.
	 * @var integer|float
	 */
	public $max = NULL;

	/**
	 * Lower limit of the number. Defaults to null, meaning no lower limit.
	 * @var integer|float
	 */
	public $min = NULL;

	/**
	 * Number must be greater than. Defaults to null, meaning no constraint.
	 * @var integer|float
	 */
	public $gt = null;

	/**
	 * Number must be lesser than. Defaults to null, meaning no constraint.
	 * @var integer|float
	 */
	public $lt= null;

	/**
	 * Deprecated: Use `msgTooSmall` instead
	 * @var string user-defined error message used when the value is too big.
	 * @deprecated Use `msgTooSmall` instead
	 */
	public $tooBig = NULL;

	/**
	 * Deprecated: Use `msgTooBig` instead
	 * @var string user-defined error message used when the value is too small.
	 * @deprecated Use `msgTooBig` instead
	 */
	public $tooSmall = NULL;

	/**
	 * Custom message to show if value is not number
	 * @Label('{attribute} must be a number')
	 * @var string
	 */
	public $msgNumber = '';

	/**
	 * Custom message to show if value is not integer when integer checking is
	 * enabled
	 * @Label('{attribute} must be an integer')
	 * @var string
	 */
	public $msgInteger = '';

	/**
	 * Custom message to show if value is over maximum
	 * @Label('{attribute} is too small (minimum is {min})')
	 * @var string
	 */
	public $msgTooSmall = '';

	/**
	 * Custom message to show if value is under minimum
	 * @Label('{attribute} is too big (maximum is {max})')
	 * @var string
	 */
	public $msgTooBig = '';

	/**
	 * Custom message to show if value must be greater than required
	 * @Label('{attribute} must be greater than {gt}')
	 * @var string
	 */
	public $msgGt = '';

	/**
	 * Custom message to show if value is lesser than required
	 * @Label('{attribute} must be lesser than {lt}')
	 * @var string
	 */
	public $msgLt = '';

	public function isValid(AnnotatedInterface $model, $attribute)
	{
		// For lt/gt values exactly zero, the `allowEmpty` **must** be `false`
		// or validation would not be performed at all.
		if($this->lt === 0 || $this->gt === 0 || $this->lt === 0.0 || $this->gt === 0.0)
		{
			$this->allowEmpty = false;
		}
		$value = $model->$attribute;
		if ($this->allowEmpty && empty($value))
		{
			return true;
		}

		$label = ManganMeta::create($model)->field($attribute)->label;
		if (!is_scalar($value))
		{
			$this->addError('msgNumber', ['{attribute}' => $label]);
			return false;
		}
		if (!is_numeric($value))
		{
			$this->addError('msgNumber', ['{attribute}' => $label]);
			return false;
		}
		if ($this->integerOnly)
		{

			if (false === filter_var($value, FILTER_VALIDATE_INT))
			{
				$this->addError('msgInteger', ['{attribute}' => $label]);
				return false;
			}
		}
		else
		{
			if (false === filter_var($value, FILTER_VALIDATE_FLOAT))
			{
				$this->addError('msgNumber', ['{attribute}' => $label]);
				return false;
			}
		}
		if ($this->min !== null && $value < $this->min)
		{
			if (!empty($this->tooSmall))
			{
				$this->addError($this->tooSmall, ['{min}' => $this->min, '{attribute}' => $label]);
				return false;
			}
			$this->addError('msgTooSmall', ['{min}' => $this->min, '{attribute}' => $label]);
			return false;
		}
		if ($this->max !== null && $value > $this->max)
		{
			if (!empty($this->tooBig))
			{
				$this->addError($this->tooBig, ['{max}' => $this->max, '{attribute}' => $label]);
			}
			$this->addError('msgTooBig', ['{max}' => $this->max, '{attribute}' => $label]);
			return false;
		}
		if ($this->gt !== null && !($value > $this->gt))
		{
			$this->addError('msgGt', ['{gt}' => $this->gt, '{attribute}' => $label]);
			return false;
		}
		if ($this->lt !== null && !($value < $this->lt))
		{
			$this->addError('msgLt', ['{lt}' => $this->lt, '{attribute}' => $label]);
			return false;
		}
		return true;
	}

}
