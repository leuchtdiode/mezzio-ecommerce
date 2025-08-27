<?php
namespace Ecommerce\Common;

use Mezzio\Router\RouterInterface;

readonly class UrlProvider
{
	public function __construct(
		private array $config,
		private RouterInterface $router
	)
	{
	}

	/**
	 * @param string[] $params
	 */
	public function get(string $routeName, array $params = []): string
	{
		return sprintf(
			'%s://%s%s',
			$this->config['ecommerce']['url']['protocol'],
			$this->config['ecommerce']['url']['host'],
			$this->router->generateUri($routeName, $params)
		);
	}
}
