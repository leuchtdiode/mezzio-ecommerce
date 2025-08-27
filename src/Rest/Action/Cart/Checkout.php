<?php
namespace Ecommerce\Rest\Action\Cart;

use Common\Db\FilterChain;
use Common\Hydration\ObjectToArrayHydrator;
use Ecommerce\Address\Address;
use Ecommerce\Address\Provider as AddressProvider;
use Ecommerce\Cart\Checkout\CheckoutData as CheckoutHandlerCheckoutData;
use Ecommerce\Cart\Checkout\Handler as CheckoutHandler;
use Ecommerce\Cart\CouldNotFindCartError;
use Ecommerce\Cart\Provider as CartProvider;
use Ecommerce\Db\Address\Filter\Customer;
use Ecommerce\Payment\MethodProvider as PaymentMethodProvider;
use Ecommerce\Rest\Action\Base;
use Ecommerce\Rest\Action\Response;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Checkout extends Base
{
	private CheckoutData $data;

	private CartProvider $cartProvider;

	private AddressProvider $addressProvider;

	private PaymentMethodProvider $paymentMethodProvider;

	private CheckoutHandler $checkoutHandler;

	public function __construct(
		CheckoutData $data,
		CartProvider $cartProvider,
		AddressProvider $addressProvider,
		PaymentMethodProvider $paymentMethodProvider,
		CheckoutHandler $checkoutHandler
	)
	{
		$this->data                  = $data;
		$this->cartProvider          = $cartProvider;
		$this->addressProvider       = $addressProvider;
		$this->paymentMethodProvider = $paymentMethodProvider;
		$this->checkoutHandler       = $checkoutHandler;
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

		$billingAddress  = $this->getAddress(
			$values->get(CheckoutData::BILLING_ADDRESS_ID)
				->getValue()
		);
		$shippingAddress = $this->getAddress(
			$values->get(CheckoutData::SHIPPING_ADDRESS_ID)
				->getValue()
		);

		if (!$billingAddress || !$shippingAddress)
		{
			return $this->forbidden();
		}

		$result = $this->checkoutHandler->checkout(
			CheckoutHandlerCheckoutData::create()
				->setCart($cart)
				->setCustomer($this->getCustomer())
				->setPaymentMethod(
					$this->paymentMethodProvider->byId(
						$values
							->get(CheckoutData::PAYMENT_METHOD)
							->getValue()
					)
				)
				->setBillingAddress($billingAddress)
				->setShippingAddress($shippingAddress)
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
					CheckoutSuccessData::create()
						->setRedirectUrl($result->getRedirectUrl())
				)
			)
			->dispatch();
	}

	/**
	 * @throws Exception
	 */
	private function getAddress(string $addressId): ?Address
	{
		$customerId = $this
			->getCustomer()
			->getId();

		$addresses = $this->addressProvider->filter(
			FilterChain::create()
				->addFilter(Customer::is($customerId))
		);

		foreach ($addresses as $address)
		{
			if ($address->getId()
					->toString() === $addressId)
			{
				return $address;
			}
		}

		return null;
	}
}
