<?php
namespace Ecommerce\Rest\Action\Payment;

use Ecommerce\Db\Transaction\Saver as TransactionEntitySaver;
use Ecommerce\Payment\MethodHandler\HandleCallbackData;
use Ecommerce\Payment\MethodHandler\Provider as MethodHandlerProvider;
use Ecommerce\Payment\ReturnUrl\GetData;
use Ecommerce\Payment\ReturnUrl\Provider as ReturnUrlProvider;
use Ecommerce\Rest\Action\Base;
use Ecommerce\Rest\Action\LoginExempt;
use Ecommerce\Transaction\Provider as TransactionProvider;
use Exception;
use Laminas\Diactoros\Response;
use Log\Log;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Callback extends Base implements LoginExempt
{
	const METHOD = 'method';
	const TYPE   = 'type';

	private TransactionProvider $transactionProvider;

	private MethodHandlerProvider $methodHandlerProvider;

	private TransactionEntitySaver $transactionEntitySaver;

	private ReturnUrlProvider $returnUrlProvider;

	public function __construct(
		TransactionProvider $transactionProvider,
		MethodHandlerProvider $methodHandlerProvider,
		TransactionEntitySaver $transactionEntitySaver,
		ReturnUrlProvider $returnUrlProvider
	)
	{
		$this->transactionProvider    = $transactionProvider;
		$this->methodHandlerProvider  = $methodHandlerProvider;
		$this->transactionEntitySaver = $transactionEntitySaver;
		$this->returnUrlProvider      = $returnUrlProvider;
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
			return $this->badRequest();
		}

		$type = $this->getRouteParam(self::TYPE);

		$methodHandler = $this->methodHandlerProvider->getHandler(
			$transaction->getPaymentMethod()
		);

		$handleCallbackResult = $methodHandler->handleCallback(
			HandleCallbackData::create()
				->setTransaction($transaction)
				->setType($type)
				->setRequest($this->getRequest())
		);

		$transactionEntity = $transaction->getEntity();
		$transactionEntity->setStatus($handleCallbackResult->getTransactionStatus());

		if (($foreignId = $handleCallbackResult->getForeignId()))
		{
			$transactionEntity->setForeignId($foreignId);
		}

		try
		{
			$this->transactionEntitySaver->save($transactionEntity);
		}
		catch (Exception $ex)
		{
			Log::error($ex);
		}

		if (!$handleCallbackResult->isRedirect())
		{
			return new Response\EmptyResponse(204);
		}

		$customer = $transaction->getCustomer();

		return new Response\RedirectResponse(
			$this->returnUrlProvider->get(
				GetData::create()
					->setLocale($customer->getLocale())
					->setCallbackType($type)
			)
		);
	}
}