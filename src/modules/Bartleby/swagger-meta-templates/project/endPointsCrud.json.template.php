<?php

include  FLEXIONS_SOURCE_DIR.'/Shared.php';


/* @var $f Flexed */
/* @var $d EntityRepresentation */

if (isset ( $f )) {
    $f->fileName = 'crudPathsFragment.json';
    $f->package = '';
}

/* TEMPLATES STARTS HERE -> */?>
{ "paths" : {
<?php
/* @var $d ProjectRepresentation */
/* @var $entity EntityRepresentation */

$counter=0;
foreach ($d->entities as $entity ) {
    $name=$entity->name;
    if(isset($prefix)){
        $name=str_replace($prefix,'',$name);
    }


    //$pluralized=lcfirst(P
    //luralization::pluralize($name));
    $counter++;
    $lastEntity=false;
    if ($counter==count($d->entities)){
        $lastEntity=true;
    }

    // EXCLUSION FROM CRUD
    // You can exclude entities containing a given string
    $shouldBeExcluded=false;
    $exclusion=array();
    if(isset($excludeEntitiesWith)){
        $exclusion=$excludeEntitiesWith;
    }
    foreach ($exclusion as $exclusionString ) {
        if(strpos($name,$exclusionString)!==false){
            $shouldBeExcluded=true;
        }
    }
    if($shouldBeExcluded){
        continue;//Let's exclude this entity from the CRUD
    }


    // UPDATE EXCLUSION
    $isUnModifiable=false;
    $unModifiable=array();
    if(isset($unDeletableEntitiesWith)){
        $unModifiable=$unModifiableEntitiesWith;
    }
    foreach ($unModifiable as $unModifiableString ) {
        if(strpos($name,$unModifiableString)!==false){
            $isUnModifiable=true;
        }
    }

    // DELETION EXCLUSION
    $isUndeletable=false;
    $undeletable=array();
    if(isset($unDeletableEntitiesWith)){
        $undeletable=$unDeletableEntitiesWith;
    }
    foreach ($undeletable as $undeletableString ) {
        if(strpos($name,$undeletableString)!==false){
            $isUndeletable=true;
        }
    }

    $pluralizedName=lcfirst(Pluralization::pluralize($name));

    ////////////////////////////
    // SINGLE INSTANCE CRUD
    ////////////////////////////

    $createBlock='
    "/'.$name.'" : {
        "post" : {
            "tags" : [
                "'.$pluralizedName.'"
            ],
        "summary" : "Creates a new '.$name.' to the system",
        "description" : "",
        "operationId" : "create'.ucfirst($name).'",
        "consumes" : [
                "application/json"
            ],
        "produces" : [
                "application/json"
            ],
        "parameters" : [
          {
            "in" : "body",
            "name" : "'.lcfirst($name).'",
            "description" : "'.$name.' object that needs to be added",
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
      }
    },';


    $readBlock= '
    "/'.$name.'/{'.lcfirst($name).'Id}" : {
        "get" : {
            "tags" : [
                "'.$pluralizedName.'"
            ],
            "summary" : "Find '.$name.' by ID",
            "description" : "Returns a single '.$name.'",
            "operationId" : "get'.ucfirst($name).'ById",
            "produces" : [
                    "application/json"
                ],
            "parameters" : [
              {
                "name" : "'.lcfirst($name).'Id",
                "in" : "path",
                "description" : "ID of '.$name.' to return",
                "required" : true,
                "type" : "string"
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
          }'
    ;

    $updateBlock=',
        "put" : {
            "tags" : [
                "'.$pluralizedName.'"
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
            "name" : "'.lcfirst($name).'",
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
      }';

    $deleteBlock=',
        "delete" : {
            "tags" : [
                "'.$pluralizedName.'"
            ],
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
                "name" : "'.lcfirst($name).'Id",
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
        }';

    $block=$createBlock;
    $block.=$readBlock;
    if($isUnModifiable==false){
        $block.=$updateBlock;
    }
    if($isUndeletable==false){
        $block.=$deleteBlock;
    }
    $block.='}';
    $block.=',';


    ////////////////////////////
    // COLLECTIONS  CRUD
    ////////////////////////////



    $createCollectionBlock='
    "/'.ucfirst($pluralizedName).'" : {
        "post" : {
            "tags" : [
                "'.$pluralizedName.'"
            ],
            "summary" : "Create '.$pluralizedName.' to the system",
            "description" : "",
            "operationId" : "create'.ucfirst($pluralizedName).'",
            "consumes" : [
                    "application/json"
                ],
            "produces" : [
                    "application/json"
                ],
            "parameters" : [
             {
                "in" : "body",
                "name" : "'.lcfirst($pluralizedName).'",
                "description" : "Collection of '.$name.' that needs to be added",
                "required" : true,
                "schema": {
                            "type": "array",
                            "items":
                            {
                                "$ref": "#/definitions/'.ucfirst($name).'"
                             }
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
    ';

    $readCollectionBlock= '    "get" : {
            "tags" : [
                "'.$pluralizedName.'"
            ],
            "summary" : "Find '.$pluralizedName.' by ID",
            "description" : "Returns a collection of '.$name.'",
            "operationId" : "get'.ucfirst($pluralizedName).'ByIds",
            "produces" : [
                    "application/json"
                ],
            "parameters" : [
              {
                "name" : "ids",
                "in" : "path",
                "description" : "IDS of the '.$pluralizedName.' to return",
                "required" : true,
                 "type": "array",
                 "items": {
                     "type": "string"
                  }
              }
            ],
            "responses" : {
               "200" : {
                        "description" : "successful operation",
               "schema": {
                    "type": "array",
                    "items": {
                    "$ref": "#/definitions/'.ucfirst($name).'"
                }
              },
              "400" : {
                        "description" : "Invalid IDS supplied"
              },
              "404" : {
                        "description" : "'.ucfirst($pluralizedName).' not found"
              }
            },
            "security" : [
              {
                  "api_key" : []
              }
            ]
          }
        }'
    ;

    $updateCollectionBlock=',
        "put" : {
            "tags" : [
                "'.$pluralizedName.'"
            ],
            "summary" : "Update an existing '.$name.'",
            "description" : "",
            "operationId" : "update'.ucfirst($pluralizedName).'",
            "consumes" : [
                    "application/json"
                ],
            "produces" : [
                    "application/json"
                ],
            "parameters" : [
              {
                "in" : "body",
                "name" : "'.lcfirst($pluralizedName).'",
                "description" : "Collection of '.ucfirst($name).' to update",
                "required" : true,
                "schema": {
                            "type": "array",
                            "items": {
                                "$ref": "#/definitions/'.ucfirst($name).'"
                             }
                    }
              }
            ],
            "responses" : {
              "400" : {
                 "description" : "Invalid IDS supplied"
              },
              "404" : {
                        "description" : "'.ucfirst($pluralizedName).' not found"
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
      }';

    $deleteCollectionBlock=',
        "delete" : {
            "tags" : [
                "'.$pluralizedName.'"
            ],
            "summary" : "Deletes some '.$pluralizedName.'",
            "description" : "",
            "operationId" : "delete'.ucfirst($pluralizedName).'",
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
                "name" : "'.lcfirst($pluralizedName).'Ids",
                "in" : "path",
                "description" : "'.ucfirst($pluralizedName).' Ids to delete",
                "required" : true,
                "type" : "integer",
                "format" : "int64"
              }
            ],
            "responses" : {
                    "400" : {
                        "description" : "Invalid '.$pluralizedName.' value"
              }
            },
            "security" : [
              {
                  "api_key" : []
              }
            ]
        }';

    $block.=$createCollectionBlock;
    $block.=$readCollectionBlock;
    if($isUnModifiable==false){
        $block.=$updateCollectionBlock;
    }
    if($isUndeletable==false){
        $block.=$deleteCollectionBlock;
    }
    $block.='}';
    if(!$lastEntity){
        $block.=',';
    }
    //$block.='}}}'.cr();
    echo $block;
}
?>
    }
}
<?php /*<- END OF TEMPLATE */


?>


