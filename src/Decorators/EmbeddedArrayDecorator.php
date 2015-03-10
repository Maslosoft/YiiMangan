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

namespace Maslosoft\Mangan\Decorators;

use Maslosoft\Mangan\Interfaces\IOwnered;
use Maslosoft\Mangan\Transformers\ITransformator;

/**
 * EmbeddedArrayDecorator
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class EmbeddedArrayDecorator implements IDecorator
{

	public function read($model, $name, &$dbValue, $transformatorClass = ITransformator::class)
	{
		if (is_array($dbValue))
		{
			$docs = [];
			foreach ($dbValue as $key => $data)
			{
				EmbeddedDecorator::ensureClass($model, $name, $data);
				$embedded = $transformatorClass::toModel($data);
				if ($embedded instanceof IOwnered)
				{
					$embedded->setOwner($model);
				}
				$docs[$key] = $embedded;
			}
			$model->$name = $docs;
		}
		else
		{
			$model->$name = $dbValue;
		}
	}

	public function write($model, $name, &$dbValue, $transformatorClass = ITransformator::class)
	{
		if (is_array($model->$name))
		{
			$dbValue[$name] = [];
			$key = 0;
			foreach ($model->$name as $key => $document)
			{
				$data = $transformatorClass::fromModel($document);
				$dbValue[$name][$key] = $data;
			}
		}
		else
		{
			$dbValue[$name] = $model->$name;
		}
	}

}
