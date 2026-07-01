<?php declare(strict_types=1);

use Noga\Core\NgManager;
use Noga\Core\DateManager;

 function clean(string $name){
    // $name = strtolower($name);
    $name = iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$name);
    $name = preg_replace('/[^\w]+/','_',$name);
    $name = preg_replace('/_+/','_',$name);
    $name = trim($name, '_');
    return $name;
}

/**
 * Summary of App\Util\str_date
 * @param string $date date en format brute chiffre
 * @param mixed $lang langage de format
 * @param mixed $format format de la date à obtenir
 * @throws RuntimeException exception brute
 * @return string
 */

function str_date(string $date = '', ?string $lang = null, ?string $format = 'd m Y'):string {
 return  (new DateManager($date,$lang,$format))->get();
}
/**
 * Summary of ng
 * @param string $key
 * @param mixed $default
 * @return string
 */

function ng(string $key,mixed $default = null):mixed{
   return NgManager::getInstance()->getParam($key,$default);
}

function ngAll(){
   return NgManager::getInstance()->all();
}


// utils/CLI.php ou directement dans noga.php
function ask(string $question, ?string $default = null): string
{
    $prompt = $question;
    if ($default !== null) {
        $prompt .= " [$default]";
    }
    $prompt .= ": ";

    echo $prompt;
    $input = trim(fgets(STDIN));
    return $input !== '' ? $input : ($default ?? '');
}

function secret(string $question): string
{
    if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
        // Windows simple fallback (affiche quand même, car readline n'est pas simple)
        echo $question . ": ";
        $input = trim(fgets(STDIN));
        return $input;
    } else {
        // Linux/macOS : cacher l’input
        echo $question . ": ";
        system('stty -echo');
        $input = trim(fgets(STDIN));
        system('stty echo');
        echo "\n";
        return $input;
    }
}

/**
 * 
 *CLI color text ....
 * @param string $text
 * @param string $color
 * @return string
 */
function color(mixed $text, string $color): string
{
    $colors = [
        'black'   => '0;30',
        'red'     => '0;31',
        'green'   => '0;32',
        'yellow'  => '0;33',
        'blue'    => '0;34',
        'purple'  => '0;35',
        'cyan'    => '0;36',
        'white'   => '1;37',
    ];

    $code = $colors[$color] ?? '0';
    return "\033[{$code}m{$text}\033[0m";
}

if(!function_exists('success')){
 function success(string $text){
    echo color($text,'green');
}
}

if(!function_exists('warning')){
function warning(string $text){
    echo color($text,'yellow');
}
}

if(!function_exists('error')){
function error(string $text){
    echo color($text,'red');
}
}