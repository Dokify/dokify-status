<?php

	$dir = dirname(__FILE__);

	//  --- load all dokify libs and autoloads
	require_once "{$dir}/../public/config.php";

	//  --- Autoload vendor
	require_once "{$dir}/../vendor/autoload.php";

	//  --- Cargar todos los ficheros de config
	foreach (glob("{$dir}/config/*.php") as $file) require_once $file;

	//  --- Cargar todos los ficheros de lib
	foreach (glob("{$dir}/lib/*.php") as $file) require_once $file;
	
	classLoader::register();



	$userLocale = getCurrentLanguage().'.utf8';
	setlocale(LC_MESSAGES, $userLocale);
	$textdomain = "messages";

	// --- Esta funcion falla en MAC de entrada, así voy tirando
	if (function_exists('bindtextdomain')) {
		$text_domain = bindtextdomain($textdomain, dirname(__FILE__) . '/locale');
		bind_textdomain_codeset($textdomain, 'UTF-8');
	}



	date_default_timezone_set('UTC');