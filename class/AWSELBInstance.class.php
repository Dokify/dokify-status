<?php

	require_once 'AWSSDKforPHP/sdk.class.php';

	class AWSELBInstance  {

		const STATE_OK = 'InService';

		private $id;
		private $state;

		public function __construct($data){
			$this->id = (string) $data->InstanceId;
			$this->state = (string) $data->State;
		}

		public function isOk(){
			return (bool) $this->State === self::STATE_OK;
		}

		public function __call($function, $arguments){
			// vamos a ver sin por ejemplo la funcion getID() tiene una variable correspondiente
			$varname = str_replace("get", "", strtolower($function));
			if( isset($this->$varname) ) return $this->$varname;

			return null;
		}

	}