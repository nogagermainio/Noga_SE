<?php
namespace Test;

use Src\CLI\Services\Render;
use Src\Noga;

class Test{

      public static function handle(){
      $array = [];
      $select = Noga::table("users")
      ->select("id")
      ->selectCase(Noga::cases()->when("id","12")->else("25")->as("c"))
      ->innerJoin(Noga::j("noga","n")
      ->on("n.id","users.id"))
      ->unionAll(Noga::u()->from("membres","group"))
      ->where(["n.id"=>25])
      ->viewState();

    $in = Noga::update("users")
          ->set(["noms"=>"noga","prenoms"=>"germainio"])
          ->where(["id"=>12])
          ->viewState();
     
        Render::data($select)->json();
    } 
}