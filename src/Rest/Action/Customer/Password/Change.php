<?php
namespace Ecommerce\Rest\Action\Customer\Password;

use Ecommerce\Customer\Password\ChangeHandler;
use Ecommerce\Rest\Action\Base;
use Ecommerce\Rest\Action\Response;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Change extends Base
{
	private ChangeData $data;

	private ChangeHandler $changeHandler;

	public function __construct(ChangeData $data, ChangeHandler $changeHandler)
	{
		$this->data          = $data;
		$this->changeHandler = $changeHandler;
	}

	/**
	 * @throws Throwable
	 */
	public function executeAction(): ResponseInterface
	{
		$customerId = $this->getRouteParam('id');

		if (!$this->customerCheck($customerId))
		{
			return $this->forbidden();
		}

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

		$result = $this->changeHandler->change(
			$this->getCustomer(),
			$values
				->get(ChangeData::PASSWORD)
				->getValue(),
			$values
				->get(ChangeData::PASSWORD_NEW)
				->getValue(),
			$values
				->get(ChangeData::PASSWORD_NEW_VERIFY)
				->getValue()
		);

		if (!$result->isSuccess())
		{
			return Response::is()
				->unsuccessful()
				->errors($result->getErrors())
				->dispatch();
		}

		return Response::is()
			->successful()
			->dispatch();
	}
}
