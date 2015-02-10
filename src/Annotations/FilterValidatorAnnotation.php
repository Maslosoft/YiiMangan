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

namespace Maslosoft\Mangan\Annotations;

use Maslosoft\Mangan\Base\ValidatorAnnotation;
use Maslosoft\Mangan\Base\IBuiltInValidatorAnnotation;

/**
 * NOTE: This class is automatically generated from Yii validator class.
 * This is not actual validator. For validator class @see CFilterValidator.
 */

/**
 * CFilterValidator transforms the data being validated based on a filter.
 *
 * CFilterValidator is actually not a validator but a data processor.
 * It invokes the specified filter method to process the attribute value
 * and save the processed value back to the attribute. The filter method
 * must follow the following signature:
 * <pre>
 * function foo($value) {...return $newValue; }
 * </pre>
 * Many PHP functions qualify this signature (e.g. trim).
 *
 * To specify the filter method, set {@link filter} property to be the function name.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.validators
 * @since 1.0
 */
class FilterValidatorAnnotation extends ValidatorAnnotation implements IBuiltInValidatorAnnotation
{

	/**
	 * @var callback the filter method
	 */
	public $filter = NULL;

}