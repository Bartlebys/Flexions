<?php

require_once FLEXIONS_ROOT_DIR.'flexions/core/Flog.class.php';
require_once FLEXIONS_ROOT_DIR.'flexions/core/Hypotypose.class.php';
require_once FLEXIONS_ROOT_DIR.'flexions/core/Flexed.class.php';


////////////////////////////////////
// FUNCTIONS
/////////////////////////////////////


/**
 * @param string  $dir
 * @param array $result
 * @return array  of path
 */
function directoryToArray($dir, &$result = array()) {
	$dirList = scandir ( $dir );
	foreach ( $dirList as $key => $value ) {
		$dotPos = strpos ( $value, '.' );
		if (($dotPos === false) or ($dotPos != 0)) {
			if (is_dir ( $dir . DIRECTORY_SEPARATOR . $value )) {
				$sub = directoryToArray ( $dir . DIRECTORY_SEPARATOR . $value );
				$result = array_merge ( $result, $sub );
			} else {
				$result [] = $dir . DIRECTORY_SEPARATOR . $value;
			}
		}
	}
	return $result;
}

function fLog($message, $show=false){
	Flog::Instance()->addMessage($message);
	if($show || ECHO_LOGS===true){
		echo $message;
	}
}

/**
 *  Contextual Carriage return
 * @return string
 */
function cr(){
	return COMMANDLINE_MODE?"\n":"</br>";
}

/**
 * 
 * @param int $n
 * @return string
 */
function tabs($n=1){
	$s="";
	for ($i = 0; $i < $n; $i++) {
		$s.=chr(9);
	}
	return $s;
}


/**
 * 
 * @param string $string
 * @param int $n
 */
function echoIndent($string,$n=1){
	 echo tabs($n).$string;
}


/**
 * Logs a warning
 * @param string $warning
 */
function fWarning( $warning){
	fLog('WARNING : '.$warning,false);
}

function fDate(){
	$dt = new DateTime();
	return $dt->format('Y-m-d-').microtime(true);
}


function hypotyposeToFiles() {
	$h=Hypotypose::Instance();
	$history=array();
	foreach ( $h->flexedList as $loopname=>$list ) {
		/* @var $list array */
		foreach ( $list as  $f ) {
			$path=$f->packagePath . $f->fileName;
			// We put to file once only per destination
			if(in_array($path, $history)==false){
				$shouldBePreserved=false;
				foreach ($h->preservePath as $pathToPreserve ) {
					if(strpos($path,$pathToPreserve)!==false){
						$shouldBePreserved=true;
					}
				}
				/* @var $f Flexed */
				if( !file_exists($path) || (file_exists($path) && $shouldBePreserved==false)){
					// Any path marked as preserved in preprocessor is not ovveriden
					file_put_Flexed ( $f );
					$history[]=$path;
				}else{
                    $f->wasPreserved=true;
                }
			}
		}
	}
	if (VERBOSE_FLEXIONS)
		fLog("\nSerializing hypotypose to ".count($history)." file(s)"." with loop name == ".$loopname.cr(),true);
}

function file_put_Flexed(Flexed $f){
	// Create the package folder if necessary
	if (! file_exists ( $f->packagePath )) {
		if (VERBOSE_FLEXIONS)
			fLog("-> creating package " .  $f->package . cr(),true);
		mkdir ( $f->packagePath, 0777,true );
	}
	// Save the generated file
	file_put_contents ( $f->packagePath . $f->fileName, $f->source );
	if (VERBOSE_FLEXIONS)
		fLog("Writing : ".$f->packagePath . $f->fileName.cr(),true);
}

/**
 * 
 * @param string $path
 * @return string
 */
function simplifyPath($path){
	$path=realpath($path);
	$path=str_replace(FLEXIONS_ROOT_DIR,"", $path);
	return $path;
}


/**
 *  Converts an int to a compact notation according to a key
 * @param unknown_type $n
 * @param unknown_type $withKey
 * @return string
 */
function intToCompactMode($n,$withKey){
	$alpha=str_split($withKey);
	return intToAlphaBaseN($n, $alpha);
}


/**
 *  Convert an int to any base 
 * @param int $n
 * @param array $baseArray
 * @return string
 */
function intToAlphaBaseN($n,$baseArray) {
	$l=count($baseArray);
	$s = '';
	for ($i = 1; $n >= 0 && $i < 10; $i++) {
		$s =  $baseArray[($n % pow($l, $i) / pow($l, $i - 1))].$s;
		$n -= pow($l, $i);
	}
	return $s;
}






