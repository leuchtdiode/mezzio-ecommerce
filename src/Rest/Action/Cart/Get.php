<?php
namespace Ecommerce\Rest\Action\Cart;

use Common\Hydration\ObjectToArrayHydrator;
use Ecommerce\Cart\CouldNotFindCartError;
use Ecommerce\Cart\Provider;
use Ecommerce\Rest\Action\Base;
use Ecommerce\Rest\Action\LoginExempt;
use Ecommerce\Rest\Action\Response;
use Exception;
use Psr\Http\Message\ResponseInterface;

class Get extends Base implements LoginExempt
{
	private Provider $cartProvider;

	public function __construct(Provider $cartProvider)
	{
		$this->cartProvider = $cartProvider;
	}

	/**
	 * @throws Exception
	 */
	public function executeAction(): ResponseInterface
	{
		$cart = $this->cartProvider->byId(
			$this->getRouteParam('cartId')
		);

		if (!$cart)
		{
			return Response::is()
				->unsuccessful()
				->errors([ CouldNotFindCartError::create() ])
				->dispatch();
		}

		return Response::is()
			->successful()
			->data(
				ObjectToArrayHydrator::hydrate(
					GetSuccessData::create()
						->setCart($cart)
				)
			)
			->dispatch();
	}
}
