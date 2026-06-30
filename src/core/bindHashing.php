<?php
namespace Src\Core;

use LogicException;

class BindHashing{

    private string $bindParams = "";
    public function __construct(private string $prefix,private string $columns)
    {
        $col = str_replace(['.', '-'], '_', $this->columns);

        $hash = \bin2hex(\random_bytes(4));

        $this->bindParams = ":{$this->prefix}_{$hash}_{$col}";
       
    }
    
    /**
     * Summary of hash
     * @param string $prefix
     * @param string $columns
     * @return string
     */
    public static function hash(string $prefix,string $columns):string{
      $instance = new static($prefix,$columns);
      
      return $instance->bindParams;
    }
}