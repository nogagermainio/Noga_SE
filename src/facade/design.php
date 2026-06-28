<?php
namespace Src\Facade;
use BadMethodCallException;
use LogicException;

abstract class Design{

    protected static array $instance = [];


   protected static function getProcessInstance()
{
    $insta = new static();
    $class = $insta->getProcessClass();

    if (!isset(self::$instance[$class])) {
        self::$instance[$class] = new $class();
    }

    return self::$instance[$class];
}


    protected function getProcessClass(): string{
        throw new LogicException("Error : Process class is not definied ! ");
    } 

    public function __call(mixed $method, mixed $args)
    {
        // On regarde quel processor peut gérer la méthode
        foreach (self::getProcessInstance() as $key => $class) {
            $instance = $this->getProcessorInstance($key);
            if (method_exists($instance, $method)) {
            $result = $instance->$method(...$args);
            }else{
              $result = $instance::$method(...$args);
            }

            return $result;
        }

        throw new BadMethodCallException("invalide method $method");
    }

     public static function __callStatic(mixed $method, mixed $args)
    {
             $instance =  self::getProcessInstance();
             if(!method_exists($instance,$method)){
                throw new BadMethodCallException("Method $method inconnue");
             }

            return $instance->$method(...$args);
    }

        public static function swap(string $key, object $instance)
        {
            static::$instance[$key] = $instance;
        }

}