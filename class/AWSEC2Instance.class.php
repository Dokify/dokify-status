<?php

	require_once 'AWSSDKforPHP/sdk.class.php';

	class AWSEC2Instance  {

		const STATE_OK = 'running';
		const STATE_UNKNOWN = 'state_unknown';
		const MEM_UNKNOWN = 'mem_unknown';
		const PROC_UNKNOWN = 'proc_unknown';
		const RUTA_FICHERO = 'http://status.dokify.net/statistics_json.log';

		private $aws;
		private $id;
		private $state;
		private $zone;
		private $datosFichero=null;

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

		private function leerFichero($ruta){
			$datos = file_get_contents($ruta);
			return $datos;
		}

		public function getMemoria(){
			if ( $this->datosFichero == null ){
				$this->datosFichero = json_decode($this->leerFichero(self::RUTA_FICHERO));		
			}
			foreach($this->datosFichero as $instance){
					$instanceId = $instance->nombre;
					if( $instanceId === $this->id ){
						return $instance->memoria;
					}
				}
			return self::MEM_UNKNOWN;
		}

		public function getProcesos(){
			if ( $this->datosFichero == null ){

				$this->datosFichero = json_decode($this->leerFichero(self::RUTA_FICHERO));		
			}
			foreach($this->datosFichero as $instance){
					$instanceId = $instance->nombre;
					if( $instanceId === $this->id ){
						return $instance->conexiones;
					}
				}
			return self::PROC_UNKNOWN;
		}

		public function getCpu(){
			if ( $this->datosFichero == null ){

				$this->datosFichero = json_decode($this->leerFichero(self::RUTA_FICHERO));		
			}
			foreach($this->datosFichero as $instance){
					$instanceId = $instance->nombre;
					if( $instanceId === $this->id ){
						return $instance->cpu;
					}
				}
			return self::PROC_UNKNOWN;
		}

		public function getProgress(){
			$por = self::getPorcentaje();
 			$progress = 100-$por;
 			return $progress;
		}

		public function getPorcentaje(){
			$mem = self::getMemoria();
			$por = round(((100*$mem)/1700));
			return $por;
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