<?php

//  Created by Benoit Pereira da Silva on 20/04/2013.
//  Copyright (c) 2013  http://www.pereira-da-silva.com

// This file is part of Flexions

// Flexions is free software: you can redistribute it and/or modify
// it under the terms of the GNU LESSER GENERAL PUBLIC LICENSE as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

// Flexions is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU LESSER GENERAL PUBLIC LICENSE for more details.

// You should have received a copy of the GNU LESSER GENERAL PUBLIC LICENSE
// along with Flexions  If not, see <http://www.gnu.org/licenses/>


class DefaultLoops {
    const ENTITIES = 'entities'; // Generaly for generating models
    const ACTIONS = 'actions'; // For example for endpoints generations
    const PROJECT = 'project'; // For a glob
}

/**
 *  An hypotypose is a structured description that
 *  uses  agnostic data structure
 *
 *  An  hypotypose instance is persistent during all flexions phases
 *
 *  Preprocessing    :
 *  hypotypose is instanciated and populated from sources.
 *
 *  Processing        :
 *  hypotypose is used by the loops and passed to the templates.
 *  if the template returns some contents its content is stored in a Flexed instance
 *
 *  PostProcessing :
 *  hypotypose->flexedList(s) are iterated to generate the files from  $f->source
 *
 * @package   flexions
 * @author          benoit@pereira-da-silva.com
 * @version     0.0.1
 */
final class Hypotypose extends stdClass {


    /**
     *
     * @var array
     */
    protected $_descriptor = array();


    /**
     *
     * @var int
     */
    private $_loopIndex;

    /**
     *
     * @var array
     */
    protected $_loopNameList;


    /**
     * The list of the flexed files
     * @var array
     */
    public $flexedList = array();

    /**
     * class prefix
     * @var string
     */
    public $classPrefix = "";


    /**
     * Call this method to get singleton
     *
     * @return Hypotypose
     */
    public static function Instance($loopsList = NULL) {
        static $inst = NULL;
        if ($inst === NULL) {
            $inst = new Hypotypose ($loopsList);
        }
        return $inst;
    }

    /**
     */
    function __construct($loopsList = NULL) {
        $this->_loopIndex = -1;
        $this->_loopNameList = array();
    }

    /**
     *
     * @return the $loopName string
     */
    public function getLoopName() {
        if ($this->_loopIndex >= 0) {
            return $this->_loopNameList [$this->_loopIndex];
        } else {
            return NULL;
        }
    }

    /**
     *
     * @return  array
     */
    public function getContentForCurrentLoop() {
        $name = $this->getLoopName();
        if ($name != NULL) {
            if (array_key_exists($name, $this->_descriptor)) {
                return $this->_descriptor[$name];
            }
        }
        return NULL;
    }

    /**
     *
     * @param string $name
     * @return string:|NULL
     */
    public function getContentForLoopWithName($name) {
        if (array_key_exists($name, $this->_descriptor)) {
            return $this->_descriptor[$name];
        }
        return NULL;
    }

    /**
     *
     * @return boolean
     */
    public function nextLoop() {
        $this->_loopIndex++;
        if ($this->_loopIndex < count($this->_loopNameList)) {
            return true;
        } else {
            $this->_loopIndex = -1;
            return false;
        }
    }

    /**
     * Sets the descriptor for a given loop name
     * this is done once per loop in the preprocessor
     *
     * @param array $descriptor
     * @param string $loopName
     * @return boolean
     */
    public function setLoopDescriptor($descriptor, $loopName) {
        if (!array_key_exists($loopName, $this->_loopNameList)) {
            // We add the descriptor
            $this->_loopNameList[] = $loopName;
            $this->_descriptor[$loopName] = $descriptor;
            $this->flexedList[$loopName] = array();
            $this->_descriptor[$loopName] = $descriptor;
            return true;
        }
        return false;
    }

    /**
     *
     * @param Flexed $flexed
     */
    public function  addFlexed(Flexed $flexed) {
        $this->flexedList [$this->getLoopName()] [] = $flexed;
    }


    public function getFlatFlexedList() {
        $flatList = array();
        foreach ($this->flexedList as $subList) {
            if (is_array($subList)) {
                $flatList = array_merge($flatList, $subList);
            }
        }
        return $flatList;
    }
}

?>