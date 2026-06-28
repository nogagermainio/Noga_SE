<?php
namespace Src\Router;

class Definition{

    public function __construct(
        public string $type,
        public array $execute = [],
        public bool $runtime = false
    ){}
}