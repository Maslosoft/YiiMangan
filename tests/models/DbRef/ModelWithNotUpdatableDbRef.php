<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\ManganTest\Models\DbRef;

use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\ManganTest\Models\Plain\SimplePlainDbRef;
use MongoId;

/**
 * ModelWithUpdatableDbRef
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class ModelWithNotUpdatableDbRef implements AnnotatedInterface
{

	/**
	 * @Sanitizer('MongoObjectId')
	 * @var MongoId
	 */
	public $_id;

	/**
	 * @DbRef(class = SimplePlainDbRef, updatable = false)
	 * @var SimplePlainDbRef
	 */
	public $stats = null;

}
