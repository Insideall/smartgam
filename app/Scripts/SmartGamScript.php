<?php

namespace App\Scripts;

class SmartGamScript
{
	protected $traffikerId;
	protected $advertiserId;
	protected $orderId;
	protected $keyId;
	protected $adsApi;
	protected $currency = "";

	public function setCredentials($credentials)
	{
		$this->adsApi = new \App\Scripts\AdsApiGenerator;
		$this->adsApi->setCredentials($credentials)
			->generateAdsApi();
		return $this;
	}

	public function clearCredentials()
	{
		
		$this->adsApi->deleteAdsApi();
	}

	public function CreateSmartGamCampaign()
	{
		if($this->currency == ""){
			die("Currency needs to be set");
		}
		$this->orderName = "Smart Holistic Tool";
		$this->advertiserName = "Smart Holistic Tool";
		$this->priceGranularity = "dense";
		$this->sizes = [[120,600],[160,600],[300,50],[300,100],[300,250],[300,600],[300,1000],[320,50],[320,100],[336,280],[728,90],[970,90],[970,150],[970,250],[1000,90],[1000,200],[1000,250],[1000,300],[1800,1000]];
		$this->priceKeyName = "smart_cpm";
		$this->createLineItems();

		return $this;
	}

	public function createLineItems()
	{

		$this->valuesList = Buckets::createBuckets($this->priceGranularity);

		//Get the Trafficker Id
		$this->traffickerId = (new \App\AdManager\UserManager())->getUserId();
		echo 'TraffickerId: '.$this->traffickerId."\n";

		

		//Get the Advertising Company Id
		$this->advertiserId = (new \App\AdManager\CompanyManager())->setUpCompany($this->advertiserName);
		echo 'AdvertiserName : '.$this->advertiserName."\tAdvertiserId: ".$this->advertiserId."\n";

		//Get the OrderId
		$this->orderId = (new \App\AdManager\OrderManager())->setUpOrder($this->orderName, $this->advertiserId, $this->traffickerId);
		echo 'OrderName : '.$this->orderName."\tOrderId: ".$this->orderId."\n";


		//Create and get KeyIds
		$this->priceKeyId = (new \App\AdManager\KeyManager())->setUpCustomTargetingKey($this->priceKeyName);
		echo 'PriceKeyName : '.$this->priceKeyName."\tPriceKeyId: ".$this->priceKeyId."\n";

		//Create and get Values
		$valuesManager = new \App\AdManager\ValueManager();
		$valuesManager->setKeyId($this->priceKeyId);
		$this->dfpValuesList = $valuesManager->convertValuesListToDFPValuesList($this->valuesList);
		echo "Values List Created\n";

		
		$creativeManager = new \App\AdManager\CreativeManager();
		$creativeManager->setAdvertiserId($this->advertiserId);
		$this->creativesList = $creativeManager->setUpCreatives();


		echo "\n\n".json_encode($this->creativesList)."\n\n";
		$this->rootAdUnitId = (new \App\AdManager\RootAdUnitManager())->setRootAdUnit();
		echo 'rootAdUnitId: '.$this->rootAdUnitId."\n";

		$i = 0;

		foreach ($this->dfpValuesList as $dfpValue) {
			$lineItemManager = new \App\AdManager\LineItemManager();
			$lineItemManager->setOrderId($this->orderId)
				->setSizes($this->sizes)
				->setCurrency($this->currency)
				->setKeyId($this->priceKeyId)
				->setValueId($dfpValue['valueId'])
				->setBucket($dfpValue['valueName'])
				->setRootAdUnitId($this->rootAdUnitId)
				->setLineItemName();
			$lineItem = $lineItemManager->setUpLineItem();
			$licaManager = new \App\AdManager\LineItemCreativeAssociationManager();
			$licaManager->setLineItem($lineItem)
				->setCreativeList($this->creativesList)
				->setSizeOverride($this->sizes)
				->setUpLica();

			++$i;
			echo "\n\nLine Item SmartGam_".$dfpValue['valueName']." created/updated.\n";
			

			echo round(($i / count($this->dfpValuesList)) * 100, 1)."% done\n\n";
		}
		
		(new \App\AdManager\OrderManager())->approveOrder($this->orderId);
		
	}

	public function setCurrency($currency)
	{
		$this->currency = $currency;
		return $this;
	}
}
