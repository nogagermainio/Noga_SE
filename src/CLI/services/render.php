<?php declare(strict_types=1);
namespace Src\CLI\Services;

use RuntimeException;
use Throwable;

class Render{
    private mixed $data = null;
    private string $colorCols = "yellow";
    private string $colorVals = "green";
    private int $space = 40;
    
    public function __construct(mixed $data = null,string $colorCols = 'yellow',string $colorVals = 'green',int $space = 40)
    {
       $this->data = $data;
       $this->colorCols = $colorCols;
       $this->colorVals = $colorVals;
       $this->space = $space;
       
    }

    public static function data(mixed $data,string $colorCols = 'yellow',string $colorVals = 'green',int $space = 40):static{
        return new static($data,$colorCols,$colorVals,$space);
    }

    /**
     * Summary of render
     * @throws RuntimeException
     * @return void
     */
    public function array():void{
        try{

            foreach($this->data as $k=>$v){

                if(\is_array($v)){

                        foreach($v as $c=>$t){
                             \printf("%-{$this->space}s => %s\n",
                    color($c,
                    $this->colorCols),
                    color(\is_array($t) ? \print_r($t) : $t,$this->colorVals));
                   
                        }
                         echo "=========================================\n";
                }else{  

                    \printf("%-{$this->space}s => %s\n",
                color($k,
                $this->colorCols),
                color(\is_array($v) ? \var_dump($v) : $v,$this->colorVals));

                }

                
        }

    }catch(Throwable $e){
       \var_dump($e);
       exit(0);
    }

    }

    function string():void{
        if(!\is_string($this->data)){
            throw new RuntimeException("data is not string !");
        }
        
        echo color($this->data,"green");
    }

    public function json(string $color = "green"){
        $data = (is_array($this->data)) ? $this->data : [$this->data];
        echo color(json_encode(
            $data,
            \JSON_PRETTY_PRINT
        ),$color);
    }

}