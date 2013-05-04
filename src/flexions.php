<?php

/*
Created by Benoit Pereira da Silva on 20/04/2013.
Copyright (c) 2013  http://www.pereira-da-silva.com

This file is part of Flexions

Flexions is free software: you can redistribute it and/or modify
it under the terms of the GNU LESSER GENERAL PUBLIC LICENSE as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Flexions is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU LESSER GENERAL PUBLIC LICENSE for more details.

You should have received a copy of the GNU LESSER GENERAL PUBLIC LICENSE
along with Flexions  If not, see <http://www.gnu.org/licenses/>

## EXPLANATIONS ##

Flexions is a simple, easy to use (but powerfull ) code generator

Usage of the Php command line : 

source 									:   the generation source folder path
descriptor								:   the descriptor file name
templates 								:  	templates relative path separated by commas for advanced cibled generation 
													OR  "* " for recursive generation  for simple hierarchies
													(default value =="*") 
destination 								:  	destination path (default value is =="out/standard/")
preProcessors 						: 	the pre-processors separated by commas
postProcessors 						: 	the post-processors separated by commas


*/

define ( "ECHO_LOGS", false );
define ( "FLEXIONS_ROOT_DIR", __DIR__ . '/' );
define ( "VERBOSE_FLEXIONS", true );

include_once 'flexions/core/flexions.script.php';