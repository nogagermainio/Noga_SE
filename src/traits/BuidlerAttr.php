<?php
namespace Src\Traits;

use Src\QueryBuilder\Select\Select;

trait BuidlerAttr{

    /**
     * Summary of table
     * @var string
     */
    protected string $table   = '';

    /**
     * Summary of cols
     * @var array
     */
    private array $cols       = [];

    /**
     * Summary of sql
     * @var Select|null
     */
    private ?Select $sql      = null;

    /**
     * Summary of params
     * @var array
     */
    private array $params     = [];

    /**
     * Summary of conditions
     * @var array
     */
    private array $conditions = [];

    /**
     * Summary of groups
     * @var array
     */
    private array $groups     = [];

    /**
     * Summary of order
     * @var string
     */
    private string $order     = '';

    /**
     * Summary of limit
     * @var int|null
     */
    private ?int $limit       = null;

    /**
     * Summary of offset
     * @var int|null
     */
    private ?int $offset      = null;

    /**
     * Summary of having
     * @var array
     */
    private array $having     = [];

    /**
     * Summary of cte
     * @var array
     */
    private array $cte        = [];

    /**
     * Summary of joins
     * @var array
     */
    private array $joins      = [];

    /**
     * Summary of union
     * @var array
     */
    private array $union      = [];
}