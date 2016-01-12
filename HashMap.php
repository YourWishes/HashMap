<?php
/*
 * Copyright 2016 Dominic Masters.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * HashMap v1.00
 * Java/C# like Maps for PHP.
 * 
 * @author Dominic Masters <dominic@domsplace.com>
 */
class HashMap implements JsonSerializable, ArrayAccess, Iterator {
    //Static Methods
    
    //Instance
    private $keys;
    private $values;
    
    private $keys_class;
    private $values_class;
    
    private $position;
    
    public function __construct($key_class, $val_class) {
        if(!class_exists($key_class)) throw new Exception('Class used for key does not exist.');
        if(!class_exists($val_class)) throw new Exception('Class used for val does not exist.');
        
        $this->keys_class = $key_class;
        $this->values_class = $val_class;
        
        $this->keys = array();
        $this->values = array();
    }
    
    public function getKeyType() {return $this->keys_class;}
    public function getValueType() {return $this->values_class;}
    
    public function isValidKeyClass($clazz) {return is_subclass_of($clazz, $this->keys_class);}
    public function isValidValueClass($clazz) {return is_subclass_of($clazz, $this->values_class);}
    
    public function isValidKey($clazz) {return $clazz instanceof $this->keys_class;}
    public function isValidValue($clazz) {return $clazz instanceof $this->values_class;}
    
    public function keySet() {return $this->keys;}
    
    private function getIndex(&$key_obj) {
        //Try Find
        if(($key = array_search($key_obj, $this->keys)) !== false) {
            return $key;
        }
        return -1;
    }
    
    public function isKeySet(&$key_obj) {
        if(!$this->isValidKey($key_obj)) throw new Exception('Invalid Key Type');
        return $this->getIndex($key_obj) !== -1;
    }
    
    /**
     * Returns the size of the array (Amount of keys in the map)
     * 
     * @return int
     */
    public function size() {
        return sizeof($this->keys);
    }
    
    /**
     * Returns true if the HashMap contains no items, false if it does.
     * 
     * @return bool
     */
    public function isEmpty() {
        return $this->size() > 0;
    }
    
    public function put(&$key, &$value) {
        //Validate class types
        if(!$this->isValidKey($key)) throw new Exception('Invalid Key Type');
        if(!$this->isValidValue($value)) throw new Exception('Invalid Value Type');
        
        //Try and get an existing index (if needed)
        $index = sizeof($this->keys);
        $old_index = $this->getIndex($key);
        if($old_index !== -1) {
            $index = $old_index;
        }
        
        //Now set the keys array index.
        $this->keys[$index] = $key;
        $this->values[$index] = $value;
    }
    
    public function get(&$key) {
        if(!$this->isValidKey($key)) throw new Exception('Invalid Key Type');
        $index = $this->getIndex($key);
        if($index === -1) return null;
        return $this->values[$index];
    }
    
    public function remove(&$key) {
        if(!$this->isValidKey($key)) throw new Exception('Invalid Key Type');
        $index = $this->getIndex($key);
        if($index === -1) throw new Exception('Key not found');
        array_splice($this->keys, $index, 1);
        array_splice($this->values, $index, 1);
    }
    
    private function removeByIndex($index) {
        array_splice($this->keys, $index, 1);
        array_splice($this->values, $index, 1);
    }

    public function jsonSerialize() {
        $obj = array();
        foreach($this->keys as $index => $key) {
            $obj[$index] = array(
                "key" => $key,
                "value" => $this->values[$index]
            );
        }
        return $obj;
    }
    
    function rewind() {
        $this->position = 0;
    }
    
    function current() {
        return $this->values[$this->position];
    }


    /**
     * Returns the key at the current() position().
     * Unfortunately due to the nature of PHP object keys are not supported.
     * 
     * What do you mean?
     *  Look at it like this;
     *      $left = new ClassLeft('Whatever Left');
     *      $right = new ClassRight('Whatever Right');
     *      $map = new HashMap('ClassLeft', 'ClassRight');
     *      $map->put($left, $right);
     *      foreach($map as $key => $value) {
     *          //$key is not $left, cuz PHP
     *      }
     * 
     * @return type
     */
    function key() {
        return $this->position;
    }

    function next() {
        ++$this->position;
    }
    
    function valid() {
        return isset($this->values[$this->position]);
    }
    
    public function offsetExists($offset) {
        return isset($this->values[$offset]);
    }

    public function offsetGet($offset) {
        return $this->values[$offset];
    }

    public function offsetSet($offset, $value) {
        if(!$this->isValidValue($value)) throw new Exception('Invalid Class Type.');
        $this->keys[$offset] = $key;
        $this->put($this->keys[$offset], $value);
    }

    public function offsetUnset($offset) {
        $this->removeByIndex($offset);
    }
}