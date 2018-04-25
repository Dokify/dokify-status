<?php

	require_once 'AWSSDKforPHP/sdk.class.php';
	require_once dirname(__FILE__) . '/AWSAutoScaler.class.php';
	require_once dirname(__FILE__) . '/AWSLoadBalancer.class.php';
	require_once dirname(__FILE__) . '/AWSEC2.class.php';

	class AWSStatus {
		const KEY_ACCESS = "aws.s3_access";
		const KEY_SECRET = "aws.s3_secret";
		const DEFAULT_REGION = 'REGION_EU_W1';
		const METRICS_FILE = 'http://status.dokify.net/statistics.json';
		const CACHE_FILE = 'cache.json';

		static $AutoScaler;
		static $EC2;
		static $LoadBalancer;
		static $MetricsData;
		static $data = array();

		public function getAutoScaler(){
			if( !self::$AutoScaler ){
				self::$AutoScaler = new AWSAutoScaler($this, array("key" => $this->getKey(), "secret" => $this->getSecret()));
				self::$AutoScaler->set_region(constant('AmazonAS::'.self::DEFAULT_REGION));
			}

			return self::$AutoScaler;
		}

		public function getEC2(){
			if( !self::$EC2 ){
				self::$EC2 = new AWSEC2($this, array("key" => $this->getKey(), "secret" => $this->getSecret()));
				self::$EC2->set_region(constant('AmazonEC2::'.self::DEFAULT_REGION));
			}

			return self::$EC2;
		}

		public function getLoadBalancer(){
			if( !self::$LoadBalancer){
				self::$LoadBalancer = new AWSLoadBalancer($this, array("key" => $this->getKey(), "secret" => $this->getSecret()));
				self::$LoadBalancer->set_region(constant('AmazonELB::'.self::DEFAULT_REGION));
			}

			return self::$LoadBalancer;
		}

		public function getMetricData($id = NULL){
			if( !self::$MetricsData ){
				$filedata = file_get_contents(AWSStatus::METRICS_FILE);
				self::$MetricsData = json_decode($filedata);

				if( $code = json_last_error() ){
					throw new Exception("error parsing json [$code]");
				}
			}

			$instances = self::$MetricsData;

			if( !$instances || !count($instances) ) return false;

			if( $id ){
				foreach($instances as $instance){
					if($instance->nombre === $id){
						return $instance;
					}
				}

				return false;
			}

			return $instances;
		}


		/**************** PRIVATE METHODS *******************/

		private function getKey(){
			if( $awsAccesKey  = @trim(get_cfg_var(self::KEY_ACCESS)) ){
				return $awsAccesKey;
			}

			throw new Exception('Please, set aws access key!');
		}

		private function getSecret(){
			if( $awsSecretKey  = @trim(get_cfg_var(self::KEY_SECRET)) ){
				return $awsSecretKey;
			}

			throw new Exception('Please, set aws secret key!');
		}

	}
