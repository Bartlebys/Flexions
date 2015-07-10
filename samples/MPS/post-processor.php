<?php 

// /////////////////////////////////////////
// #1 Save the hypotypose to files
// /////////////////////////////////////////


hypotyposeToFiles();


// /////////////////////////////////////////
// #2 generate a consolidated header file 
// /////////////////////////////////////////

/* @var $outPutFolderRelativePath string */

if(file_exists(realpath($destination))==false){
	throw new Exception("Unexisting destination ".realpath($destination));
}

$generated='';

$h=Hypotypose::Instance();


// Let's write the list of the files we have created in the loop "Project"
$list = $h->flexedList [DefaultLoops::PROJECT];
$counter = 0;

foreach ( $list as $flexed ) {
	if ($flexed->exclude === false) {
		// Let's add a human readable log.
		$counter ++;
        $line='';
		if (VERBOSE_FLEXIONS)
			fLog ( $counter . " " . $flexed->fileName. cr() , false );
		// Let's list the file names
		$line .= 'We have created "'.$flexed->fileName . '"' . "\n";
		$generated .= $line;
	}
}

// We use the models variables
$f=new Flexed();
// This include sets $f properties
include FLEXIONS_SOURCE_DIR . "variables-for-MPS.php";
$f->package="";
$f->fileName="PostProcessed.txt";

// We save the generated headers.
$filePath= $destination .$f->package. $f->fileName;
$generated=getCommentHeader ( $f )."\n\n" .$generated;
file_put_contents ( $filePath, $generated );