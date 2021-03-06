<?php

/**
 * This software package is licensed under AGPL or Commercial license.
 *
 * @package   maslosoft/mangan
 * @licence   AGPL or Commercial
 * @copyright Copyright (c) Piotr Masełkowski <pmaselkowski@gmail.com>
 * @copyright Copyright (c) Maslosoft
 * @copyright Copyright (c) Others as mentioned in code
 * @link      https://maslosoft.com/mangan/
 */

namespace Maslosoft\Mangan;

use Exception;
use Maslosoft\Addendum\Interfaces\AnnotatedInterface;
use Maslosoft\Mangan\Exceptions\TransactionException;
use Maslosoft\Mangan\Helpers\CommandProxy;

/**
 * Transaction
 *
 * @author Piotr Maselkowski <pmaselkowski at gmail.com>
 */
class Transaction
{

	public const IsolationMVCC = 'mvcc';
	public const IsolationSerializable = 'serializable';
	public const IsolationReadUncommitted = 'readUncommitted';
	public const CommandBegin = 'beginTransaction';
	public const CommandCommit = 'commitTransaction';
	public const CommandRollback = 'rollbackTransaction';

	/**
	 *
	 * @var CommandProxy
	 */
	private $cmd;

	/**
	 * Whenever transaction is currently active
	 * @var bool
	 */
	private static $isActive = false;

	/**
	 * Whenever transactions are available in current database
	 * @var bool
	 */
	private static $isAvailable;

	/**
	 * Begin new transaction
	 * @param AnnotatedInterface $model
	 * @param string             $isolation
	 */
	public function __construct(AnnotatedInterface $model, $isolation = self::IsolationMVCC)
	{
		$this->cmd = new CommandProxy($model);
		if (null === self::$isAvailable)
		{
			self::$isAvailable = $this->isAvailable();
		}
		if (!self::$isAvailable)
		{
			return;
		}
		if (self::$isActive)
		{
			throw new TransactionException('Transaction is already running');
		}

		$this->cmd->call(self::CommandBegin, [
			'isolation' => $isolation
		]);

		self::$isActive = true;
	}

	public function isAvailable(): bool
	{
		return $this->cmd->isAvailable(self::CommandBegin) && $this->cmd->isAvailable(self::CommandCommit);
	}

	public function commit(): void
	{
		$this->_finish(self::CommandCommit);
	}

	public function rollback(): void
	{
		$this->_finish(self::CommandRollback);
	}

	private function _finish($command): void
	{
		try
		{
			$this->cmd->call($command);
		} catch (Exception $e)
		{
			throw $e;
		} finally
		{
			self::$isActive = false;
		}
	}

}
