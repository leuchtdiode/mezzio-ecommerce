<?php
declare(strict_types=1);

namespace Ecommerce\Middleware;

use Ecommerce\Customer\Auth\JwtHandler;
use Ecommerce\Customer\Customer;
use Ecommerce\Rest\Action\LoginExempt;
use Laminas\Diactoros\Response;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class EcommerceMiddleware implements MiddlewareInterface
{
	public function __construct(
		private readonly array $config,
		private readonly JwtHandler $jwtHandler,
	)
	{
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$restConfig = $this->config['ecommerce']['rest'] ?? [];

		if ($restConfig)
		{
			// TODO test me
			/**
			 * @var RouteResult $routeResult
			 */
			$routeResult = $request->getAttribute(RouteResult::class);

			$routeName = $routeResult->getMatchedRouteName();

			$exclusions = $restConfig['nonDisabledRoutes'] ?? [];

			if (($restConfig['disabled'] ?? false) && !in_array($routeName, $exclusions))
			{
				return new Response\EmptyResponse(403);
			}
		}

		$customer = $this->loadCustomer($request);

		if (!$this instanceof LoginExempt && !$customer) // needs login, but not logged in
		{
			return new Response\EmptyResponse(403);
		}

		if ($customer)
		{
			$request = $request->withHeader(
				'X-JwtToken',
				$this->jwtHandler->generate($customer)
			);
		}

		return $handler->handle($request);
	}

	private function loadCustomer(ServerRequestInterface $request): ?Customer
	{
		// TODO test me
		$token = $request->getHeader('Authorization')[0] ?? null;

		if (!$token)
		{
			return null;
		}

		$result = $this->jwtHandler->validate($token);

		if (!$result->isValid())
		{
			return null;
		}

		return $result->getCustomer();
	}
}