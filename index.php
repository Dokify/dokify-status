<?php

	require_once 'config.php';

	$cachePath = dirname(__FILE__) . "/" . AWSStatus::CACHE_FILE;
	$loader = new Twig_Loader_Filesystem(dirname(__FILE__));
	$twig = new Twig_Environment($loader);

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
		//echo('cache');
		//echo($cache['action']);
		$json=json_encode($cache);
		file_put_contents($cachePath, $json);
		$persist="/var/dokify/dev-tools/persist.php";
		if (file_exists($persist)){
			$_SERVER['argv'][1]="aws-status";
			$_SERVER['argv'][2]=$json;
			require $persist;
		}
	} else { 

		$awsStatus = json_decode(file_get_contents($cachePath)); 
		$running = $terminated = $wrong = $inservice = array();
		$load = array();
		$loadAvg = 0;
		foreach ($awsStatus->instances as $instance) {
			switch ($instance->state) {
				case 'running': // --- normal, running machine
					$running[] = $instance;
					if ($instance->balancer->status == "InService") {
						$inservice[] = $instance;
					}
					break;

				case 'terminated': // --- normal, aws autoscaler
					$terminated[] = $instance;
					break;

				default: // --- something is wrong
					$wrong[] = $instance;
					break;
			}
		}

		if (count($inservice)) {
			foreach ($inservice as $machine) {
				$cpu = (float) $machine->cpu;
				$mem = (float) $machine->memory->percentage;
				$load[] =  round(($cpu + $mem) / 2);
				//var_dump($machine->id);
			}
			$loadAvg = round(array_sum($load) / count($inservice));
		}

		$vars['inservice'] = $inservice;
		$vars['instances'] = $awsStatus->instances;
		$vars['loadAvg'] = $loadAvg;
		$vars['running'] = $running;
		$vars['terminated'] = $terminated;
		$vars['wrong'] = $wrong;

		$vars['problems'] = count($wrong) || !count($inservice);

		$vars['action'] = array(
			'text' => $awsStatus->action->Description,
			'time' => strtotime($awsStatus->action->StartTime)
		);
	}


	echo $twig->render('status.html', $vars);
	
