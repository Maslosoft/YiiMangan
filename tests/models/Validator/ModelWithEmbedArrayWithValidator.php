<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maslosoft\ManganTest\Models\Validator;

use Maslosoft\Addendum\Interfaces\AnnotatedInterface;

/**
 * ModelWithEmbedWithValidator
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class ModelWithEmbedArrayWithValidator extends AbstractValidatedModel
{

	/**
	 * @EmbeddedArray(EmbeddedModelWithValidator)
	 * @var EmbeddedModelWithValidator
	 */
	public $addresses = [];

}
