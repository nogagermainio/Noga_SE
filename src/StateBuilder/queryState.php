<?php declare(strict_types=1);

namespace Noga\StateBuilder;

class QueryState{
    protected array $data = [];
    protected array $registry = [];
    private static array $instance = [];
    private ?string $name = null; 

    public function __construct(){}

    public function getInstanceName():string{
        return $this->name ?:"";
    }

     public static function active(string $name): self
    {
        if (!isset(static::$instance[$name])) {
            $instance = new static();
            $instance->name = $name;
            static::$instance[$name] = $instance;
        }

        return static::$instance[$name];
    }

public function register(string $key):array{
     $this->registry[$key] = $this->data;
    return $this->registry[$key] ?: [];
}

public function get(string $key):array{
     if(!isset($this->registry[$key])){
        return ["undefinied key $key."];
    }

    return $this->registry[$key] ?: [];
}


public function show_registry():array{
    return $this->registry;
}


public function collect(string $type,mixed $data):static
{
    $this->data[$type][] = $data;
    return $this;
}

public function getState():array{
    return $this->data ?? [];
}

public function reset():static{
    $this->registry[] = [];
    return $this;
}

public static function show_Instance():array{
    return static::$instance ?: [];
}

}