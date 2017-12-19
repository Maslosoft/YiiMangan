<?php
/**
 * Created by PhpStorm.
 * User: peter
 * Date: 17.12.17
 * Time: 17:55
 */

namespace Maslosoft\ManganTest\Models\Indexes;


use Maslosoft\Mangan\Document;
use Maslosoft\Mangan\Sort;

class ModelWithCompoundI18NIndex extends Document
{
	/**
	 * @Index({'username' = Sort::SortDesc, 'email' = Sort::SortDesc}, {'unique' = true, 'sparse' = true})
	 * @Index({'username' = Sort::SortAsc, 'email' = Sort::SortAsc}, {'unique' = true, 'sparse' = true})
	 * @I18N
	 * @see Sort
	 * @var string
	 */
	public $username = '';

	public $email = '';
}