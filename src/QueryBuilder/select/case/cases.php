<?php declare(strict_types=1);
namespace Noga\QueryBuilder\Select\Case;

use InvalidArgumentException;
use Noga\Core\BindHashing;

class Cases {
    protected string $name = "";
    protected array $whens = [];        // [[condition, value, paramName]]
    protected string|null $else = null; // valeur de l'ELSE
    protected string|null $alias = null;
    protected array $params = [];       // tableau final des binds
    protected int $counter = 0;
    protected int $caseCounter = 0;
    protected array $caseFromArray = [];


    /**
     * Ajoute une condition WHEN … THEN …
     * $condition = "col = :param" ou n'importe quelle condition valide SQL
     * $value = valeur à retourner si la condition est vraie
     * @param string $condition
     * @param string $then
     * @param mixed $bindvalue
     * @throws InvalidArgumentException
     * @return Cases
     */
    public function when(string $condition,string $then, ?string $bindvalue=""):Cases
    {
        if (str_contains($condition, '?')) {
            throw new InvalidArgumentException("Bind '?' interdit — utilisez :name");
        }
        
        // générer un nom unique pour la valeur THEN
        $paramThen = BindHashing::hash("then",$then). (++$this->counter);
        $this->whens[] = [$condition, $bindvalue, $paramThen,$then];

       
         $this->name = $this->matchCond($condition);
            if (!array_key_exists($this->name, $this->params) && !empty($bindvalue)) {
                $this->params[":$this->name"] = $bindvalue;
            }

        // ajouter THEN value
        $this->params[$paramThen] = $then;

        return $this;
    }

    private function matchCond(string $condition):string{
        $name = "";
         // extraire les :params dans la condition et les stocker dans $this->params
         preg_match_all('/:([a-zA-Z_]\w*)/', $condition, $matches);
        foreach ($matches[1] as $nm) {
           $name = $nm;
        }
        return $name;
    }

    /**
     * Définit la valeur ELSE
     * @param mixed $value
     * @return Cases
     */
    public function else(mixed $value):Cases
    {
        $clone = clone $this;
        $paramElse = BindHashing::hash("else",$value). (++$clone->counter);
        $clone->else = $paramElse;
        $clone->params[$paramElse] = $value;
        return $clone;
    }

    /**
     * Définit l'alias de la colonne
     * @param string|null $alias
     * @return Cases
     */
    public function as(?string $alias = null):Cases
    {
        $clone = clone $this;
        $clone->alias = ($alias !== null) ? $alias : "case_".$clone->counter++."";
        return $clone;
    }

/**
 * Summary of selectCaseFormArray
 * @param array{column:string,alias:string,else:string,case:array{cond:string,value:int|string,then:int|string}} $array
 * @throws InvalidArgumentException
 * @return Cases
 */
public function selectCaseFormArray(array $array):Cases {
    $clone = clone $this;
     
    foreach ($array as $block) {
        if (!isset($block['cases']) || !is_array($block['cases'])) {
            throw new InvalidArgumentException("Erreur : 'cases' doit être un tableau");
        }

        $column = $block['column'] ?? "";
        $alias  = $block['alias'] ?? "";
        $else   = $block['else'] ?? null;

        $whens = [];
        foreach ($block['cases'] as $case) {
            $cond  = $case['cond']  ?? "";
            $value = $case['value'] ?? null;
            $then  = $case['then']  ?? "";

            // $clone->when($cond, $value, $then);
            $whens[] = "WHEN $cond THEN '$then' ";
           $name = $clone->matchCond($cond);
            $clone->params[":$name"] = $value;
        }

        $clone->caseFromArray[] = " CASE {$column} ".implode(" \n",$whens)." ELSE '$else' END AS $alias ";

    }

    return $clone;
}

    /**
     * Retourne la colonne SQL et fusionne tous les params
     * Summary of toColumn
     * @param string $cols
     * @return array<array|string>
     */
    public function toColumn(string $cols = ""):array
    {
        $this->name = $cols;

        $sql = "CASE {$this->name}";

        foreach ($this->whens as [$cond, $_val, $paramThen,$then]) {
            $sql .= " WHEN {$cond} THEN {$paramThen} ";
        }

        if ($this->else !== null) {
            $sql .= " ELSE {$this->else} ";
        }

        $sql .= " END ";

        if ($this->alias) {
            $sql .= " AS {$this->alias} ";
        }else{
            $sql .= " AS ".($this->name ?: "case")."_".++$this->caseCounter;
        }

        if(!empty($this->caseFromArray)){
            $sql = implode(',',$this->caseFromArray);
        }

        return [$sql, $this->params];
    }
}
