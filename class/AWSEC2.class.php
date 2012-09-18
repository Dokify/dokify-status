<?php

	require_once 'AWSSDKforPHP/sdk.class.php';
	require_once dirname(__FILE__) . '/AWSEC2Instance.class.php';


	class AWSEC2 extends AmazonEC2 {

		protected $aws;
		private static $describe_instances_data = array();

		public function __construct($aws, $credentials){
			$this->aws = $aws;
			return parent::__construct($credentials);
		}

		public function getInstances($AutoScalerName = false){
			if( !self::$describe_instances_data ) self::$describe_instances_data = $this->describe_instances();

			$instances = self::$describe_instances_data->body->reservationSet->item;

			if( $AutoScalerName ){
				$groupInstances = array();
				foreach($instances as $instance){
					$instanceGroup = (string) $instance->instancesSet->item->tagSet->item->value;
					if( $instanceGroup === $AutoScalerName ){
						$groupInstances[] = $instance;
					}
				}

				$instances = $groupInstances;
			}

			foreach($instances as $i => $instance){
				$instances[$i] = new AWSEC2Instance($instance);
			}

			return $instances;
		}

	}