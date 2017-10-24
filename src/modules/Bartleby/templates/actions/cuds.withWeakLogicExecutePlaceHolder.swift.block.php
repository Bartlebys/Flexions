<?php

// This block is included in cuds.withWeakLogic.swift.template.php

///////////////////////
// LOCAL REQUIREMENTS
///////////////////////

////////////////////////
// VARIABLES DEFINITION
////////////////////////

//////////////////////////
// BLOCK
//////////////////////////


?>



    /// Should be implemented in a children.
    /// Don't forget to override typeName()
    /// - Parameters:
    ///   - <?php echo$subjectName ?>: the <?php echo$subjectName ?>
    ///   - documentUID: the documentUID
    ///   - success: the success closure
    ///   - failure: the failure closure
    open class func execute(_ <?php echo$subjectName ?>:<?php echo$subjectStringType ?>,
            <?php echo$registrySyntagm ?> documentUID:String,
            sucessHandler success: @escaping(_ context:HTTPContext)->(),
            failureHandler failure: @escaping(_ context:HTTPContext)->()){
    }