<?php

	require_once 'AWSSDKforPHP/sdk.class.php';

	class AWSEC2Instance  {

		const STATE_OK = 'running';

		private $id;
		private $state;
		private $zone;

		public function __construct($data){
			$this->id = (string) $data->instancesSet->item->instanceId;
			$this->state = (string) $data->instancesSet->item->instanceState->name;
			$this->zone = (string) $data->instancesSet->item->placement->availabilityZone;
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