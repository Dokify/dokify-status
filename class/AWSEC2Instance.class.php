<?php

	require_once 'AWSSDKforPHP/sdk.class.php';

	class AWSEC2Instance  {

		const STATE_OK = 'running';
		const STATE_UNKNOWN = 'state_unknown';
		const MEM_UNKNOWN = 'mem_unknown';
		const PROC_UNKNOWN = 'proc_unknown';

		private $aws;
		private $id;
		private $state;
		private $zone;
		private $datosFichero=null;

		public function __construct($aws, $data){
			$this->aws = $aws;
			$this->id = (string) $data->instancesSet->item->instanceId;
			$this->state = (string) $data->instancesSet->item->instanceState->name;
			$this->zone = (string) $data->instancesSet->item->placement->availabilityZone;
		}

		public function toArray(){
			$data = array();
			$data['id'] = $this->id;
			$data['state'] = $this->state;
			$data['class'] = $this->getStatusClass();
			$data['zone'] = $this->state;
			$data['balancer'] = array(
				"class" => $this->getBalancerClass(),
				"status" => $this->getBalancerStatus()
			);

			$data['connections'] = $this->getProcesos();
			$data['cpu'] = $this->getCpu();
			$data['memory'] = array(
				"used" => $this->getMemoria(),
				"average" => $this->getPorcentaje()
			);

			$data['load'] = $this->getLoadAverage();

			return $data;
		}

		public function getBalancerStatus(){
			$instanceHealth = $this->aws->getLoadBalancer()->getInstances($this->id);
			if ($instanceHealth){
				return (string) $instanceHealth->State;
			}
			return self::STATE_UNKNOWN;
		}

		public function getMetricData(){
			return $this->aws->getMetricData($this->id);
		}


		public function getLoadAverage(){
			if( $data = $this->getMetricData($this->id) ){
				return $data->average->load;
			}
			
			return 0;
		}

		public function getMemoria(){
			if( $data = $this->getMetricData($this->id) ){
				return $data->memoria;
			}
			
			return 0;
		}

		public function getProcesos(){
			if( $data = $this->getMetricData($this->id) ){
				return $data->conexiones;
			}

			return self::PROC_UNKNOWN;
		}

		public function getCpu(){
			if( $data = $this->getMetricData($this->id) ){
				return $data->cpu;
			}

			return self::PROC_UNKNOWN;
		}

		public function getProgress(){
			$por = self::getPorcentaje();
 			$progress = 100-$por;
 			return $progress;
		}

		public function getPorcentaje(){
			$mem = self::getMemoria();
			$por = round(((100*$mem)/1700));
			return $por;
		}

		public function getStatusClass(){
			$class = array();
			$class[] = $this->getState()=="running"?'correcto':'incorrecto';
			return implode(" ", $class); 
		}

		public function getBalancerClass(){
			$class = array();
			$class[] = $this->getBalancerStatus()=="InService"?'correcto':'incorrecto';
			return implode(" ", $class); 
		}


		public function isOk(){
			return (bool) $this->getState() === self::STATE_OK;
		}


		public function __get($name){
			return $this->$name=$this->$name();
		}

		public function __call($function, $arguments){
			// vamos a ver sin por ejemplo la funcion getID() tiene una variable correspondiente
			$varname = str_replace("get", "", strtolower($function));
			if( isset($this->$varname) ) return $this->$varname;

			return null;
		}

	}