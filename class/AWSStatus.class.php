<?php

	require_once 'AWSSDKforPHP/sdk.class.php';
	require_once dirname(__FILE__) . '/AWSAutoScaler.class.php';
	require_once dirname(__FILE__) . '/AWSEC2.class.php';

	class AWSStatus {
		const KEY_ACCESS = "aws.s3_access";
		const KEY_SECRET = "aws.s3_secret";
		const LOAD_BALANCER_NAME = 'dokifyloadbalancer';
		const DEFAULT_REGION = 'REGION_EU_W1';

		static $AutoScaler;
		static $EC2;
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