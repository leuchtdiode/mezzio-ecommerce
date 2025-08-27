<?php
namespace Ecommerce\Rest\Action\Customer;

use Common\Hydration\ObjectToArrayHydrator;
use Ecommerce\Rest\Action\Base;
use Ecommerce\Rest\Action\Response;
use Exception;
use Psr\Http\Message\ResponseInterface;

class Get extends Base
{
	/**
	 * @throws Exception
	 */
	public function executeAction(): ResponseInterface
	{
		$customerId = $this->getRouteParam('id');

		if (!$this->customerCheck($customerId))
		{
			return $this->forbidden();
		}

		return Response::is()
			->successful()
			->data(
				ObjectToArrayHydrator::hydrate(
					GetSuccessData::create()
						->setCustomer($this->getCustomer())
				)
			)
			->dispatch();
	}
}