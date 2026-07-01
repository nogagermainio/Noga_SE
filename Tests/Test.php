<?php
namespace Noga\Tests;

use Noga\CLI\Services\Render;
use Noga\Noga;

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

     $builder = Noga::with(
        "categories",
        Noga::table("categories", "c")
            ->select("id", "parent_id", "name")
            ->unionAll(Noga::u()
                ->from("categories")
                  ->select("id", "parent_id", "name")
            )
    ,true)
    ->table("products", "p")
    ->select("id", "name", "category_id")
      ->where(["active" => 1])->getQuery();
     
        Render::data($builder)->json();
    } 
}

/**
 *   "WITH RECURSIVE categories AS (
 * SELECT id,parent_id,name FROM categories AS c 
 *  UNION ALL  SELECT  id,parent_id,name FROM categories 
 *  )
 *  SELECT id,name,category_id FROM products AS p  WHERE active = :wh_72e31f89_active "
 */

// WITH RECURSIVE categories AS (
//     SELECT id,parent_id,name FROM categories AS c
//     UNION ALL
//     SELECT id,parent_id,name FROM categories
// )
// SELECT id,name,category_id FROM products AS p WHERE active = :wh_
