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

namespace Maslosoft\Mangan\Meta;

use Maslosoft\Addendum\Collections\Meta;
use Maslosoft\Addendum\Interfaces\IAnnotated;
use Maslosoft\Addendum\Options\MetaOptions;
use Maslosoft\Mangan\Options\ManganMetaOptions;

/**
 * Mangan metadata container class
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class ManganMeta extends Meta
{

	/**
	 * TODO Move this to class constructor
	 * Create instance of Metadata specifically designed for Mangan
	 * @param IAnnotated $component
	 * @param MetaOptions $options
	 * @return ManganMeta
	 */
	public static function create(IAnnotated $component, MetaOptions $options = null)
	{
		if (null === $options)
		{
			$options = new ManganMetaOptions();
		}
		return parent::create($component, $options);
	}

	/**
	 * Get document type meta
	 * @return DocumentTypeMeta
	 */
	public function type()
	{
		return parent::type();
	}

	/**
	 * Get field by name
	 * @param string $name
	 * @return DocumentPropertyMeta
	 */
	public function field($name)
	{
		return parent::field($name);
	}

}
