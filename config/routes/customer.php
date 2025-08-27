<?php
namespace Ecommerce;

use Common\Router\HttpRouteCreator;
use Ecommerce\Rest\Action\Customer\Activate;
use Ecommerce\Rest\Action\Customer\Get;
use Ecommerce\Rest\Action\Customer\Login;
use Ecommerce\Rest\Action\Customer\Modify;
use Ecommerce\Rest\Action\Customer\Register;

return HttpRouteCreator::create()
	->setRoute('/customer')
	->setMayTerminate(false)
	->setChildRoutes(
		[
			'login'       => HttpRouteCreator::create()
				->setRoute('/login')
				->setMayTerminate(false)
				->setChildRoutes(
					[
						'do' => HttpRouteCreator::create()
							->setAction(Login::class)
							->setMethods([ 'POST' ]),
					]
				),
			'register'    => HttpRouteCreator::create()
				->setRoute('/register')
				->setMayTerminate(false)
				->setChildRoutes(
					[
						'do' => HttpRouteCreator::create()
							->setAction(Register::class)
							->setMethods([ 'POST' ]),
					]
				),
			'password'    => include 'customer/password.php',
			'single-item' => HttpRouteCreator::create()
				->setRoute('/:id')
				->setConstraints(
					[
						'id' => '.{36}',
					]
				)
				->setMayTerminate(false)
				->setChildRoutes(
					[
						'get'         => HttpRouteCreator::create()
							->setAction(Get::class)
							->setMethods([ 'GET' ]),
						'modify'      => HttpRouteCreator::create()
							->setAction(Modify::class)
							->setMethods([ 'PUT' ]),
						'activate'    => HttpRouteCreator::create()
							->setRoute('/activate')
							->setMayTerminate(false)
							->setChildRoutes(
								[
									HttpRouteCreator::create()
										->setAction(Activate::class)
										->setMethods([ 'POST' ]),
								]
							),
						'address'     => include 'customer/address.php',
						'password'    => include 'customer/specific-customer-password.php',
						'transaction' => include 'customer/transaction.php',
					]
				),
		]
	);