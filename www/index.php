<?php

	define('MVCCORE_APP_ROOT', str_replace('\\', '/', dirname(__DIR__)));
	
	include_once('../vendor/autoload.php');

	$app = \App\Bootstrap::Init();

	$app->Dispatch();
