<?php

	require 'config.php';


	$cachePath = dirname(__FILE__) . "/" . AWSStatus::CACHE_FILE;
	
	if( isset($_SERVER['argv']) && isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] === 'cache' ){

		$aws = new AWSStatus();
	
		$autoscaler = $aws->getAutoScaler();
		$instances = $autoscaler->getInstances();
		$action = $autoscaler->getLastActivity();

		// Vamos a guardar en cache las informacion que nos devuelve amazon
		$cache = array();

		$cache['instances'] = array();
		foreach( $instances as $instance) {
			$cache['instances'][] = $instance->toArray();
		}

		$cache['action'] = (array) $action;
		echo('cache');
		echo($cache['action']);
		$json=json_encode($cache);
		file_put_contents($cachePath, $json);
		$persist="/var/dokify/dev-tools/persist.php";
		if (file_exists($persist)){
			$_SERVER['argv'][1]="aws-status";
			$_SERVER['argv'][2]=$json;
			require $persist;
		}
	} else {
		$data = json_decode(file_get_contents($cachePath));
		$m = new Mustache_Engine;
		echo $m->render(file_get_contents("status.html"), $data);
	}	


	/*if( in_array("application/json", httpaccepts()) ) {
		header('Access-Control-Allow-Origin: *');
		header("Content-Type: application/json");
		print json_encode($json['grupo']);
	} else {
		require './vendor/mustache/mustache/src/Mustache/Autoloader.php';
		Mustache_Autoloader::register();
		$m = new Mustache_Engine;
		echo $m->render(file_get_contents("plantilla.html"), $json['grupo']); 
	}*/
