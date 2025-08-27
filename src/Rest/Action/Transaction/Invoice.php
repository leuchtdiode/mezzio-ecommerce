<?php
namespace Ecommerce\Rest\Action\Transaction;

use Ecommerce\Rest\Action\Base;
use Ecommerce\Rest\Action\LoginExempt;
use Ecommerce\Transaction\Invoice\FileSystemPathProvider;
use Ecommerce\Transaction\Invoice\SecurityHashHandler;
use Ecommerce\Transaction\Provider as TransactionProvider;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Invoice extends Base implements LoginExempt
{
	public function __construct(
		private readonly TransactionProvider $transactionProvider,
		private readonly FileSystemPathProvider $fileSystemPathProvider,
		private readonly SecurityHashHandler $securityHashHandler
	)
	{
	}

	/**
	 * @throws Throwable
	 */
	public function executeAction(): ResponseInterface
	{
		$transaction = $this->transactionProvider->byId(
			$this->getRouteParam('transactionId')
		);

		if (!$transaction)
		{
			return $this->notFound();
		}

		$hash = $this
					->getRequest()
					->getQueryParams()['sec'] ?? null;

		if (!$hash || !$this->securityHashHandler->valid($hash))
		{
			return $this->forbidden();
		}

		$path = $this->fileSystemPathProvider->get($transaction);

		if (!file_exists($path))
		{
			return $this->notFound();
		}

		return new TextResponse(file_get_contents($path), 200, [
			'Content-type' => 'application/pdf;charset=utf-8',
		]);
	}
}