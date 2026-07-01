<?php
namespace Noga\Schema;
use Noga\Schema\Alter;

trait AlterTable{
    protected string $alter = "";
    protected array $newColumn = [];
    protected string $tableAlter = "";
    protected ?Alter $alterSchema = null;

    protected function alter(): Alter|null{
        if($this->alterSchema === null){
            $this->alterSchema = (new Alter($this->tableAlter));
        }
        return $this->alterSchema;
    }

    public function alterTable(string $table):Schema{
            $clone = clone $this;
        $clone->tableAlter = $table;

        return $clone;
    }

    public function getAltered(): array{
            return $this->newColumn;
    }


    public function add(
        string|array $column,
        string|array $type,
        bool $nullable = false,
        string|array $default = "",
        string $comment = ""):Schema{
         $clone = clone $this;
        $null = $nullable ? "NOT NULL":"NULL";

       $clone->newColumn[] = " ADD $column $type $null $default  $comment";

         return $clone;
    }

    public function addIndex(string $index,array|string $columns):Schema{
        $clone = clone $this;
        $clone->newColumn[] = $this->alter()
                    ->add_Index(
                        $index,
                        $columns
                    );
        return $clone;
    }

    public function addPrimary(string|array $primary_key):Schema{
        $clone =  clone $this;
        $clone->newColumn[] = $this->alter()
        ->add_Primary($primary_key);

        return $clone;
    }

    public function addUnique(string $index,string $columns):Schema{
        $clone = clone $this;
        $clone->newColumn[] = $this->alter()
            ->add_Unique($index,$columns);
        return $clone;
    }

    public function addFullText(string $index,string $column):Schema{
        $clone = clone $this;
        $clone->newColumn[] = $this->alter()
            ->add_FullText($index,$column);
           return $clone;
    }

    public function addSpatial(string $index,string $column):Schema{
        $clone = clone $this;
        $clone->newColumn[] = $this->alter()
            ->add_Spatial($index,$column);
        return $clone;

        }
     
     public function addForeign(string $cols,callable|Schema $callback):Schema{
        $clone = clone $this;
        $clone->newColumn[] = $this->alter()->add_Foreign(
            $cols,
        $callback);
        return $clone;
     }

    public function change(
        string|array $column,
        string|array $newColumn,
        string $type,
        bool $nullable = false):Schema{
            $clone = clone $this;
            $clone->newColumn[] = $this->alter()
            ->change_column(
                $column,
                $newColumn,
                $type,
                $nullable
            );

            return $clone;
    }

    public function modify(
        string $column,
        array|string $type,
        string $comment = ""):Schema{
            $clone = clone $this;
            $clone->newColumn[] = $this->alter()
            ->modify_column(
                $column,
                $type,
                $comment
            );
            return $clone;
    }

    public function setDefault(string $column,string $values):Schema{
        $clone = clone $this;
        $clone->newColumn[] = $this->alter()
            ->set_Default(
                $column,
                $values
            );
            
            return $clone;
    }

    public function drop(string $column):Schema{
        $clone = clone $this;
        $clone->newColumn[] = $this->alter()
            ->drop_column(
                $column
            );
            
        return $clone;
    }

    public function dropDefault(string $column):Schema{
        $clone = clone $this;
        $clone->newColumn[] = $this->alter()
        ->drop_Default($column);
        return $clone;
    }

    public function tableEngine(string $engine):Schema{
        $clone = clone $this;
        $clone->newColumn[] = $this->alter()
        ->table_Engine($engine);

        return $clone;
    }

    public function tableCharset(string $character):Schema{
        $clone = clone $this;
        $clone->newColumn[] = $this->alter()
            ->table_Charset($character);
            return $clone;
    }

    public function tableRename(string $new_name):Schema{
        $clone = clone $this;
        $clone->newColumn[] = $this->alter()
            ->table_Rename($new_name);
            return $clone;
    }

    public function tableComment(string $comment):Schema{
        $clone = clone $this;
        $clone->newColumn[] = $this->alter()
            ->table_Comment($comment);
            return $clone;
    }

    public function columnOrder(string $column,string $after):Schema{
        $clone = clone $this;
        $clone->newColumn[] = $this->alter()
        ->column_Order($column,$after);
        return $clone;
    }

    public function columnToFirst(string $column):Schema{
        $clone = clone $this;
        $clone->newColumn[] = $this->alter()
        ->column_To_First($column);
        return $clone;
    }

    public function columnToLast(string $column):Schema{
        $clone = clone $this;
        $clone->newColumn[] = $this->alter()
            ->column_To_Last($column);

            return $clone;
    }

    public function buildAlter(): string{
        $this->alter = "ALTER TABLE {$this->tableAlter} ";
        $this->alter .= !empty($this->newColumn) ? 
        implode(',',$this->newColumn) 
        : implode(',',$this->newColumn);

        return $this->alter;
    }



//     "ALTER TABLE nom_table
// ADD COLUMN nouvelle_colonne INT(11) NULL COMMENT 'Exemple'";

// ALTER TABLE `membres_msbc`
//  CHANGE `prenoms` `prenomsS` VARCHAR(255) 
//  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
// ALTER TABLE `membres_msbc` 
// CHANGE `id` `ids` INT(11) NOT NULL AUTO_INCREMENT, 
// CHANGE `identifiant` `identifiants` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
// CHANGE `noms` `nomss` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
// CHANGE `prenoms` `prenomss` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
// CHANGE `adhesion` `adhesions` INT(11) NULL DEFAULT NULL, 
// CHANGE `lieuNaissance` `lieuNaissances` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
// CHANGE `age` `ages` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
// CHANGE `cin` `cins` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
// CHANGE `delivrance` `delivrances` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
// CHANGE `duplicata` `duplicatas` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
// CHANGE `email` `emails` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
// CHANGE `profession` `professions` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
// CHANGE `adresse` `adresses` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
// CHANGE `arrondissement` `arrondissements` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
// CHANGE `telephone` `telephones` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
// CHANGE `famille` `familles` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
// CHANGE `origine` `origines` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
// CHANGE `district` `districts` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
// CHANGE `region` `regions` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
// CHANGE `genre` `genres` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
// CHANGE `IRMAR` `IRMARs` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0', 
// CHANGE `TGV` `TGVs` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0', 
// CHANGE `photo` `photos` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
// CHANGE `date` `dates` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
// CHANGE `categorie` `categories` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
// CHANGE `users` `userss` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
// CHANGE `titre` `titres` SET('titulaire') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'titulaire', 
// CHANGE `rattache` `rattaches` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
// CHANGE `action` `actions` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, 
// CHANGE `admin` `admins` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL, 
// CHANGE `adminId` `adminIds` INT(11) NULL DEFAULT NULL;
}