<?php

/*
define('APN_PASSPHRASE','<your pass phrase>');
define('APN_STAGE','DEVELOPMENT');
*/


if (!defined('APN_PASSPHRASE')){
    define('APN_PASSPHRASE','NEED-TO-BE-DEFINED');
}
if (!defined('APN_STAGE')){
    define('APN_STAGE','DEVELOPMENT');
}

if(APN_STAGE=='PRODUCTION'){
    define('APN_HOST', 'tls://gateway.push.apple.com');
    define('APN_CERTIFICATE_PATH', dirname ( __FILE__ ) . DIRECTORY_SEPARATOR. 'prodck.pem');
}else{
    define('APN_HOST', 'tls://gateway.sandbox.push.apple.com');
    define('APN_CERTIFICATE_PATH', dirname ( __FILE__ ) . DIRECTORY_SEPARATOR. 'devck.pem');
}
define('APN_PORT',2195);
define('APN_CERTIFICATE_AUTHORITY_PATH',dirname(__FILE__).DIRECTORY_SEPARATOR.'entrust_2048_ca.cer');



class APNPush {


	/**
	 *  A stream context
	 * @var unknown_type
	 */
	private $context;
	private $socket;
	
	public function __construct(){
		$log = "";
		$success = TRUE;
		$passphrase = APN_PASSPHRASE;
		$apnsHost = APN_HOST;
		$apnsPort = APN_PORT;
		$apnsCert = APN_CERTIFICATE_PATH;
		 $certificateAuthority=APN_CERTIFICATE_AUTHORITY_PATH;
		try {
			$this->context = stream_context_create ();
			stream_context_set_option ( $this->context, 'ssl', 'passphrase', $passphrase );
			stream_context_set_option($this->context, 'ssl', 'cafile', $certificateAuthority);
			stream_context_set_option ( $this->context, 'ssl', 'local_cert', $apnsCert );
			$this->socket = stream_socket_client ( $apnsHost . ':' . $apnsPort, $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $this->context );
		} catch ( Exception $e ) {
			
		}
	}
	
	public function __destruct(){
		fclose ( $this->socket );
	}
	
	
	/**
	 * Pushes a payload to a set of device identified by their device token
	 * @param array $payload
	 * @param amultitype:boolean:array string $devicesWithToken
	 * @return multitype:boolean string Ambigous <string, boolean>
	 */
	public function synchronousPushToAPN($payload = NULL,  $devicesWithToken = NULL) {
		if(is_string($devicesWithToken)){
			// We serialize to json to allow Background exec
			$devicesWithToken=json_decode($devicesWithToken);
		}
		$result = array (
				'log' => "",
				'success' => TRUE ,
				'deviceTokens'=>array()
		);
		foreach ( $devicesWithToken as $deviceToken ) {
			$subResult = $this->synchronousPushPayloadToDevice ( $payload, $deviceToken);
			$result ['log'] .= $subResult ['log'];
			$result ['success'] = ($result ['success'] && $subResult ['success']);
			$result['deviceTokens'][]=$subResult['deviceToken'];
		}
		return $result;
	}
	

	/**
	 *  Pushes a payload to a unique device
	 * @param array $payload
	 * @param string $deviceToken
	 * @return multitype:string boolean
	 */
	public function synchronousPushPayloadToDevice($payload = NULL, $deviceToken = NULL) {
		$log = "";
		$success = TRUE;
		try {
			if (! $this->socket ) {
				$log .= ('Failed to connect error = ' . $err . ' ->' . $errstr . PHP_EOL);
			} else {
				$log .= 'Connected to APNS' . PHP_EOL;
				
				// Build the binary notification
				$msg = chr ( 0 ) . pack ( 'n', 32 ) . pack ( 'H*', $deviceToken ) . pack ( 'n', strlen ( $payload ) ) . $payload;
				
				// Send it to the server
				$result = fwrite ( $this->socket, $msg, strlen ( $msg ) );
				if (! $result) {
					$log .= 'Message not delivered' . PHP_EOL;
					$success = FALSE;
				} else {
					$log .= 'Message successfully delivered' . PHP_EOL;
					$success = TRUE;
				}
			}
		} catch ( Exception $e ) {
			$log .= $e->getMessage () . PHP_EOL;
			$success = FALSE;
		}
		return array (
				'log' => $log,
				'success' => $success ,
				'deviceToken' => $deviceToken
		);
	}
	
}