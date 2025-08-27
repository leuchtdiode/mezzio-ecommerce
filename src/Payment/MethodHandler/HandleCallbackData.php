<?php
namespace Ecommerce\Payment\MethodHandler;

use Ecommerce\Transaction\Transaction;
use Psr\Http\Message\ServerRequestInterface;

class HandleCallbackData
{
	private Transaction            $transaction;
	private string                 $type;
	private ServerRequestInterface $request;

	public static function create(): self
	{
		return new self();
	}

	public function getTransaction(): Transaction
	{
		return $this->transaction;
	}

	public function setTransaction(Transaction $transaction): HandleCallbackData
	{
		$this->transaction = $transaction;
		return $this;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function setType(string $type): HandleCallbackData
	{
		$this->type = $type;
		return $this;
	}

	public function getRequest(): ServerRequestInterface
	{
		return $this->request;
	}

	public function setRequest(ServerRequestInterface $request): HandleCallbackData
	{
		$this->request = $request;
		return $this;
	}
}
