<?php
		const AWS_KEY_ACCESS = "aws.s3_access";
		const AWS_KEY_SECRET = "aws.s3_secret";
		require_once ('AWSSDKforPHP/sdk.class.php');
		//require_once ('../config.php');
		$awsAccesKey = @trim(get_cfg_var(AWS_KEY_ACCESS));
		$awsSecretKey = @trim(get_cfg_var(AWS_KEY_SECRET));

		$ec2 = new AmazonEC2(array("key" => $awsAccesKey, "secret"=>$awsSecretKey)); 
		$s3 = new AmazonS3(array("key" => $awsAccesKey, "secret"=>$awsSecretKey));
		$as = new AmazonAS(array("key" => $awsAccesKey, "secret"=>$awsSecretKey));
		$elb = new AmazonELB(array("key" => $awsAccesKey, "secret"=>$awsSecretKey));
		$cw = new AmazonCloudWatch(array("key" => $awsAccesKey, "secret"=>$awsSecretKey));
		$nombre_fichero = "statistics.log";

		//Abrir fichero log
		$gestor = fopen($nombre_fichero, "r");
		$contenido = fread($gestor, filesize($nombre_fichero));
		$arrayContenido = explode('/',$contenido);
		foreach($arrayContenido as $clave=>$valor){
			if($valor == end($arrayContenido)) unset($arrayContenido[$clave]);
		}
		$arrayContenido = array_merge($arrayContenido);
		fclose($gestor);

		$json = array();

		$balanceador = 'dokifyloadbalancer';
		$datosBalanceador = $elb->describe_instance_health($balanceador);
		$response = $as->describe_auto_scaling_groups();
		$json['grupo'] = array();
		$json['grupo']['nombre'] = (string)$response->body->DescribeAutoScalingGroupsResult->AutoScalingGroups->member->AutoScalingGroupName;
		$AutoScaler = $as->describe_auto_scaling_groups();
		$json['grupo']['capacidad'] = (string)$AutoScaler->body->DescribeAutoScalingGroupsResult->AutoScalingGroups->member->DesiredCapacity;

		$response = $ec2->describe_instances();
		$arrayInstances = $response->body->reservationSet->item;
		$json['grupo']['instances'] = array();
		foreach ($arrayInstances as $key => $value) {
			$grupo = $value->instancesSet->item->tagSet->item->value;
			if ($grupo == $json['grupo']['nombre']) {
				$nombre = (string)$value->instancesSet->item->instanceId;
				$estado = (string)$value->instancesSet->item->instanceState->name;
				$zona = (string)$value->instancesSet->item->placement->availabilityZone;
				$procesos = procesos($nombre, $arrayContenido);
				$memoria = memoria($nombre, $arrayContenido); 
				$estadoBalanceador = balanceador($nombre, $datosBalanceador);
				$instance = array('nombre' => $nombre,'estado'  => $estado, 'zona' => $zona, 'procesos' => $procesos, 'memoria' => $memoria, 'estadoBalanceador' => $estadoBalanceador);
				$json['grupo']['instances'][]=$instance;
			}
		}

		function procesos($instancia, $arrayContenido) 
   		{ 
   			foreach ($arrayContenido as $key => $value) {
				$arrayInstCont = explode(';',$value);
				if($arrayInstCont[0] == $instancia){
					return $arrayInstCont[1];
				}
			}
 		} 

   		function memoria($instancia, $array) 
   		{ 
   			foreach ($array as $key => $value) {
				$arrayInstCont = explode(';',$value);
				if($arrayInstCont[0] == $instancia){
					return $arrayInstCont[2] % 1024;
				}
			}
   		}

   		function balanceador($instancia, $datos){ 		
			$healthy = $datos->body->DescribeInstanceHealthResult->InstanceStates->member;
			foreach ($healthy as $key => $value) {		
				if($value->InstanceId == $instancia){
					return $value->State;
				}
			}
   		}

   		function httpaccepts(){
   			return explode ("," , $_SERVER["HTTP_ACCEPT"]);
   		}

		$response = $as->describe_scaling_activities();
		$ultimasAcciones = $response->body->DescribeScalingActivitiesResult->Activities->member;
		$value = $ultimasAcciones[0];
		$descripcion = (string)$value->Description;	
		$hora = (string)$value->StartTime;
		$estado = (string)$value->StatusCode;
		$json['grupo']['ultimaAccion'] = array('descripcion' => $descripcion, 'hora' => $hora, 'estado' => $estado);

		if (in_array("text/html", httpaccepts())) {
			require '/var/www/dokify-status/vendor/mustache/mustache/src/Mustache/Autoloader.php';
			Mustache_Autoloader::register();

			$m = new Mustache_Engine;
			echo $m->render(file_get_contents("plantilla.html"), $json['grupo']); 
			exit();	
		}
		

		header("Content-Type: application/json");
		print json_encode($json['grupo']);
?> 
