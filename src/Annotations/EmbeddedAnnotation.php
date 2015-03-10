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

namespace Maslosoft\Mangan\Annotations;

use Maslosoft\Mangan\Decorators\EmbeddedDecorator;
use Maslosoft\Mangan\Meta\ManganPropertyAnnotation;
use Maslosoft\Mangan\Sanitizers\Embedded;

/**
 * Annotation for embedded document in mongo
 * defaultClassName will be used for getting empty properties,
 * but any type of embedded document can be stored within this field
 * Examples:
 * <ul>
 * 		<li><b>Embedded(Company\Product\EmbeddedClassName)</b>: Embed with namespaced class literal</li>
 * 		<li><b>Embedded(EmbeddedClassName)</b>: Embed with default class</li>
 * </ul>
 * @Target('property')
 * @template Embedded(${defaultClassName})
 * @author Piotr
 */
class EmbeddedAnnotation extends ManganPropertyAnnotation
{

	public $value = true;

	public function init()
	{
		$this->_entity->embedded = $this->value;
		$this->_entity->decorators[] = EmbeddedDecorator::class;
	}

}
