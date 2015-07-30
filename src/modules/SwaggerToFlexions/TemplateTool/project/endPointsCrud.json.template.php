<?php

include  FLEXIONS_SOURCE_DIR.'/Shared.php';


/* @var $f Flexed */
/* @var $d EntityRepresentation */

if (isset ( $f )) {
    $f->fileName = 'crudPathsFragment.json';
    $f->package = '';
}

/* TEMPLATES STARTS HERE -> */?>
{
    "paths" : {
<?php
/* @var $d ProjectRepresentation */
/* @var $entity EntityRepresentation */

$counter=0;
foreach ($d->entities as $entity ) {
    $name=$entity->name;
    if(isset($prefix)){
        $name=str_replace($prefix,'',$name);
    }
    //$pluralized=lcfirst(Pluralization::pluralize($name));
    $counter++;
    $lastEntity=false;
    if ($counter==count($d->entities)){
        $lastEntity=true;
    }
    $block='
    "/'.$name.'" : {
        "post" : {
            "tags" : [
                "'.$name.'"
            ],
        "summary" : "Add a new '.$name.' to the system",
        "description" : "",
        "operationId" : "add'.ucfirst($name).'",
        "consumes" : [
                "application/json"
            ],
        "produces" : [
                "application/json"
            ],
        "parameters" : [
          {
            "in" : "body",
            "name" : "body",
            "description" : "'.$name.' object that needs to be added to the store",
            "required" : true,
            "schema" : {
              "$ref" : "#/definitions/'.ucfirst($name).'"
            }
          }
        ],
        "responses" : {
                "405" : {
                    "description" : "Invalid input"
          }
        },
        "security" : [
          {
              "api_key" : []
          }
        ]
      },
      "put" : {
            "tags" : [
                "'.$name.'"
            ],
        "summary" : "Update an existing '.$name.'",
        "description" : "",
        "operationId" : "update'.ucfirst($name).'",
        "consumes" : [
                "application/json"
            ],
        "produces" : [
                "application/json"
            ],
        "parameters" : [
          {
              "in" : "body",
            "name" : "body",
            "description" : "'.ucfirst($name).' object that needs to be added to the store",
            "required" : true,
            "schema" : {
              "$ref" : "#/definitions/'.ucfirst($name).'"
            }
          }
        ],
        "responses" : {
          "400" : {
             "description" : "Invalid ID supplied"
          },
          "404" : {
                    "description" : "'.ucfirst($name).' not found"
          },
          "405" : {
                    "description" : "Validation exception"
          }
        },
        "security" : [
          {
              "api_key" : []
          }
        ]
      }
    },
    "/'.$name.'/{'.$name.'Id}" : {
        "get" : {
        "summary" : "Find '.$name.' by ID",
        "description" : "Returns a single '.$name.'",
        "operationId" : "get'.ucfirst($name).'ById",
        "produces" : [
                "application/json"
            ],
        "parameters" : [
          {
             "name" : "'.$name.'Id",
            "in" : "path",
            "description" : "ID of '.$name.' to return",
            "required" : true,
            "type" : "integer",
            "format" : "int64"
          }
        ],
        "responses" : {
                "200" : {
                    "description" : "successful operation",
            "schema" : {
                        "$ref" : "#/definitions/'.ucfirst($name).'"
            }
          },
          "400" : {
                    "description" : "Invalid ID supplied"
          },
          "404" : {
                    "description" : "'.ucfirst($name).' not found"
          }
        },
        "security" : [
          {
              "api_key" : []
          }
        ]
      },
      "delete" : {
        "summary" : "Deletes a '.$name.'",
        "description" : "",
        "operationId" : "delete'.ucfirst($name).'",
        "produces" : [
                "application/json"
            ],
        "parameters" : [
          {
              "name" : "api_key",
            "in" : "header",
            "required" : false,
            "type" : "string"
          },
          {
            "name" : "'.$name.'Id",
            "in" : "path",
            "description" : "'.ucfirst($name).' id to delete",
            "required" : true,
            "type" : "integer",
            "format" : "int64"
          }
        ],
        "responses" : {
                "400" : {
                    "description" : "Invalid '.$name.' value"
          }
        },
        "security" : [
          {
              "api_key" : []
          }
        ]
      }
    }';
    if(!$lastEntity){
        $block.=',';
    }
    echo $block;
}
?>


    }
}
<?php /*<- END OF TEMPLATE */?>
