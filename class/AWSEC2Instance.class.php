<?php

	require_once 'AWSSDKforPHP/sdk.class.php';

	class AWSEC2Instance  {

		const STATE_OK = 'running';
		const STATE_UNKNOWN = 'unknown';

		private $aws;
		private $id;
		private $state;
		private $zone;
		private $balancerStatus=null;

		public function __construct($aws, $data){
			$this->aws = $aws;
			$this->id = (string) $data->instancesSet->item->instanceId;
			$this->state = (string) $data->instancesSet->item->instanceState->name;
			$this->zone = (string) $data->instancesSet->item->placement->availabilityZone;
		}

		public function getBalancerStatus(){
			$instanceHealth = $this->aws->getLoadBalancer()->getInstances($this->id);
			if ($instanceHealth){
				return $instanceHealth->State;
			}
			return self::STATE_UNKNOWN;
		}

		public function isOk(){
			return (bool) $this->getState() === self::STATE_OK;
		}

		public function __call($function, $arguments){
			// vamos a ver sin por ejemplo la funcion getID() tiene una variable correspondiente
			$varname = str_replace("get", "", strtolower($function));
			if( isset($this->$varname) ) return $this->$varname;

			return null;
		}

	}