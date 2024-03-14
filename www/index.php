<?php

	include_once('../vendor/autoload.php');

	$app = \App\Bootstrap::Init();

	$app->Dispatch();
