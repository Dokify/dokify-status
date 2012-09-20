<?php

	require 'config.php';

	$aws = new AWSStatus();
	$autoscaler = $aws->getAutoScaler();

	$json = array();

	$json['grupo'] = array();
	$json['grupo']['nombre'] = (string)$autoscaler->getName();
	$json['grupo']['capacidad'] = (string)$autoscaler->getCapacity();

	$action = $autoscaler->getLastActivity();
	$json['grupo']['ultimaAccion'] = array(
				'descripcion' 	=> $action->Description, 
				'hora' 			=> $action->StartTime, 
				'estado' 		=> $action->StatusCode, 
				'class' 		=> ($action->StatusCode=="Successful"?'success':'error') 
				);

	$instances = $autoscaler->getInstances();
	foreach($instances as $instance){
		$instance = array(
				'nombre' 			=> $instance->getID(),
				'estado' 			=> $instance->getState(),
				'class' 			=> ($instance->getState()=="running"?'correcto':'incorrecto'), 
				'zona' 				=> $instance->getZone(), 
				'procesos' 			=> $instance->getProcesos(), 
				'memoria' 			=> $instance->getMemoria(), 
				'estadoBalanceador' => $instance->getBalancerStatus(),
				'class2' 			=> ($instance->getBalancerStatus()=="InService"?'correcto':'incorrecto'), 
				'porcentaje' 		=> $instance->getPorcentaje(), 
				'progress'  		=> $instance->getProgress(),
				'cpu' 				=> $instance->getCpu()
				);
		$json['grupo']['instances'][]=$instance;
	}

	function httpaccepts(){
   		return explode ("," , $_SERVER["HTTP_ACCEPT"]);
   	}

	if( in_array("application/json", httpaccepts()) ) {
		header('Access-Control-Allow-Origin: *');
		header("Content-Type: application/json");

		print json_encode($json['grupo']);
	} else {
		require './vendor/mustache/mustache/src/Mustache/Autoloader.php';
		Mustache_Autoloader::register();

		$m = new Mustache_Engine;
		echo $m->render(file_get_contents("plantilla.html"), $json['grupo']); 
		exit();	
	}