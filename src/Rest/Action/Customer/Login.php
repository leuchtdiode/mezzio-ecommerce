<?php
namespace Ecommerce\Rest\Action\Customer;

use Common\Hydration\ObjectToArrayHydrator;
use Ecommerce\Customer\Auth\LoginData as CustomerLoginData;
use Ecommerce\Customer\Auth\LoginHandler;
use Ecommerce\Rest\Action\Base;
use Ecommerce\Rest\Action\LoginExempt;
use Ecommerce\Rest\Action\Response;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Login extends Base implements LoginExempt
{
	private LoginData $data;

	private LoginHandler $loginHandler;

	public function __construct(LoginData $data, LoginHandler $loginHandler)
	{
		$this->data         = $data;
		$this->loginHandler = $loginHandler;
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

		$loginResult = $this->loginHandler->login(
			CustomerLoginData::create()
				->setEmail($values->get(LoginData::EMAIL)
					->getValue())
				->setPassword($values->get(LoginData::PASSWORD)
					->getValue())
		);

		if ($loginResult->isSuccess())
		{
			return Response::is()
				->successful()
				->data(
					ObjectToArrayHydrator::hydrate(
						LoginSuccessData::create()
							->setJwtToken($loginResult->getJwtToken())
							->setCustomer($loginResult->getCustomer())
					)
				)
				->dispatch();
		}

		return Response::is()
			->unsuccessful()
			->errors($loginResult->getErrors())
			->dispatch();
	}
}