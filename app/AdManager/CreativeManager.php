<?php

namespace App\AdManager;

use Google\AdsApi\AdManager\Util\v201911\StatementBuilder;
use Google\AdsApi\AdManager\v201911\CreativeService;
use Google\AdsApi\AdManager\v201911\ThirdPartyCreative;
use Google\AdsApi\AdManager\v201911\Size;
use Google\AdsApi\AdManager\v201911\ApiException;

class CreativeManager extends Manager
{
	protected $ssp;
	protected $advertiserId;

	public function setSsp($ssp)
	{
		$this->ssp = $ssp;

		return $this;
	}

	public function setAdvertiserId($advertiserId)
	{
		$this->advertiserId = $advertiserId;

		return $this;
	}

	public function setUpCreatives()
	{
		$safeframe = false;
		
		$output = [];
		//Create a creativeName List
		$creativeNameList = [];
		for ($i = 1; $i <= 10; ++$i) {
			if (empty($this->ssp)) {
				array_push($creativeNameList, "SmartGam_Creative_$i");
			} else {
				array_push($creativeNameList, ucfirst($this->ssp)."_SmartGam_Creative_$i");
			}
		}

		foreach ($creativeNameList as $creativeName) {
			if (empty(($foo = $this->getCreative($creativeName)))) {
				$foo = $this->createCreative($creativeName, $this->createSnippet(), $this->advertiserId, $safeframe);
			} else {
				$foo = $this->updateCreative($creativeName, $this->createSnippet(), $this->advertiserId, $safeframe);
			}
			array_push($output, $foo[0]);
		}

		return $output;
	}

	public function getAllCreatives()
	{
		$output = [];
		$creativeService = $this->serviceFactory->createCreativeService($this->session);
		$pageSize = StatementBuilder::SUGGESTED_PAGE_LIMIT;
		$statementBuilder = (new StatementBuilder())->orderBy('id ASC')
			->limit($pageSize);

		$totalResultSetSize = 0;
		do {
			$data = $creativeService->getCreativesByStatement($statementBuilder->toStatement());
			if (null == $data->getResults()) {
				return $output;
			}
			foreach ($data->getResults() as $creative) {
				$foo = [
					'creativeId' => $creative->getId(),
					'creativeName' => $creative->getName(),
				];

				array_push($output, $foo);
				$statementBuilder->increaseOffsetBy($pageSize);
			}
		} while ($statementBuilder->getOffset() < $totalResultSetSize);

		return $output;
	}

	public function getCreative($creativeName)
	{
		$output = [];
		$creativeService = $this->serviceFactory->createCreativeService($this->session);
		$statementBuilder = (new StatementBuilder())
			->orderBy('id ASC')
			->where('name = :name AND advertiserId = :advertiserId')
			->WithBindVariableValue('name', $creativeName)
			->WithBindVariableValue('advertiserId', $this->advertiserId);
		do{
			try{
				$data = $creativeService->getCreativesByStatement($statementBuilder->toStatement());
			} catch (ApiException $Exception) {
				echo "\n\n======EXCEPTION======\n\n";
				$ApiErrors = $Exception->getErrors();
				foreach ($ApiErrors as $Error) {
					printf(
						"There was an error on the field '%s', caused by an invalid value '%s', with the error message '%s'\n",
					$Error->getFieldPath(),
					$Error->getTrigger(),
					$Error->getErrorString()
					);
				}
				++$attempts;
				sleep(30);
				continue;
			}
			break;
		} while ($attempts < 5);
		if (null !== $data->getResults()) {
			foreach ($data->getResults() as $creative) {
				$foo = [
					'creativeId' => $creative->getId(),
					'creativeName' => $creative->getName(),
				];
				array_push($output, $foo);
			}
		}

		return $output;
	}

	public function createCreative($creativeName, $snippet, $advertiserId, $safeframe)
	{
		$output = [];
		$creativeService = $this->serviceFactory->createCreativeService($this->session);
		$size = new Size();
		$size->setWidth(1);
		$size->setHeight(1);
		$size->setIsAspectRatio(false);

		$creative = new ThirdPartyCreative();

		$creative->setName($creativeName)
			->setAdvertiserId($advertiserId)
			->setIsSafeFrameCompatible($safeframe)
			->setSnippet($snippet)
			->setSize($size);

		// Create the order on the server.
		do{
			try{
				$results = $creativeService->createCreatives([$creative]);
			} catch (ApiException $Exception) {
				echo "\n\n======EXCEPTION======\n\n";
				$ApiErrors = $Exception->getErrors();
				foreach ($ApiErrors as $Error) {
					printf(
						"There was an error on the field '%s', caused by an invalid value '%s', with the error message '%s'\n",
					$Error->getFieldPath(),
					$Error->getTrigger(),
					$Error->getErrorString()
					);
				}
				++$attempts;
				sleep(30);
				continue;
			}
			break;
		} while ($attempts < 5);
		foreach ($results as $creative) {
			$foo = [
				'creativeId' => $creative->getId(),
				'creativeName' => $creative->getName(),
			];
			array_push($output, $foo);
		}

		return $output;
	}

	public function updateCreative($creativeName, $snippet, $advertiserId, $safeframe)
	{
		$output = [];
		$creativeService = $this->serviceFactory->createCreativeService($this->session);
		$statementBuilder = (new StatementBuilder())->where('name = :name')
            ->orderBy('id ASC')
            ->limit(1)
            ->withBindVariableValue('name', $creativeName);
        // Get the creative.
        $page = $creativeService->getCreativesByStatement(
            $statementBuilder->toStatement()
        );

        $creative = $page->getResults()[0];
		$size = new Size();
		$size->setWidth(1);
		$size->setHeight(1);
		$size->setIsAspectRatio(false);

		$creative->setName($creativeName)
			->setAdvertiserId($advertiserId)
			->setIsSafeFrameCompatible($safeframe)
			->setSnippet($snippet)
			->setSize($size);

		// Create the order on the server.
		do {
			try {
				$results = $creativeService->updateCreatives([$creative]);
			} catch (ApiException $Exception) {
				echo "\n\n======EXCEPTION======\n\n";
				$ApiErrors = $Exception->getErrors();
				foreach ($ApiErrors as $Error) {
					printf(
						"There was an error on the field '%s', caused by an invalid value '%s', with the error message '%s'\n",
					$Error->getFieldPath(),
					$Error->getTrigger(),
					$Error->getErrorString()
					);
				}
				++$attempts;
				sleep(30);
				continue;
			}
			break;
		} while ($attempts < 5);
		
		foreach ($results as $creative) {
			$foo = [
				'creativeId' => $creative->getId(),
				'creativeName' => $creative->getName(),
			];
			array_push($output, $foo);
		}

		return $output;
	}

	

	private function createSnippet()
	{
		$snippet = "<script>\n";
		$snippet .= "\tconsole.log('SmartGam')\n";
		$snippet .= "</script>\n";
		return $snippet;

	}

	

	
}
