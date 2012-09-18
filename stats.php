<?php

	require 'config.php';

	$aws = new AWSStatus();
	$autoscaler = $aws->getAutoScaler();

	$action = $autoscaler->getLastActivity();
	echo "Last autoscaler action: ". $action->Description ." at ". $action->StartTime ." <br /><hr />";

	$instances = $autoscaler->getInstances();
	foreach($instances as $instance){
		print $instance->getID() . "<br />";
		print $instance->getZone() . "<br />";
		print $instance->getState() . "<br />";


		echo "<hr />";
	}
