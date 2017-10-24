<?php
include  FLEXIONS_MODULES_DIR . '/Bartleby/templates/localVariablesBindings.php';
require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';
require_once FLEXIONS_MODULES_DIR . '/Languages/FlexionsSwiftLang.php';

/* @var $f Flexed */
/* @var $d EntityRepresentation */

/*
 *
 * Value object are simple serializable entities (supporting Json an NSSecureCoding) nothing else
 * So when creating a valueObject there is no stack
 *
 */

if (isset($d)){
    if ($d->isUnManagedModel()){
        include __DIR__ . '/unManagedModel.swift.php';
        return;
    }
    include __DIR__.'/model.swift.php';
}