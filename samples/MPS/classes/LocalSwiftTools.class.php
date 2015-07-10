<?php


class LocalSwiftTools{
    static function getCurrentClassNameWithPrefix($d,$classesPrefix="") {
        if (! $d)
            return '$d should be set in getCurrentClassFragment( )';
        if (property_exists ( $d, 'name' )) {
            return $classesPrefix.$d->name ;
        } else {
            return 'UNDEFINDED-CLASS-FRAGMENT';
        }
    }
}
?>