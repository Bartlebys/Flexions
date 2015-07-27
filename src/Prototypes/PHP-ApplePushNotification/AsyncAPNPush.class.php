<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'BackgroundExecution.class.php';
require_once dirname ( __FILE__ ) . DIRECTORY_SEPARATOR . 'APNPush.class.php';

class AsyncAPNPush {
	/**
	 * 
	 * @var BackgroundExecution
	 */
	public  $process;

	/**
	 *  Asynchronous  version of the method that Pushes a payload to a set of device identified by their device token
	 * @param unknown_type $payload
	 * @param array $devicesWithToken
	 */
	public function asynchronousPushToAPN($payload = NULL, array $devicesWithToken = NULL) {
		$this->process = new BackgroundExecution();
		$classPath=dirname ( __FILE__ ) . DIRECTORY_SEPARATOR .'APNPush.class.php';
		$logPath= '/home/colunchers/logs/push_log';
		$jsonDevices=json_encode($devicesWithToken);
		return $this->process->runPHP('
							require_once "'.$classPath.'";
							$apnPush=new APNPush();
                                                        $apnPush->synchronousPushToAPN("'.addcslashes($payload, '\"').'",\''. $jsonDevices.'\');'
				, $logPath);
	}
	
	

	/**
	 *  Asynchronous  version of the method that Pushes a payload to a set of device identified by their device token
	 * @param unknown_type $payload
	 * @param array $devicesWithToken
	 */
	public function asynchronousPushPayloadToDevice($payload = NULL, $deviceToken = NULL) {
		$this->process = new BackgroundExecution();

		$classPath=dirname ( __FILE__ ) . DIRECTORY_SEPARATOR .'APNPush.class.php';
		$logPath= '/home/colunchers/logs/push_log';
       return $this->process->runPHP('
                                                        require_once "'.$classPath.'";
                                                        $apnPush=new APNPush();
                                                        $apnPush->synchronousPushPayloadToDevice("'.addcslashes($payload, '"\'').'","'.$deviceToken.'");
                                                ', $logPath);
	}
	
}
