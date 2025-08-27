<?php
namespace Ecommerce\Rest\Action\Product;

use Common\Hydration\ObjectToArrayHydrator;
use Ecommerce\Product\Provider;
use Ecommerce\Rest\Action\Base;
use Ecommerce\Rest\Action\LoginExempt;
use Ecommerce\Rest\Action\Response;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Get extends Base implements LoginExempt
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
		$product = $this->provider->byId(
			$this->getRouteParam('productId')
		);

		if (!$product)
		{
			return Response::is()
				->unsuccessful()
				->dispatch();
		}

		return Response::is()
			->successful()
			->data(
				ObjectToArrayHydrator::hydrate(
					$product
				)
			)
			->dispatch();
	}
}
