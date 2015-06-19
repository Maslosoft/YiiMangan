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

namespace Maslosoft\Mangan\Sanitizers;

use Maslosoft\Mangan\Interfaces\Sanitizers\Property\SanitizerInterface;

/**
 * String
 * This sanitizer forces values to be string
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class StringSanitizer implements SanitizerInterface
{

	public function read($model, $dbValue)
	{
		return (string) $dbValue;
	}

	public function write($model, $phpValue)
	{
		return (string) $phpValue;
	}

}
