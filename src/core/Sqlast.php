<?php
namespace Noga\Core;

use RuntimeException;

class Sqlast
{
    private array $token = [];
    private string $buffer = '';
    private string $sql = '';
    private array $params = [];
    private string $driver = '';
    private array $node = [];
    private array $ast = [];
    private int $position = 0;

    private bool $inString = false;

    private array $specialChar = [',',';','(',')','{','}','!','=','\'','<','>','@','%','$','/','\\'];

    private array $comparator = ['=','!=','<=','>=','<','>'];

    private array $stringCover = ["'", '"', '`'];

    private array $keywordsSql = [
        'CREATE','ALTER','INSERT','DELETE',
        'WITH','RECURSIVE','SELECT','FROM',
        'JOIN','LEFT','INNER','RIGHT','CROSS','ON',
        'AS','UNION','ALL',
        'WHERE','HAVING','GROUP','BY',
        'ORDER','LIMIT','OFFSET','AND','IN',
        'NOT','IS','EXISTS','NULL','BETWEEN'
    ];

    private array $functionSql = [
        'MAX','MIN','SUM','AVG','COUNT',
        'COALESCE','CONCAT','DATETIME','NOW'
    ];

    private array $operator = ['+','-','/'];

    public function __construct(string $driver = 'mysql')
    {
        $this->driver = $driver;
    }

    public static function driver(string $driver = 'mysql'): static
    {
        return new static($driver);
    }

    public function QueryString(string $request): static
    {
        $this->sql = $request;
        return $this;
    }

    private function flushBuffer(): void
    {
        if ($this->buffer !== '') {
            $this->token[] = $this->buffer;
            $this->buffer = '';
        }
    }

    private function isSpace(string $c): bool
    {
        return ctype_space($c);
    }

    private function isString(string $c): bool
    {
        return in_array($c, $this->stringCover, true);
    }

    private function isSpecialChar(string $c): bool
    {
        return in_array($c, $this->specialChar, true);
    }

    private function isComparator(string $c): bool
    {
        return in_array($c, $this->comparator, true);
    }

    private function splitString(): static
    {
        $i = 0;
        $len = strlen($this->sql);

        while ($i < $len) {
            $char = $this->sql[$i];
            $next = $this->sql[$i + 1] ?? '';
            $two  = $char . $next;

            if ($char === '-' && $next === '-') {
                while ($i < $len && $this->sql[$i] !== "\n") $i++;
                continue;
            }

            if ($char === '/' && $next === '*') {
                $i += 2;
                while ($i < $len) {
                    if ($this->sql[$i] === '*' && ($this->sql[$i+1] ?? '') === '/') {
                        $i += 2;
                        break;
                    }
                    $i++;
                }
                continue;
            }

            if ($this->inString) {
                if ($this->isString($char) && $this->isString($next)) {
                    $this->buffer .= $char;
                    $i += 2;
                    continue;
                }

                if ($this->isString($char)) {
                    $this->buffer .= $char;
                    $this->token[] = $this->buffer;
                    $this->buffer = '';
                    $this->inString = false;
                    $i++;
                    continue;
                }

                $this->buffer .= $char;
                $i++;
                continue;
            }

            if ($this->isString($char)) {
                $this->flushBuffer();
                $this->buffer = $char;
                $this->inString = true;
                $i++;
                continue;
            }

            if ($this->isSpace($char)) {
                $this->flushBuffer();
                $i++;
                continue;
            }

            if ($this->isComparator($two)) {
                $this->flushBuffer();
                $this->token[] = $two;
                $i += 2;
                continue;
            }

            if ($this->isSpecialChar($char)) {
                $this->flushBuffer();
                $this->token[] = $char;
                $i++;
                continue;
            }

            $this->buffer .= $char;
            $i++;
        }

        $this->flushBuffer();
        return $this;
    }

    public function parseToken(): static
    {
        $this->splitString();

        foreach ($this->token as $t) {

            $upper = strtoupper($t);

            if (in_array($upper, $this->keywordsSql, true)) {
                $this->node[] = ["TYPE"=>"KEYWORD","VALUE"=>$upper];
            }
            elseif (in_array($upper, $this->functionSql, true)) {
                $this->node[] = ["TYPE"=>"FUNCTION_NAME","VALUE"=>$upper];
            }
            elseif (in_array($t, $this->comparator, true)) {
                $this->node[] = ["TYPE"=>"COMPARATOR","VALUE"=>$t];
            }
            elseif(in_array($t, $this->operator, true)){
                  $this->node[] = ["TYPE"=>"OPERATOR","VALUE"=>$t];
            }elseif ($t === '*') {
                $this->node[] = ["TYPE"=>"STAR","VALUE"=>$t];
            }
            elseif ($t === ',') {
                $this->node[] = ["TYPE"=>"COMMA","VALUE"=>$t];
            }
            elseif ($t === '(') {
                $this->node[] = ["TYPE"=>"LPAREN","VALUE"=>$t];
            }
            elseif ($t === ')') {
                $this->node[] = ["TYPE"=>"RPAREN","VALUE"=>$t];
            }
            elseif (preg_match("/^(['\"]).*\\1$/", $t)) {
                $this->node[] = ["TYPE"=>"STRING","VALUE"=>$t];
            }
            elseif (preg_match("/^`.*`$/", $t)) {
                $this->node[] = ["TYPE"=>"QUOTED_IDENTIFIER","VALUE"=>$t];
            }
            elseif (is_numeric($t)) {
                $this->node[] = ["TYPE"=>"NUMBER","VALUE"=>$t];
            }
            else {
                $this->node[] = ["TYPE"=>"IDENTIFIER","VALUE"=>$t];
            }
        }

        return $this;
    }

    public function createAst(): array
    {
        if ($this->matchKeyword('SELECT')) {
            return $this->parseSelect();
        }

        return [];
    }

    private function parseSelect(): array
    {
        $this->ast = [
            "DRIVER"=>$this->driver,
            "TYPE"=>"SELECT",
            "COLUMNS"=>[],
            "FROM"=>null
        ];

        $this->next(); // SELECT

        while ($this->currentNode()) {

            if ($this->matchKeyword('FROM')) break;

            if ($this->currentNode()['TYPE'] === 'COMMA') {
                $this->next();
                continue;
            }

            $this->ast["COLUMNS"][] = $this->parseColumn();
        }

        if ($this->matchKeyword('FROM')) {
            $this->next();

            if ($this->currentNode() &&
                in_array($this->currentNode()['TYPE'], ['IDENTIFIER','QUOTED_IDENTIFIER'], true)
            ) {
                $this->ast["FROM"] = $this->currentNode()['VALUE'];
                $this->next();
            }
        }

        return $this->ast;
    }

    private function parseColumn()
    {
        $token = $this->currentNode();

        if ($token['TYPE'] === 'FUNCTION_NAME') {
            return $this->parseFunction();
        }

        if ($token['TYPE'] === 'IDENTIFIER') {
            return $this->parseIdentifier();
        }

        if ($token['TYPE'] === 'STAR') {
            $this->next();
            return ["TYPE"=>"WILDCARD"];
        }

        throw new RuntimeException("Column expected");
    }

    private function parseIdentifier(): array
    {
        $name = $this->currentNode()['VALUE'];
        $this->next();

        return [
            "TYPE"=>"COLUMN",
            "NAME"=>$name,
            "ALIAS"=>$this->parseAlias()
        ];
    }

    private function parseFunction(): array
    {
        $name = $this->currentNode()['VALUE'];
        $this->next();

        if ($this->currentNode()['TYPE'] !== 'LPAREN') {
            throw new RuntimeException("Expected (");
        }
        $this->next();

        $args = [];

        while ($this->currentNode() && $this->currentNode()['TYPE'] !== 'RPAREN') {

            if ($this->currentNode()['TYPE'] === 'COMMA') {
                $this->next();
                continue;
            }

            $args[] = $this->parseExpression();
        }

        $this->next(); // RPAREN

        return [
            "TYPE"=>"FUNCTION_CALL",
            "NAME"=>$name,
            "ARGS"=>$args,
            "ALIAS"=>$this->parseAlias()
        ];
    }

    private function parseExpression()
    {
        $left = $this->parsePrimary();

        while ($this->currentNode()
                && $this->currentNode()['TYPE'] === 'OPERATOR'
               ) {

                if($this->currentNode())

            $op = $this->currentNode()['VALUE'];

            $this->next();

            $right = $this->parsePrimary();

            $left = [
                "TYPE"=>"BINARY_EXPRESSION",
                "OPERATOR"=>$op,
                "LEFT"=>$left,
                "RIGHT"=>$right
            ];
        }

        return $left;
    }

  private function parsePrimary()
{
    $t = $this->currentNode();

    if (!$t) {
        throw new RuntimeException("Unexpected EOF");
    }

    if ($t['TYPE'] === 'IDENTIFIER') {
        $this->next();
        return ["TYPE" => "COLUMN", "NAME" => $t['VALUE']];
    }

    if ($t['TYPE'] === 'NUMBER') {
        $this->next();
        return $t;
    }
    
    if($t['TYPE'] === 'STAR'){
        $this->next();
        return ["TYPE" => "WILDCARD", "VALUE" => $t['VALUE']];
    }
   

    if($t['TYPE'] === 'OPERATOR'){
        $this->next();
        return ["TYPE" => "OPERATOR", "VALUE" => $t];
    }

    throw new RuntimeException("Invalid expression: " . $t['TYPE']);
}

    private function parseAlias()
    {
        if (!$this->matchKeyword('AS')) return null;

        $this->next();

        if ($this->currentNode()['TYPE'] !== 'IDENTIFIER') {
            throw new RuntimeException("Alias expected");
        }

        $a = $this->currentNode()['VALUE'];
        $this->next();

        return $a;
    }

    private function currentNode(): ?array
    {
        return $this->node[$this->position] ?? null;
    }

    private function next(): ?array
    {
        $this->position++;
        return $this->currentNode();
    }

    private function matchKeyword(string $k): bool
    {
        $c = $this->currentNode();

        return $c
            && $c['TYPE'] === 'KEYWORD'
            && strtoupper($c['VALUE']) === strtoupper($k);
    }

    public function toNode(): array { return $this->node; }
    public function toToken(): array { return $this->token; }
}