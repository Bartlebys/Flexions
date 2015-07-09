<?php


// To use Geshi untar the files in dependencies/geshi/

/**
 *  A postprocessor that uses geshi
 *  @var $language string
 * @var $h Hypotypose
 */

if (isset ( $language )) {
	include_once FLEXIONS_ROOT_DIR . 'modules/GeSHI/dependencies/geshi/geshi.php';
	foreach ( $h->flexedList as $loopname => $list ) {
		foreach ( ( array ) $list as $f ) {
			/* @var $f Flexed */
			$geshi = new GeSHi ( $f->source, $language );
			$f2 = clone $f;
			$f2->source = $geshi->parse_code ();
			$f2->fileName = $f->fileName . '.html';
			fLog ( "Invoking GeSHi on " . $f2->fileName . cr (), true );
			file_put_Flexed ( $f2 );
		}
	}
}