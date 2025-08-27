<?php
namespace Ecommerce\Rest\Action\Customer;

use Ecommerce\Customer\Auth\ActivateHandler;
use Ecommerce\Rest\Action\Base;
use Ecommerce\Rest\Action\LoginExempt;
use Ecommerce\Rest\Action\Response;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Activate extends Base implements LoginExempt
{
	private ActivateData $data;

	private ActivateHandler $activateHandler;

	public function __construct(ActivateData $data, ActivateHandler $activateHandler)
	{
		$this->data            = $data;
		$this->activateHandler = $activateHandler;
	}

	/**
	 * @throws Throwable
	 */
	public function executeAction(): ResponseInterface
	{
		$values = $this->data
			->setRequest($this->getRequest())
			->getValues();

		if ($values->hasErrors())
		{
			return Response::is()
				->unsuccessful()
				->errors($values->getErrors())
				->dispatch();
		}

		$result = $this->activateHandler->activate(
			$this->getRouteParam('id'),
			$values->get(ActivateData::CREATED_DATE)
				->getValue()
		);

		if ($result->isSuccess())
		{
			return Response::is()
				->successful()
				->dispatch();
		}

		return Response::is()
			->unsuccessful()
			->errors($result->getErrors())
			->dispatch();
	}
}