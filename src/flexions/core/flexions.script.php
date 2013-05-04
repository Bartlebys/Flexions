<?php
/*

Created by Benoit Pereira da Silva on 20/04/2013.
Copyright (c) 2013  http://www.pereira-da-silva.com

This file is part of Flexions

Flexions is free software: you can redistribute it and/or modify
it under the terms of the GNU LESSER GENERAL PUBLIC LICENSE as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Flexions is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU LESSER GENERAL PUBLIC LICENSE for more details.

You should have received a copy of the GNU LESSER GENERAL PUBLIC LICENSE
along with Flexions  If not, see <http://www.gnu.org/licenses/>

## EXPLANATIONS ##

This script Interprets and validates the call and then runs through 3 phases : pre-processing / processing / post-processing

#1- pre-processing : ( you can have multiple pre-processors )

	1.1 can proceed to preparation (pre-generation, data set building per introspection, etc....)
 	1.2 loads the "descriptor" and transform them to an Hypotypose instance
  
   	We create aliases to be used in the templates : 
   	
	$h refers to the singleton Hypotypose::instance()  
	$d refers whithin the loop to the current focus for example : $d=$actions[19] if the loop runs on the 19th actions.
	$f  fefers to the current Flexed instance
 	
 
#2- processing (usually a triple loop)  :

	2.1 Per entity loop (entity )												enumerates { $entities }
	2.2 Action loop (operations, command)							enumerates { $actions }
 	2.3 One stop loop (api , http client , shared headers)     runs once  per $project
 	
   IMPORTANT if the package contains a loops.php file the standard flexion loop is not used.

#3- post-processing : (you can have multiple post-processors)
	
	Each loop store a collection of Flexed instances .
     
 	3.1 Can use the "Flexed" instances to generate sub-Flexed (for example an header file for all the generated files)
 	3.1 Serializes the "Flexed" to sources files to the destination.
	3.2 Perform any other post processing action (notification, push, ....)

*/

require_once FLEXIONS_ROOT_DIR.'flexions/core/Flog.class.php';
require_once FLEXIONS_ROOT_DIR.'flexions/core/Hypotypose.class.php';
require_once FLEXIONS_ROOT_DIR.'flexions/core/Flexed.class.php';
require_once FLEXIONS_ROOT_DIR.'flexions/core/functions.script.php';

error_reporting(E_ALL | E_STRICT);
date_default_timezone_set ( 'UTC' );

// Server & commandline versatile support 

if ($_SERVER ['argc'] == 0 || ! defined ( 'STDIN' )) {
	// Server mode
	$arguments = $_GET;
	define ( "COMMANDLINE_MODE", false );
} else {
	// Command line mode
	$rawArgs = $_SERVER ['argv'];
	array_shift ( $rawArgs ); // shifts the commandline script file flexions.php
	$arguments = array ();
	parse_str ( implode ( '&', $rawArgs ), $arguments );
	define ( "COMMANDLINE_MODE", true );
}


// We instanciate the Flog singleton
// and store a time stamp as  first log.
Flog::Instance ()->addMessage ( '##' . fDate () . '##' . cr() );
                                        
$preProcessors = '';
$postProcessors = '';
$source = '';
$descriptorFilePath='';
$destination='';
if (isset ( $arguments ["source"] )) {
	$source = $arguments ["source"];
} else {
	 throw new Exception ( 'Required parameter "source"' . cr() );
}

define ( "FLEXIONS_SOURCE_DIR", $source );

if (isset ( $arguments ["descriptor"] )) {
	if(file_exists($arguments["descriptor"])){
		// We use an absolute path
		$descriptorFilePath=$arguments["descriptor"];
	}else{
		// we use a relative path
		$descriptorFilePath = FLEXIONS_SOURCE_DIR  . $arguments ["descriptor"];
	}
}


if (isset ( $arguments ["templates"] ) && strlen ( $arguments ["templates"] ) >= 1) {
	$templates = $arguments ["templates"];
} else {
	$templates = '*';
}

if (isset ( $arguments ["destination"] ) && strlen ( $arguments ["destination"] ) >= 1) {
	$destination = $arguments ["destination"];
} else {
	$destination = FLEXIONS_ROOT_DIR . '/out/standard/';
	if(file_exists($destination)==false)
		mkdir ( $destination, 0777, true );
}

if (isset ( $arguments ["preProcessors"] ))
	$preProcessors = $arguments ["preProcessors"];

if (isset ( $arguments ["postProcessors"] ))
	$postProcessors = $arguments ["postProcessors"];
	
	// Check if mandatory $arguments are set
	// (Preprocessors and PostProcessors are optionnal)

if (! isset ( $descriptorFilePath )) {
	 throw new Exception( 'Required parameter "descriptor"' . cr() );
}


$baseTemplatePath = FLEXIONS_SOURCE_DIR . 'templates';
// Templates joker.
if ($templates == "*") {
	// We populate the templates with the relative path
	$templatesArray = directoryToArray ( $baseTemplatePath );
	$templates = implode ( ',', $templatesArray );
}else{
	$templatesTempArray= explode(',', $templates);
	$templatesArray=array();
	foreach ( $templatesTempArray as $templatePath ) {
		// Compute the absolute path
		$templatesArray[]=$baseTemplatePath."/".$templatePath;;
	}
}


$specificLoops = FLEXIONS_SOURCE_DIR . 'loops.php';

$m = cr();
$m.=  '# phpversion : '.phpversion().cr();
$m .= '# Invoking flexions  for ' .simplifyPath($descriptorFilePath) . cr();
$m .= '# On template(s): ' . str_replace ( FLEXIONS_SOURCE_DIR . 'templates/', '', $templates ) . cr();
$m .= '# With destination: ' . simplifyPath($destination) . cr();
$m .= '# Using pre-processor: ' . $preProcessors . cr();
$m .= '# And post-processor: ' . $postProcessors . cr();
if (file_exists ( $specificLoops )) {
	$m .= '# Using the loops : ' . $source . '/loops.php' . cr() . cr();
}else{
	$m .=  '# Using the standard loops' . cr() . cr() ;
}
$m .='FLEXIONS_SOURCE_DIR='. FLEXIONS_SOURCE_DIR.cr();
$m .='FLEXIONS_ROOT_DIR='. FLEXIONS_ROOT_DIR.cr(). cr();
fLog ( $m, true );

// /////////////////////////////////
// PHASE #1
// PREPROCESSING
// /////////////////////////////////


$arrayOfPreProcessors = explode ( ",", $preProcessors );
foreach ( $arrayOfPreProcessors as $preProcessor ) {
	// Invokes the pre-processor
	$preProcessorPath = FLEXIONS_SOURCE_DIR . $preProcessor;
	include $preProcessorPath;
}
	
// //////////////////////////////////
// PHASE #2
// PROCESSING
// /////////////////////////////////

/**
 *
 * @var $h Hypotypose
 *     
 */

if (file_exists ( $specificLoops )) {
	// We use the specific loops
	include $specificLoops;
} else {
	
	while ( $h->nextLoop () == true ) {
		$list = $h->getContentForCurrentLoop (); // Returns the current loop items
		foreach ( $list as $descriptions ) {
				// It is a description object 
				iterateOnTemplates ( $templatesArray, $h, $descriptions, $destination );
		}
	}
}

/**
 * 
 * @param array $templatePath
 * @param Hypotypose $h
 * @param mixed $d
 * @throws Exception
 */
 function iterateOnTemplates(array $templatesArray, Hypotypose $h,  $d,$destination){
 	
	foreach ( $templatesArray as $templatePath ) {
			
		// We need to determine if the template should be used in
		// this loop.
		$shouldBeUsedInThisLoop = (strpos ( $templatePath, $h->getLoopName () ) !== false);
			
		if ($shouldBeUsedInThisLoop) {
	
			$result = '';
			if (! isset ( $d )) {
				throw new Exception( 'Descriptor variable $d must be set for the templates' );
			}
			
			// We instanciate the current Flexed
			// will be used by the templates to define $f->fileName, $f->package
			$f = new Flexed ();
			
			// ( ! ) Template execution
			ob_start ();include $templatePath;$result = ob_get_clean ();
			// (!) End of template execution
	
			if ($f->fileName != null ) {
					
				$f->source = $result; // We store the generation result
				//and the package path
				$f->packagePath = $destination . $f->package;
				 
				// We add the flexed the Hypotypose for the post processors
				$h-> addFlexed($f);
			} else {
				fWarning ( 'fileName or package is not defined in ' . $templatePath );
			}
		}
	}
}


// //////////////////////////////////
// PHASE #3
// POST PROCESSING
// ///////////////////////////////////

$arrayOfPostProcessors = explode ( ",", $postProcessors );
foreach ( $arrayOfPostProcessors as $postProcessor ) {
	// Invokes the post-processor
	$postProcessorPath = FLEXIONS_SOURCE_DIR  . $postProcessor;
	include $postProcessorPath;
}

// Dump Flog

$logFolderPath = FLEXIONS_ROOT_DIR . '../out/logs/';
if(! file_exists($logFolderPath)){
	mkdir ( $logFolderPath, 0777, true );
}
$logsFilePath = $logFolderPath . fDate () . '-logs.txt';
file_put_contents ( $logsFilePath, Flog::Instance ()->getLogs () );