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
*/

class ActionRepresentation {
	
		/**
		 * @var array Parameters
		 */
		public $parameters = array();
	
		/**
		 * @var Parameter Additional parameters schema
		*/
		public $additionalParameters;
	
		/**
		 * @var string Name of the Operation
		 */
		public $name;
	
		/**
		 * @var string HTTP method
		 */
		public $httpMethod;
	
		/**
		 * @var string This is a short summary of what the operation does
		 */
		public $summary;
	
		/**
		 * @var string A longer text field to explain the behavior of the operation.
		 */
		public $notes;
	
		/**
		 * @var string HTTP URI of the Operation
		 */
		public $uri;
	
		/**
		 * @var string Class of the Operation object
		 */
		public $class;
	
		/**
		 * @var string This is what is returned from the method
		 */
		public $responseClass;
	
		/**
		 * @var string Type information about the response
		 */
		public $responseType;
	
		/**
		 * @var string Information about the response returned by the operation
		 */
		public $responseNotes;
	
	
		/**
		 * @var array Array of errors that could occur when running the Operation
		 */
		public $errorResponses;
	
		/**
		 * @var ServiceDescriptionInterface
		 */
		public $description;
	

}

?>