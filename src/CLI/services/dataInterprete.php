<?php
namespace Src\CLI\Services;

use PDO;
use PDOException;
use RuntimeException;
use Src\Noga;
class DataInterprete{
    protected array $argv = [];
    protected const METHOD = [
        'show',
        'count',
        'find',
        'argv',
        'search'
    ];

    public function __construct(array $args)
    {
        $this->argv = $args;

        // if(!\in_array($args[2],self::METHOD)){
        //     echo "⚠️  La méthode '{$args[2]}' n'existe pas dans la commande 'data'.\n";
        //     echo "=============================\n";
        //     return;
        // }
    }


    public function show(){
        $table = ask("entrer une table  ");
        if(empty($table)){
            echo "⚠️  Vous devez entrer le nom d'une table.\n";
            return;
        }
        try{
if(isset($this->argv[3])){
     switch($this->argv[3]){
            case '--limit': 
                 $limit = \ask("entrer une limit ");

              $res = Noga::table($table)->Limit($limit)->get(PDO::FETCH_ASSOC);

            break;
            case '--all':
           $res = Noga::table($table)
                    ->get(PDO::FETCH_ASSOC);
             break;
             case '--where':
                $colonnes = ask("entrer une colonnes ");
                $values = ask("entrer une valeur  ");
                
                if(empty($colonnes) || empty($values)){
                    echo color("invalide parametre colonnes ou valuer ! ","red");
                    return;
                }
                $res = Noga::table($table)
                        ->where([$colonnes=>$values])
                        ->get(PDO::FETCH_ASSOC);
                break;

             default:
              $res = Noga::table($table)
                        ->get(PDO::FETCH_ASSOC);
            break;
        };
}else{
     $res = Noga::table($table)
                        ->get(PDO::FETCH_ASSOC);
}
       
            
     
            
        echo color(" \nRESULTATS DE LA TABLE $table :\n",'green');
        echo "\n";
        echo "=============================\n";
        $not = ['mdp','password','Code','code','token'];
        foreach($res as $r){
            foreach($r as $k => $v){
                if(!\in_array($k,$not)){
                    \printf("%-40s => %s\n",
                    color(\strtoupper($k),'yellow')
                    ,color($v,'green'));    
            } 
        }
            echo "\n";
            echo "==================================================================\n";  
            echo "\n";
        }

            echo color(" FIN DES RESULTATS ",'green')." TOTAL : ". color(count($res),'blue');
            echo "\n";
            echo "==================================================================\n"; 
            echo "\n";
           


      
         }catch(PDOException $e){
            echo json_encode(["erreur "=>$e->getMessage()],\JSON_PRETTY_PRINT);
          return;
        }
    }


    public function count(){

        $table = \ask("entrer une table ");
        if(\str_starts_with('[',$table) && \str_ends_with(']',$table)){

        }
        try{

        $res = Noga::table($table)->get();
    

        }catch(PDOException $e){
            echo json_encode(["erreur "=>$e->getMessage()],\JSON_PRETTY_PRINT);
            exit;
        }
        $total = count($res);

        echo color("dans votre table $table il y a ",'yellow')." : ".color($total,'green'). " lignes \n";

        exit;
    }


    public function find(){

         $table = ask("entrer une table  ");
        if(empty($table)){
            echo "⚠️".color("Vous devez entrer le nom d'une table",'red')."\n";
            return;
        }
        if(!is_string($table)){
         throw new RuntimeException("table most be of type string ");
        }
       $id = ask("entrer un id  ");
        try{
          
            $res = Noga::table($table)
                        ->where(["id"=>$id])
                        ->get(PDO::FETCH_ASSOC);

            echo color(" \nRESULTATS DE LA RECHERCHE $table :\n",'green');
            echo "\n";  
        echo "=============================\n";
        $not = ['mdp','password','Code','code','token'];
        // $max = max(array_map('strlen', array_keys($res[0])));
            if(!empty($res)){
                        foreach($res as $r){
                 foreach($r as $k=>$v){
                    if(!in_array($k,$not)){
                        \printf("%-40s => %s\n",color(\strtoupper($k),'yellow'),color($v,'green'));
                    }
            }  

          }

     }else{
        error("no result for id {$id} ");           
     }
        echo "\n";
        echo "=============================\n";
            echo color("\nFIN\n",'green');
        return;

         }catch(PDOException $e){
            echo "erreur =>".color($e->getMessage(),'red');
           return;
        }
    }


    public function search(){
        
         $table = ask("entrer une table  ");
        if(empty($table)){
            echo "⚠️".color("Vous devez entrer le nom d'une table",'red')."\n";
            exit;
        }
        try{

  switch($table){
                case 'membres_msbc':
                     $search = ask("Recherches   ");
                    
                     if(empty($search)){
                        echo color("Vous devez entrer un terme de recherche",'red')."\n";
                        return;
                     }

                $res = Noga::table($table)->whereLike([
                        "id"=>$search,
                        "noms"=>$search,
                        "prenoms"=>$search,
                        "telephone"=>$search,
                        "identifiant"=>$search,
                        "adresse"=>$search,
                        "cin"=>$search,
                        "naissance"=>$search,
                        "arrondissement"=>$search,
                        "genre"=>$search,
                        "date"=>$search,
                        "categorie"=>$search,
                        "users"=>$search,
                        "admin"=>$search
                        ])
                        ->get(PDO::FETCH_ASSOC);

                    break;
                case 'administration':
                     $search = ask("Recherches dans administration :  ");
                      if(empty($search)){
                        echo color("Vous devez entrer un terme de recherche",'red')."\n";
                        return;
                      }

                     $res = Noga::table($table)->whereLike([
                        "id"=>$search,
                        "noms"=>$search,
                        "prenoms"=>$search,
                        "telephone"=>$search,
                        "adresse"=>$search,
                        "cin"=>$search,
                        "naissance"=>$search,
                        "date"=>$search,
                        "fonction"=>$search,
                        "status"=>$search,
                        "compte"=>$search
                        ])
                        ->get(PDO::FETCH_ASSOC);

                    break;
                case 'paiement':
                     $search = ask("Recherches   ");
                     if(empty($search)){
                        echo color("Vous devez entrer un terme de recherche",'red')."\n";
                        return;
                     }
                      $res = Noga::table($table)->whereLike([
                            "id"=>$search,
                            "ref"=>$search,
                            "identifiant"=>$search,
                            "montant"=>$search,
                            "date"=>$search,    
                            "mois"=>$search,
                            "dateDePaiement"=>$search,
                            "status"=>$search,
                            "users"=>$search,
                            "dateDexpiration"=>$search
                        ])
                        ->get(PDO::FETCH_ASSOC);
                    break;
                default :

                    break;
            }


            echo color(" \nRESULTATS DE LA RECHERCHE $table :\n",'green');
            echo "\n";  
        echo "=============================\n";
        $res = [];
        $not = ['mdp','password','Code','code','token'];
        // foreach($res as $r){
        //          foreach($r as $k=>$v){
        //             if(!in_array($k,$not)){
        //                 \printf("%-40s => %s\n",color(\strtoupper($k),'yellow'),color($v,'green'));
        //             }
        //     }   
        \var_dump($res);
        
        // }
        // echo "\n";
        // echo "=============================\n";
        //     echo color("\nFIN\n",'green');
        // exit;

         }catch(PDOException $e){
            echo "erreur =>".color($e->getMessage(),'red');
            exit;
        }
    }

    public function argv(){
       echo json_encode($this->argv,\JSON_PRETTY_PRINT);
        return;
    }
}