<?php

	require_once 'AWSSDKforPHP/sdk.class.php';

	class AWSAutoScaler extends AmazonAS {

		private static $describe_auto_scaling_groups_response = array();
		private static $describe_scaling_activities_response = array();

		public function __construct($aws, $credentials){
			$this->aws = $aws;
			return parent::__construct($credentials);
		}

		public function getInstances(){
			$ec2 = $this->aws->getEC2();
			return $ec2->getInstances($this->getName());
		}

		public function getName(){
			if( !self::$describe_auto_scaling_groups_response ) self::$describe_auto_scaling_groups_response = $this->describe_auto_scaling_groups();

			return (string) self::$describe_auto_scaling_groups_response->body->DescribeAutoScalingGroupsResult->AutoScalingGroups->member->AutoScalingGroupName;
		}

		public function getCapacity(){
			if( !self::$describe_auto_scaling_groups_response ) self::$describe_auto_scaling_groups_response = $this->describe_auto_scaling_groups();

			return (int) self::$describe_auto_scaling_groups_response->body->DescribeAutoScalingGroupsResult->AutoScalingGroups->member->DesiredCapacity;
		}

		public function getDescription(){
			$action = self::getLastActivity();
			return $action->Description;
		}

		public function getLastActivity(){
			if( !self::$describe_scaling_activities_response ) self::$describe_scaling_activities_response = $this->describe_scaling_activities();

			$action = (array) self::$describe_scaling_activities_response->body->DescribeScalingActivitiesResult->Activities->member;
			$action['Details'] = json_decode($action['Details']);
			$action['class'] = ( $action['StatusCode'] == 'Successful' ) ? 'success' : 'highlight';
			return (object) $action;
		}

		public function __call($function, $arguments){
			// vamos a ver sin por ejemplo la funcion getID() tiene una variable correspondiente
			$varname = str_replace("get", "", strtolower($function));
			if( isset($this->$varname) ) return $this->$varname;

			return null;
		}
	}