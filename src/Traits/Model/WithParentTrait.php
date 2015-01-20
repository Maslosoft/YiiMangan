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

namespace Maslosoft\Models\Traits;

use MongoId;

/**
 * This is trait for models having parent element
 *
 * @author Piotr
 */
trait WithParentTrait
{

	/**
	 * @SafeValidator
	 * @Sanitizer('MongoStringId')
	 * @var MongoId
	 */
	public $parentId = null;

}