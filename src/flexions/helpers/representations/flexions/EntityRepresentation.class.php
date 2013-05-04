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

class EntityRepresentation {

	/**
	 * @var string Name of the object
	 */
	public $name;
	
	/**
	 * @var array properties of the object
	 */
	public $properties=array();
	
	/**
	 * @var  string When the type is an object, you can specify the class that the object must implement
	*/
	public $instanceOf;
	
	/**
	 * @var array of interface names
	 */
	public $implements=array();
	
	
	/**
	 * Current iteration property
	 * @var int
	 */
	protected  $_propertyIndex=-1;
	
	
	
	/**
	 * Return true while there is a property
	 * @return boolean
	 */
	public function iterateOnProperties(){
		$this->_propertyIndex++;
		if($this->_propertyIndex< count($this->properties)){
			return true;
		}else{
			// Reinitialise
			$this->_propertyIndex=-1;
			return  false;
		}
	}
	
	/**
	 * Returns the current iterated property
	 * @return PropertyRepresentation
	 */
	public function getProperty(){
		$nb=count($this->properties);
		if($this->_propertyIndex<$nb && $nb>0 ){
			$keys  = array_keys( $this->properties);
			return $this->properties[ $keys[$this->_propertyIndex]];
		}
		return null;
	}
	
	/**
	 * 
	 * @return boolean
	 */
	public function firstProperty(){
		return( $this->_propertyIndex==0);
	}
	/**
	 * 
	 * @return boolean
	 */
	public function lastProperty(){
		return ( $this->_propertyIndex== count($this->properties)-1);
	}
	
}

?>