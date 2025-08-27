<?php
namespace Ecommerce\Rest\Action\Product;

use Common\Db\FilterChain;
use Common\Hydration\ObjectToArrayHydrator;
use Ecommerce\Product\Provider;
use Ecommerce\Rest\Action\Base;
use Ecommerce\Rest\Action\LoginExempt;
use Ecommerce\Rest\Action\Response;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class GetList extends Base implements LoginExempt
{
	private Provider $provider;

	public function __construct(Provider $provider)
	{
		$this->provider = $provider;
	}

	/**
	 * @throws Throwable
	 */
	public function executeAction(): ResponseInterface
	{
		$filterChain = FilterChain::create();

		$products = $this->provider->filter($filterChain);

		return Response::is()
			->successful()
			->data(
				ObjectToArrayHydrator::hydrate($products)
			)
			->dispatch();
	}
}
