<?php

	require 'config.php';

	$aws = new AWSStatus();
	$autoscaler = $aws->getAutoScaler();

	$instances = $autoscaler->getInstances();
	foreach($instances as $instance){
		dump($instance);
	}
