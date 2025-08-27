<?php
namespace Ecommerce\Rest\Action\Invoice;

use DateTime;
use Ecommerce\Rest\Action\Base;
use Ecommerce\Rest\Action\LoginExempt;
use Ecommerce\Transaction\Invoice\Bulk\GetData;
use Ecommerce\Transaction\Invoice\Bulk\Provider;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Bulk extends Base implements LoginExempt
{
	private BulkData $data;

	private Provider $bulkProvider;

	public function __construct(BulkData $data, Provider $bulkProvider)
	{
		$this->data         = $data;
		$this->bulkProvider = $bulkProvider;
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
			return $this->notFound();
		}

		$result = $this->bulkProvider->get(
			GetData::create()
				->setDateStart(
					new DateTime(
						$values->getRawValue(BulkData::DATE_START)
					)
				)
				->setDateEnd(
					new DateTime(
						$values->getRawValue(BulkData::DATE_END)
					)
				)
		);

		if (!$result->hasContent())
		{
			return $this->notFound();
		}

		$content = $result->getContent();

		return new TextResponse($content, 200, [
			'Content-type' => 'application/pdf;charset=utf-8',
		]);
	}
}