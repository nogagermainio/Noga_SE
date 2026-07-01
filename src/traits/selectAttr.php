<?php
namespace Noga\Traits;

use Noga\QueryBuilder\Builder;
use Noga\QueryBuilder\Select\Join\Join;
use Noga\QueryBuilder\Select\Union\Union;

trait SelectAttr{
     /**
     * Summary of table
     * @var string
     */
    protected string $table;

    /**
     * Summary of cols
     * @var array
     */
    protected array $cols = [];

    /**
     * Summary of from
     * @var string
     */
    protected string $from = "";

    /**
     * Summary of groups
     * @var array
     */
    protected array $groups = [];

    /**
     * Summary of order
     * @var string
     */
    protected string $order = "";
    /**
     * Summary of limit
     * @var int|null 
     */
    protected ?int $limit = null;

    /**
     * Summary of union
     * @var string
     */
    protected string $union = "";

    /**
     * Summary of join
     * @var string
     */
    protected string $join = "";

    /**
     * Summary of offset
     * @var int|null
     */
    protected ?int $offset = null;

    /**
     * Summary of cte
     * @var array
     */
    protected array $cte = [];

    /**
     * Summary of explain
     * @var string
     */
    protected string $explain = "";

    /**
     * Summary of distinct
     * @var bool
     */
    protected bool $distinct = false;

    /**
     * Summary of sql
     * @var string|null
     */
    protected ?string $sql                = null;
    /**
     * Summary of request
     * @var array
     */
    protected array $request              = [];

    /**
     * Summary of buildClause
     * @var Builder|null
     */
    protected ?Builder $buildClause = null;

    /**
     * Summary of buildJoin
     * @var Join|null
     */
    protected ?Join $buildJoin     = null;

    /**
     * Summary of buildUnion
     * @var Union|null
     */
    protected ?Union $buildUnion = null;
}