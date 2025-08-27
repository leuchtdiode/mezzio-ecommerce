<?php
namespace Ecommerce\Rest\Action\Customer;

use Common\Hydration\ObjectToArrayHydrator;
use Ecommerce\Customer\Modifier;
use Ecommerce\Customer\ModifyData as CustomerModifyData;
use Ecommerce\Rest\Action\Base;
use Ecommerce\Rest\Action\Response;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Modify extends Base
{
	private ModifyData $data;

	private Modifier $modifier;

	public function __construct(ModifyData $data, Modifier $modifier)
	{
		$this->data     = $data;
		$this->modifier = $modifier;
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

		$result = $this->modifier->modify(
			$this->getCustomer(),
			CustomerModifyData::create()
				->setSalutation(
					$values
						->get(ModifyData::SALUTATION)
						->getValue()
				)
				->setTitle(
					$values
						->get(ModifyData::TITLE)
						->getValue()
				)
				->setFirstName(
					$values
						->get(ModifyData::FIRST_NAME)
						->getValue()
				)
				->setLastName(
					$values
						->get(ModifyData::LAST_NAME)
						->getValue()
				)
				->setCompany(
					$values
						->get(ModifyData::COMPANY)
						->getValue()
				)
				->setTaxNumber(
					$values
						->get(ModifyData::TAX_NUMBER)
						->getValue()
				)
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
			->data(
				ObjectToArrayHydrator::hydrate(
					GetSuccessData::create()
						->setCustomer($result->getCustomer())
				)
			)
			->dispatch();
	}
}