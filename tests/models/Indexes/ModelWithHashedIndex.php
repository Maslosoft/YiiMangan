<?php
/**
 * Created by PhpStorm.
 * User: peter
 * Date: 17.12.17
 * Time: 18:39
 */

namespace Maslosoft\ManganTest\Models\Indexes;


use Maslosoft\Mangan\Document;
use Maslosoft\Mangan\Helpers\IndexManager;

class ModelWithHashedIndex extends Document
{
	/**
	 * @Index(IndexManager::IndexTypeHashed)
	 *
	 * @see IndexManager
	 * @var string
	 */
	public $url = '';
}