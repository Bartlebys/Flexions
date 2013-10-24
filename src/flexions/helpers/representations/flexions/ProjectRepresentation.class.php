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

class ProjectRepresentation {
	
		/**
		 * @var array Array of ActionRepresentation
		 */
		public  $actions = array();
	
		/**
		 * @var array Array of API entities (models) 
		*/
		public $entities = array();
	
		/**
		 * @var string Name of the API
		*/
		public $name;
	
		/**
		 * @var string API version
		 */
		public $apiVersion;
	
		/**
		 * @var string Summary of the API
		 */
		public $description;
	
		/**
		 * @var array Any extra API data
		 */
		public $extraData = array();
	
		/**
		 * @var string baseUrl/basePath
		*/
		public $baseUrl;
		
		
		/**
		 *  Project wide class prefix
		 * @var string
		 */
		public $classPrefix="";
		
		
		/**
		 * @var array an associative array to pass specific metadata
		 */
		public $metadata;
		
		
		// Actions
		

		/**
		 * Current iteration action index
		 * @var int
		 */
		protected  $_actionIndex=-1;
		
		/**
		 * Return true while there is an action
		 * @return boolean
		 */
		public function iterateOnActions(){
			$this->_actionIndex++;
			if($this->_actionIndex< count($this->actions)){
				return true;
			}else{
				// Reinitialise
				$this->_actionIndex=-1;
				return  false;
			}
		}
		
		/**
		 * Returns the current iterated action
		 * @return ActionRepresentation
		 */
		public function getAction(){
			$nb=count($this->actions);
			if($this->_actionIndex<$nb && $nb>0 ){
				$keys  = array_keys( $this->actions);
				return $this->actions[ $keys[$this->_actionIndex]];
			}
			return null;
		}
		
		
		// Entities
		
		/**
		 * Current iteration action index
		 * @var int
		 */
		protected  $_entityIndex=-1;
		
		/**
		 * Return true while there is an entity
		 * @return boolean
		 */
		public function iterateOnEntities(){
			$this->_entityIndex++;
			if($this->_entityIndex< count($this->actions)){
				return true;
			}else{
				// Reinitialise
				$this->_entityIndex=-1;
				return  false;
			}
		}
		
		/**
		 * Returns the current iterated entity
		 * @return EntityRepresentation
		 */
		public function getEntity(){
			$nb=count($this->entities);
			if($this->_entityIndex<$nb && $nb>0 ){
				$keys  = array_keys( $this->entities);
				return $this->entities[ $keys[$this->_entityIndex]];
			}
			return null;
		}
		
}

?>