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

namespace Maslosoft\Mangan\Decorators;

use Maslosoft\Mangan\Meta\DocumentPropertyMeta;
use Maslosoft\Mangan\Meta\ManganMeta;

/**
 * Embed Ref Decorator is alias for embedded decorator for converting
 * Db Refs into JSON arrays, Document arrays etc.
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class EmbedRefDecorator extends EmbeddedDecorator
{

	protected static function getClassName($model, $name)
	{
		$fieldMeta = ManganMeta::create($model)->$name;

		/* @var $fieldMeta DocumentPropertyMeta */
		if(!empty($fieldMeta->related))
		{
			return $fieldMeta->related->class;
		}
		if(!empty($fieldMeta->embedded))
		{
			return $fieldMeta->embedded->class;
		}
		return $fieldMeta->dbRef->class;
	}

}
