<?php

	require_once 'AWSSDKforPHP/sdk.class.php';


	class AWSLoadBalancer extends AmazonELB {

		protected $aws;
		private static $describe_instance_health_response = array();
		const LOAD_BALANCER_NAME = 'vpcdokifyLB';

		public function __construct($aws, $credentials){
			$this->aws = $aws;
			return parent::__construct($credentials);
		}

		public function getInstances($id = false){
			if( !self::$describe_instance_health_response ) self::$describe_instance_health_response = $this->describe_instance_health(self::LOAD_BALANCER_NAME);

			$instances = self::$describe_instance_health_response->body->DescribeInstanceHealthResult->InstanceStates->member;

			if ($id) {
				foreach($instances as $i => $instance){
					if ($id==$instance->InstanceId){
						return $instance;
					}
				}
				return false;
			}
			return $instances;
		}

	}
