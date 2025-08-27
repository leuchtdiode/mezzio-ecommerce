<?php
namespace Ecommerce\Rest\Action;

use Ecommerce\Customer\Customer;
use Laminas\Diactoros\Response as DiactorosResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class Base implements RequestHandlerInterface
{
	private ?Customer              $customer = null;
	private ServerRequestInterface $request;

	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		$this->request  = $request;
		$this->customer = $request->getAttribute('customer');

		return $this->executeAction();
	}

	public function getRequest(): ServerRequestInterface
	{
		return $this->request;
	}

	abstract public function executeAction(): ResponseInterface;

	protected function getRouteParam(string $key): mixed
	{
		return $this
			->getRequest()
			->getAttribute($key);
	}

	protected function getCustomer(): ?Customer
	{
		return $this->customer;
	}

	protected function customerCheck(string $customerId): bool
	{
		return $this->customer && $this->customer->getId()
				->toString() === $customerId;
	}

	protected function badRequest(): ResponseInterface
	{
		return new DiactorosResponse\EmptyResponse(400);
	}

	protected function forbidden(): ResponseInterface
	{
		return new DiactorosResponse\EmptyResponse(403);
	}

	protected function notFound(): ResponseInterface
	{
		return new DiactorosResponse\EmptyResponse(404);
	}
}
