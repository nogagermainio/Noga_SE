<?php
namespace Test;

use Src\CLI\Services\Render;
use Src\Noga;
use Src\QueryBuilder\Insert\Insert;

class Test{
      public static function handle(){
        $in = Insert::table('noga')
        ->columns(
          "id",
          "noms",
          "prenoms",
          "tel",
          "fonction"
        );

        $req1 = $in->values(
          12,
          "noga",
          "germainio",
          "0340488021"
          )
          ->values("agent")
          ->debugSql();

        $req2 = $in->driver('pgsql')->values(
          13,
          "exeth",
          "Ephore",
          "0380823939",
          "agent"
          )
          ->debugSql();

        Render::data(["req1"=>$req1,"req2"=>$req2])->json();
    } 
}