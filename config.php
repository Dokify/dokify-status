<?php
	require 'class/AWSStatus.class.php';
	require 'vendor/mustache/mustache/src/Mustache/Autoloader.php';
	Mustache_Autoloader::register();

	function dump($var){
		print '<pre>'. print_r($var, true) . '</pre>';
	}