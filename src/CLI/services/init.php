<?php declare(strict_types=1);
namespace Noga\CLI\Services;

use Noga\CLI\Services\Render;
use Noga\Noga;

class Init
{
    private array $argv = [];
    public function __construct(array $args = [])
    {
       $this->argv = $args;
    }

    public function init()
    {   
        
        $dir = Noga::get("base_path") .'/Config/';
        $file = "{$dir}ngconfig.ng";

           if($this->argv[2] === "--dump"){
         $cont = \file_get_contents($file);

        echo " configuration Noga : ". color('ngconfig.ng',"yellow")."\n";
        echo "----------------------------------------\n";
        echo $cont . "\n";
        echo "----------------------------------------\n";
        return;
    }

        echo "---------------------------------------------------------------------------\n";
              echo color("WELCOME TO NOGA FRAMEWORK INIT CONFIGURATION FILE GENERATOR\n",'green');
        echo "----------------------------------------------------------------------------\n";
        echo "This utility will help you create a configuration file for your ".color("Noga",'yellow')."\n\n";
        echo "\n";
        echo "\n";
    
    $choose = ask("vous avez creer vraiment cette fichier de configuration (Y\N) ");
       

    if(in_array($choose,['Y','y'])){
       
    if (file_exists($file)) {
        echo "⚠️".color(" Le fichier de configuration 'ngconfig.ng' existe déjà.\n",'red');

        $cont = \file_get_contents($file);

        echo "Contenu actuel du fichier:\n";
        echo "----------------------------------------\n";
        echo $cont . "\n";
        echo "----------------------------------------\n";
        
        return; 
    }

    if(!is_dir($dir)){
        mkdir($dir,0777,true);
    }

    $content = self::fileContent('ngconfig');
    file_put_contents($file, $content);

    echo "✅ Fichier de configuration ".color("ngconfig.ng",'yellow')." créé avec succès dans le dossier $file.\n";

    }else if(\in_array($choose,["N","n"])){
        return;
    }

}


public static function boot(array $command = []){
    echo "---------------------------------------------------------------------------\n";
    echo "-------------------------- ". color("WELCOME TO THE NOGA",'green')." -----------------------\n";
    echo "----------------------------------------------------------------------------\n";
    echo "-------------------------- ".color("version 0.1.0","green")." ----------------------\n";
    echo "\n";
    echo "\n";
    echo "----------------------------------------------------------------------------\n";
    echo "----------------------------- "; \success('COMMAND'); echo " ----------------------------\n";
    echo "\n";
    echo "\n";
    Render::data($command)->array();

    return;
} 

public static function fileContent(string $name){
    return '# ng-config v0.1.1
# Configuration file for the mini-framework noga application
# Database settings
# Adjust these settings according to your environment

#  ============= mysql
string DB_HOST = "localhost"
int DB_PORT = 3306

#  ============ Database connection settings name of users

string MY_USERSNAME = "root"
string MY_PASSWORD = ""
string MY_DATABASE = "msbc"
string MY_DRIVER = "mysql"
string MY_CHARSET = "utf8mb4"
string MY_COLLATION = "utf8mb4_unicode_ci"

array MY_OPTIONS = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
]

# ================= Database Sqlite connection settings config

string Lite_driver = "sqlite"
string Lite_db = "Sqlite.db"
string Lite_foreign_keys = "PRAGMA foreign_keys = ON"
array Lite_option = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
]

# ================== Database postgreSQL connection  settings config

string PG_HOST = "localhost"
int PG_PORT = 5432
string PG_USERSNAME = "postgres"
string PG_PASSWORD = "426513"
string PG_DATABASE = "postgres"
string PG_DRIVER = "pgsql"
string PG_CHARSET = "UTF-8"

array PG_OPTIONS = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
]

    ';
}


}
