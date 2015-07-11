<?php 

// /////////////////////////////////////////
// #1 Save the hypotypose to files
// /////////////////////////////////////////


hypotyposeToFiles();


// /////////////////////////////////////////
// #2 generate some post generation files
// /////////////////////////////////////////


if(file_exists(realpath($destination))==false){
	throw new Exception("Unexisting destination ".realpath($destination));
}

$generated='';
$h=Hypotypose::Instance();

// Let's write the list of the files we have created
// We could iterate of each loop ( $h->flexedList)
$list = $h->getFlatFlexedList();
$counter = 0;

foreach ( $list as $flexed ) {
    /* @var $flexed Flexed */
	if ($flexed->exclude === false) {
		// Let's add a human readable log.
		$counter ++;
        $line='';
		if (VERBOSE_FLEXIONS)
			fLog ( $counter . " " . $flexed->fileName. cr() , false );
		// Let's list the file name
		if( $flexed->wasPreserved==true){
			$line .= $counter.'<- * We have preserved "'.$flexed->package.''.$flexed->fileName . '"' . "*".cr();
		}else{
			$line .= $counter.'-> We have created "'.$flexed->package.''.$flexed->fileName . '"' . "".cr();
		}
		$generated .= $line;
	}
}


// We save the file
$filePath= $destination .'ReadMe.txt';
$c='Those files that are recreated by flexions should not be modified directly. Preserved path can be modified, and recreated by deleting them'.cr().cr();
$c.=$generated;
file_put_contents ( $filePath, $c );