<?php
	//namespace Dokify;

	// Lo primero es definir la carpeta de los archivos de configuracion y las constantes
	$configFolder =  dirname( __FILE__ ) . "/config/";
	require_once($configFolder . "defines.php");

	
	//para cada uno de los archivos de configuracion
	foreach( glob($configFolder."*.php") as $file){
		require_once $file;
	}


	//para cada uno de los archivos de funciones
	foreach( glob(DIR_FUNC."*.php") as $file){
		//incluimos el archivo
		require_once $file;
	}

	//para cada uno de los archivos de funciones de la nueva estructura
	foreach( glob(dirname(__FILE__)."/../src/lib/*.php") as $file){
		//incluimos el archivo
		require_once $file;
	}


	require_once DIR_CLASS . '/Autoloader.php';
	Autoloader::Register();


	// --- cargar los nuevos componentes
	require_once dirname(__FILE__)."/../src/config.php";