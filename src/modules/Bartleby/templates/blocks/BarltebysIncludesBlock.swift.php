<?php

$includeBlock='';
if (isset($isIncludeInBartlebysCommons) && $isIncludeInBartlebysCommons==true){
    $includeBlock .= stringIndent("import Alamofire",1);
}else{
    $includeBlock .= stringIndent("import Alamofire",1);
    $includeBlock .= stringIndent("import BartlebyKit",1);
}

?>