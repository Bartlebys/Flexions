<?php


include FLEXIONS_SOURCE_DIR.'/SharedSwagger.php';

require_once FLEXIONS_ROOT_DIR.'/flexions/representations/flexions/FlexionsRepresentationsIncludes.php';
require_once FLEXIONS_SOURCE_DIR.'helpers/classes/GenerativeHelperForSwift.class.php';
require_once FLEXIONS_MODULES_DIR.'Languages/FlexionsSwiftLang.php';

/* @var $f Flexed */
/* @var $d ProjectRepresentation */

if (isset ( $f )) {
    // We determine the file name.
    $f->fileName = 'Configuration.swift';
    // And its package.
    $f->package = 'iOS/swift/';
}

/* TEMPLATES STARTS HERE -> */?>
<?php echo GenerativeHelperForSwift::defaultHeader($f,$d); ?>

import Foundation

class Configuration {
    static let baseUrl=NSURL(string:"<?php echo($d->baseUrl)?>")
}
<?php /*<- END OF TEMPLATE */?>