<?php
namespace Test;

use Src\CLI\Services\Render;
use Src\Core\CacheManager;
use Src\Router\Definition;
use Src\Router\Route;
use Src\Router\Router;

class Test{

    public static function handle(){
      
       $dto = new Definition(
        "class",
       ["class"]
      );

        $dto = new Definition(
        "class_method",
       ["class","execute"]
      );

      Render::data([$dto->execute])->json();
    } 
}