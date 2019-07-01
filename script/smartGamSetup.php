<?php

putenv('HOME='.dirname(__DIR__)."/../");
require __DIR__.'/../vendor/autoload.php';

$applicationName = "Insideall - Test 1";
$jsonKeyFilePath = "/home/gabriel/dfp/googleServiceAccount.json";
$scopes = "https://www.googleapis.com/auth/dfp";
$impersonatedEmail = "insideall@headerbidding-199413.iam.gserviceaccount.com";

$networkCode = 21700827184;
$currency = "EUR";

$credentials = array(
	"networkCode" => $networkCode,
	"applicationName" => $applicationName,
	"jsonKeyFilePath" => $jsonKeyFilePath,
	"impersonatedEmail" => $impersonatedEmail  
);


$script = new \App\Scripts\SmartGamScript();

$script->setCredentials($credentials)
	->setCurrency($currency)
	->CreateSmartGamCampaign()
	->clearCredentials();
