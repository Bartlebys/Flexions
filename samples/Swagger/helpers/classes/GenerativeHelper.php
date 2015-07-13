<?php

require_once FLEXIONS_ROOT_DIR.'flexions/core/Hypotypose.class.php';

class GenerativeHelper{

    static function flexedWillBePreserved(Flexed $flexed) {
        $h = Hypotypose::instance();
        $shouldBePreserved=false;
        $path=Hypotypose::Instance()->exportFolderPath.$flexed->package . $flexed->fileName;
        foreach ($h->preservePath as $pathToPreserve ) {
            if(strpos($path,$pathToPreserve)!==false){
                $shouldBePreserved=true;
            }
        }
        return $shouldBePreserved;
    }

    static function defaultHeader(Flexed $f, $d){
        return '// DEFAULT HEADER';
    }
}
