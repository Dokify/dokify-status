<?php

	require 'config.php';

	$aws = new AWSStatus();
	
	$autoscaler = $aws->getAutoScaler();
	$instances = $autoscaler->getInstances();
	$action = $autoscaler->getLastActivity();	
	
	$m = new Mustache_Engine;
	echo $m->render(file_get_contents("status.html"), array(
		'instances' 	=> $instances,
		'action'		=> $action,
		'autoscaler'    => $autoscaler
	));

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