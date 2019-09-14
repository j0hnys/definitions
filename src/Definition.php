<?php

namespace j0hnys\Definitions;

use J0hnys\Typed\T;
use J0hnys\Typed\Struct;

class Definition
{

    /**
     * check if data matches definition
     *
     * @param array $data
     * @param array $haystack
     * @return void
     */
    public function check(array $data, string $definition_property = '',  array $haystack = null): void
    {
        $className = get_class($this);
        $oClass = new \ReflectionClass($className);
        $constants = $oClass->getConstants();

        if ($haystack === null) {
            if ($definition_property === '') {
                $definition_property = array_key_first($constants);
            }
            $haystack = $constants[ $definition_property ];
        }

        foreach ($haystack as $key => $value) {
            if (!isset($data[$key]) && strpos($key, '{{') === false) {
                throw new \Exception('data key: '.$key.' is not set', 1);
            }

            if (is_array($value)) {
                if (strpos($key, '{{') === false) {
                    $this->check( $data[$key], $definition_property, $haystack[$key]);
                } else {
                    $array_values = array_values($data);
                    foreach ($array_values as $array_value) {
                        $this->check( $array_value, $definition_property, $haystack[$key]);
                    }
                }
            } 
            
            $key_definition_type = '';
            $value_definition_type = '';

            if (preg_match_all("/({{)[a-zA-Z]+(}})/", $key)) {
                $definition_type_name = str_replace('{{','',$key);
                $definition_type_name = str_replace('}}','',$definition_type_name);

                $key_definition_type = $constants[$definition_type_name];
                
                $array_keys = array_keys($data);

                if (is_array($key_definition_type)) {
                    foreach ($array_keys as $array_key) {
                        if (!in_array($array_key, $key_definition_type)) {
                            throw new \Exception("unknown type", 1);
                        }
                    }
                } else if (preg_match_all("/(T::)[a-zA-z]+\(\)/", $key_definition_type)) {
                    $key_definition_type = str_replace('T::', T::class.'::', $key_definition_type);
                    $result = eval('return '.$key_definition_type.';');
                    
                    $struct_check = new Struct([
                        'value' => $result,
                    ]);

                    foreach ($array_keys as $array_key) {
                        $struct_check->set([
                            'value' => $array_key
                        ]);
                    }
                } else {
                    throw new \Exception('unknown type "'.$value.'"', 1);                        
                }
            }

            if (is_string($value)) {
                if (preg_match_all("/({{)[a-zA-Z]+(}})/", $value)) {
                    $definition_type_name = str_replace('{{','',$value);
                    $definition_type_name = str_replace('}}','',$definition_type_name);
    
                    $value_definition_type = $constants[$definition_type_name];

                    if (is_array($value_definition_type)) {
                        if (!in_array($data[$key], $value_definition_type)) {
                            throw new \Exception("unknown type", 1);
                        }
                    } else if (preg_match_all("/(T::)[a-zA-z]+\(\)/", $value_definition_type)) {
                        $value_definition_type = str_replace('T::', T::class.'::', $value_definition_type);
                        $result = eval('return '.$value_definition_type.';');
                        
                        $struct_check = new Struct([
                            'value' => $result,
                        ]);
    
                        $struct_check->set([
                            'value' => $data[$key]
                        ]);
                    } else {
                        throw new \Exception('unknown type "'.$value_definition_type.'"', 1);                        
                    }    
                } else if (preg_match_all("/(T::)[a-zA-z]+\(\)/", $value)) {
                    $value = str_replace('T::', T::class.'::', $value);
                    $result = eval('return '.$value.';');

                    $struct_check = new Struct([
                        'value' => $result,
                    ]);

                    $check_key = $key;
                    if (is_array($key_definition_type)) {
                        $data_key = array_keys($data)[0];
                        if (in_array($data_key, $key_definition_type)) {
                            $check_key = $data_key;            
                        }
                    }

                    $struct_check->set([
                        'value' => $data[$check_key]
                    ]);
                } else if ($data[$key] !== $value) {
                    throw new \Exception('Value of "'.$key.'" does not match "'.$value.'"', 1);
                }
            }
        }
    }

    /**
     * checks if path string follows a definition path
     *
     * @param string $path
     * @param string $definition_property
     * @param boolean $set_last_element_value
     * @return void
     */
    public function checkPath(string $path, string $definition_property = 'schema', $set_last_element_value = true): void
    {
        $parts = explode('/', $path);   

        $parts_nested = [];
        $this->nestArray($parts, $parts_nested, $set_last_element_value);

        $this->checkPathRecursive($parts_nested, $definition_property, null);
    }
    /**
     * @param array $data
     * @param string $definition_property
     * @param array $haystack
     * @return void
     */
    private function checkPathRecursive(array $data, string $definition_property = '',  array $haystack = null)
    {
        $className = get_class($this);
        $oClass = new \ReflectionClass($className);
        $constants = $oClass->getConstants();

        if ($haystack === null) {
            if ($definition_property === '') {
                $definition_property = array_key_first($constants);
            }
            $haystack = $constants[ $definition_property ];
        }
        
        foreach ($haystack as $key => $value) {
            if (isset($data[$key]) && empty($data[$key])) {
                return;
            }
            if (!isset($data[$key]) && strpos($key, '{{') === false) {
                $data_array_key = $key;
                if (is_array($data)) {
                    $data_array_key = array_key_first($data);
                } else if (is_string($data)) {
                    $data_array_key = $data;
                }
                if (!isset($haystack[$data_array_key])) {
                    throw new \Exception('data key: '.$data_array_key.' is not set', 1);
                }
            }

            if (is_array($value)) {
                if (strpos($key, '{{') === false) {
                    if (isset($data[$key])) {
                        $this->checkPathRecursive( $data[$key], $definition_property, $haystack[$key] );
                    }
                    return;
                } else {
                    $array_values = array_values($data);
                    foreach ($array_values as $array_value) {
                        $this->checkPathRecursive( $array_value, $definition_property, $haystack[$key] );
                        return;
                    }
                }
            } 
        }
    }
    /**
     * @param array $data
     * @param [type] $haystack
     * @param boolean $set_last_element_value
     * @return void
     */
    private function nestArray(array $data, &$haystack, $set_last_element_value = true)
    {
        for ($i=0,$ilength=count($data); $i<$ilength; $i++) { 
            $key = array_shift($data);
            $haystack[ $key ] = $this->nestArray($data, $haystack[$key], $set_last_element_value);
            if ($ilength == 1 && $set_last_element_value) {    //<-- to make the last element value, not nested array
                $haystack = $key;
            }
            break;
        }

        return $haystack;
    }


    /**
     * @return array
     */
    public function get(): array {
        $className = get_class($this);
        $oClass = new \ReflectionClass($className);
        $constants = $oClass->getConstants();

        return $constants;
    }

    /**
     * @param [type] $needle
     * @param [type] $haystack
     * @param boolean $strict
     * @param array $path
     * @return array|bool
     */
    private function arraySearchRecursive($needle, $haystack, $strict = false, $path = [])
    {
        if ( !is_array($haystack) ) {
            return [];
        }
    
        foreach($haystack as $key => $value ) {
            if (is_array($value) && $subPath = $this->arraySearchRecursive($needle, $value, $strict, $path) ) {
                $path = array_merge($path, [$value]);
                return $path;
            } else if ( (!$strict && strpos($key, strtoupper($needle)) !== false ) || ($strict && $key === $needle) ) {
                $path []= $key;
                return $path;
            }
        }

        return [];
    }


    /**
     * @param string $constant
     * @return array
     */
    public function search(string $constant, bool $strict = false): array
    {
        $all_constants = $this->get();

        $result = [];
        foreach ($all_constants as $key => $value) {
            $result[$key] = $this->arraySearchRecursive($constant, ["$key" => $value], $strict);
            if (!empty($result[$key])) {
                $result[$key] = $result[$key][0];
            }
        }        

        return $result;
    }

    /**
     * @param string $constant
     * @return boolean
     */
    public function exist(string $constant, bool $strict = true): bool
    {
        $result = $this->search($constant, $strict);

        $exist = false;
        foreach ($result as $element) {
            if (!empty($element)) {
                $exist = true;
                break;
            }
        }

        return $exist;
    }

}
