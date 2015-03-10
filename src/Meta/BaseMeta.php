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

namespace Maslosoft\Mangan\Meta;

/**
 * BaseMeta
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
abstract class BaseMeta
{

	public function __construct($data)
	{
		if(is_array($data))
		{
			foreach($data as $key => $value)
			{
				$this->$key = $value;
			}
		}
	}

}
