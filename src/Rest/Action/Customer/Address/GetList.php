<?php
namespace Ecommerce\Rest\Action\Customer\Address;

use Common\Db\FilterChain;
use Common\Hydration\ObjectToArrayHydrator;
use Ecommerce\Address\Provider;
use Ecommerce\Db\Address\Filter\Customer;
use Ecommerce\Rest\Action\Base;
use Ecommerce\Rest\Action\Response;
use Exception;
use Psr\Http\Message\ResponseInterface;

class GetList extends Base
{
	private Provider $provider;

	public function __construct(Provider $provider)
	{
		$this->provider = $provider;
	}

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

		$addresses = $this->provider->filter(
			FilterChain::create()
				->addFilter(
					Customer::is($customerId)
				)
		);

		return Response::is()
			->successful()
			->data(ObjectToArrayHydrator::hydrate(
				GetListSuccessData::create()
					->setAddresses($addresses)
			))
			->dispatch();
	}
}