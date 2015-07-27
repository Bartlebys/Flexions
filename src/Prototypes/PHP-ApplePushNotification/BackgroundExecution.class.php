<?php
/**
 * A simple back ground execution process
 * Usage sample : 
 *				$process = new BackgroundExecution();
 *				$process->runPHP('
 *							$generator=new HashMapGenerator();
 *							$generator->HashMapForRelativePaths('.$relativePaths.');
 *						');
 *						
 *  @author bpds
 */
class BackgroundExecution{

	private $pid;
	
	public  function runPHP($phpString,$outputFile = '/dev/null'){
		return $this->pid = shell_exec( 'php -r \''.$phpString.'\' > ' .$outputFile.' 2>&1 &\; echo $! ' );   
		//$this->pid = shell_exec( 'php -r \''.$phpString.'\' > '.$outputFile.' 2>&amp;1 &amp; echo $!' ); 
	}
	public  function runPHPFile($phpFile,$outputFile = '/dev/null'){
		return $this->pid = shell_exec( 'php -f \''.$phpString.'\' > ' .$outputFile.' 2>&1 &\; echo $! ' );
		//$this->pid = shell_exec( 'php -f \''.$phpFile.'\' > '.$outputFile.' 2>&amp;1 &amp; echo $!' );
	}
	
	public function isRunning() {
		try {
			$result = shell_exec('ps '. $this->pid);
			if(count(preg_split("/\n/", $result)) > 2) {
				return TRUE;
			}
		} catch(Exception $e) {		
		}
		return FALSE;
	}

	public function getPid(){
		return $this->pid;
	}
	
	public  function kill(){
		 shell_exec('ps '. $this->pid);
	}
	
}
