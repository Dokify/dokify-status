<?php

	require_once 'AWSSDKforPHP/sdk.class.php';

	class AWSAutoScaler extends AmazonAS {

		private static $describe_auto_scaling_groups_data = array();

		public function __construct($aws, $credentials){
			$this->aws = $aws;
			return parent::__construct($credentials);
		}

		public function getInstances(){
			$ec2 = $this->aws->getEC2();
			return $ec2->getInstances($this->getName());
		}

		public function getName(){
			if( !$this->describe_auto_scaling_groups_data ) $this->describe_auto_scaling_groups_data = $this->describe_auto_scaling_groups();
			return (string) $this->describe_auto_scaling_groups_data->body->DescribeAutoScalingGroupsResult->AutoScalingGroups->member->AutoScalingGroupName;
		}

		public function getCapacity(){
			if( !$this->responseData ) $$this->describe_auto_scaling_groups_data = $this->describe_auto_scaling_groups();
			return (int) $this->describe_auto_scaling_groups_data->body->DescribeAutoScalingGroupsResult->AutoScalingGroups->member->DesiredCapacity;
		}
	}