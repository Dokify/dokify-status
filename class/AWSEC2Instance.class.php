<?php

	require_once 'AWSSDKforPHP/sdk.class.php';

	class AWSEC2Instance  {

		private static $data;

		public function __construct($data){
			$this->data = $data;
		}

		public function getId(){
			return (string) $this->data->instancesSet->item->instanceId;
		}

		public function getStatus(){
			return (string) $this->data->instancesSet->item->instanceState->name;
		}

	}