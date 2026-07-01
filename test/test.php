<?php
namespace Test;

use Src\CLI\Services\Render;
use Src\Noga;

class Test{

      public static function handle(){
      $array = [];
      $select = Noga::table("users")
      ->select("id")
      ->selectCase(Noga::c("id","12")->else("25")->as("c"),"status")
      ->innerJoin(Noga::j("noga","n")
      ->on("n.id","users.id"))
      ->unionAll(Noga::u()->from("membres","group"))
      ->unionAll(Noga::u()->add([Noga::table("users")->select("id","noms")->where(["id"=>12])]))
      ->where(["n.id"=>25])
      ->viewState();

    $up = Noga::update("users")
          ->set(["noms"=>"noga","prenoms"=>"germainio"])
          ->where(["id"=>12])
          ->viewState();

   $in = Noga::insert('users')
    ->columns('name', 'email')
    ->values('Test', 'test@example.com')
    ->viewState();

    $in1 = Noga::insert('users')
    ->from(__DIR__."/../membres.json")
    ->take()
    ->viewState();

    $de = Noga::delete('users')
    ->where(['status' => 'inactive', 'last_login <' => '2023-01-01'])
    ->viewState();
     
        Render::data($de)->json();
    } 
}