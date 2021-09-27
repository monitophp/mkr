<?php
namespace MonitoMkr\Dao;

use \MonitoLib\Functions;

class MySQL extends \MonitoLib\Database\MySQL\Dao
{
    const VERSION = '1.0.0';
    /**
     * 1.0.0 - 2020-10-01
     * initial release
     */

    public function getDefaults()
    {
        return [
            'table' => [
                'type' => 'table'
            ],
            'column' => [
                'type'       => 'string',
                'format'     => null,
                'default'    => null,
                'maxLength'  => 0,
                'precision'  => null,
                'scale'      => null,
                'collation'  => 'utf8_general_ci',
                'charset'    => 'utf8',
                'primary'    => 0,
                'required'   => 0,
                'binary'     => 0,
                'unsigned'   => 1,
                'unique'     => 0,
                'zerofilled' => 0,
                'auto'       => 0,
                'source'     => null,
                'foreign'    => 0,
                'active'     => 1
            ],
            'exceptions' => [
                'column' => [
                    'name' => [
                        'id' => [
                            'type' => 'int'
                        ],
                        'dtalt' => [
                            'format'  => 'Y-m-d H:i:s',
                            'default' => '{CURRENT_DATETIME}'
                        ],
                        'dtinc' => [
                            'format'  => 'Y-m-d H:i:s',
                            'default' => '{CURRENT_DATETIME}'
                        ],
                        'usralt' => [
                            'default' => '{CURRENT_USER}'
                        ],
                        'usrinc' => [
                            'default' => '{CURRENT_USER}'
                        ]
                    ],
                    'type' => [
                        'date' => [
                            'format' => 'Y-m-d'
                        ]
                    ]
                ]
            ],
        ];
    }
    public function listColumns(string $database, string $tableName, array $columns = [], bool $onlyRequired = false) : array
    {
        $sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$database' AND TABLE_NAME = '$tableName'";

        if ($onlyRequired) {
            $sql .= " AND IS_NULLABLE = 'NO'";
        } else {
            if (!empty($columns)) {
                $sql .= "AND (IS_NULLABLE = 'NO' OR LOWER(cl.column_name) IN (";

                foreach ($columns as $column) {
                    $sql .= "LOWER('$column'),";
                }

                $sql = substr($sql, 0, -1) . '))';
            }
        }

        $sql .= ' ORDER BY ordinal_position';

        // \MonitoLib\Dev::ee($sql);

        $sth = $this->parse($sql);
        $sth->execute();

        $columns = $sth->fetchAll(\PDO::FETCH_ASSOC);

        $data = [];

        $database = new \MonitoMkr\Lib\Database();

        // \MonitoLib\Dev::pre($columns);

        foreach ($columns as $c) {
            // \MonitoLib\Dev::pre($c);

            $dataType = $c['DATA_TYPE'];
            switch ($dataType) {
                // case 'char':
                //     $type = 'char';
                //     break;
                case 'int':
                case 'bigint':
                case 'smallint':
                case 'tinyint':
                    $type = 'int';
                    break;
                case 'decimal';
                case 'double';
                case 'float';
                    $type = 'float';
                    break;
                case 'datetime';
                case 'timestamp';
                    $type = '\MonitoLib\Type\DateTime::class';
                    break;
                default:
                    $type = 'string';
            }

            // \MonitoLib\Dev::vde($c['COLUMN_DEFAULT']);

            $name       = $c['COLUMN_NAME'];
            $id         = Functions::toLowerCamelCase($name);
            // $object     = Functions::toLowerCamelCase($column['name']);
            $type       = $type;
            $format     = null;
            $label      = $database->labelIt($c['COLUMN_NAME']);
            $dataType   = $c['DATA_TYPE'];
            $default    = $c['COLUMN_DEFAULT'] ?? $c['COLUMN_DEFAULT'];
            $maxLength  = is_null($c['CHARACTER_MAXIMUM_LENGTH']) ? $c['NUMERIC_PRECISION'] : $c['CHARACTER_MAXIMUM_LENGTH'];
            $precision  = $c['NUMERIC_PRECISION'];
            $scale      = $c['NUMERIC_SCALE'];
            $collation  = $c['COLLATION_NAME'];
            $charset    = $c['CHARACTER_SET_NAME'];
            $primary    = $c['COLUMN_KEY'] === 'PRI' ? 1 : 0;
            $required   = $c['IS_NULLABLE'] === 'YES' ? 0 : 1;
            $binary     = strpos($c['COLLATION_NAME'], '_bin') === false ? 0 : 1;
            $unsigned   = strpos($c['COLUMN_TYPE'], 'unsigned') === false ? 0 : 1;
            $unique     = $c['COLUMN_KEY'] === 'UNI' ? 1 : 0;
            $zerofilled = strpos($c['COLUMN_TYPE'], 'zerofill') === false ? 0 : 1;
            $auto       = $c['EXTRA'] === 'auto_increment' ? 1 : 0;
            $source     = 'auto';
            $foreign    = $c['COLUMN_KEY'] === 'MUL' ? 1 : 0;
            $active     = 1;
            // $tableName        = $c['TABLE_NAME'];

            if ($type === 'datetime') {
                $columnFormat = 'Y-m-d H:i:s';
            }

            if ($type === 'time') {
                $columnFormat = 'H:i:s';
            }


            $column = new \MonitoLib\Database\Model\Column();
            $column
                ->setId($id)
                ->setName($name)
                ->setAuto($auto)
                ->setSource($source)
                ->setType($type)
                ->setFormat($format)
                ->setCharset($charset)
                ->setCollation($collation)
                ->setDefault($default)
                ->setLabel($label)
                ->setMaxLength($maxLength)
                // ->setMinLength($minLength)
                // ->setMaxValue($maxValue)
                // ->setMinValue($minValue)
                ->setPrecision($precision)
                // ->setRestrict($restrict)
                ->setScale($scale)
                ->setPrimary($primary)
                ->setRequired($required)
                // ->setTransform($transform)
                ->setUnique($unique)
                ->setUnsigned($unsigned);
            $data[] = $column;
        }

        // \MonitoLib\Dev::pre($data);

        return $data;
    }
    public function listConstraints($database, $tableName, $columName = null)
    {
//  " . (is_null($columName) ? '' : "AND c.column_name = '{$columName}' "
        $sql = <<<SQL
SELECT
    t.table_schema,
    t.table_name,
    t.constraint_name,
    SUBSTR(t.constraint_type, 1, 1) AS constraint_type,
    c.column_name,
    c.ordinal_position,
    c.referenced_table_schema,
    c.referenced_table_name,
    c.referenced_column_name
FROM information_schema.table_constraints t
INNER JOIN information_schema.key_column_usage c ON t.table_schema = c.table_schema AND t.table_name = c.table_name AND t.constraint_name = c.constraint_name
WHERE SUBSTR(t.constraint_type, 1, 1) IN ('F', 'U') AND t.table_schema = '$database' AND t.table_name = '$tableName'
ORDER BY t.constraint_type, c.ordinal_position
SQL;
        // \MonitoLib\Dev::ee($sql);
        $stt = $this->parse($sql);
        $stt->execute();

        $data = [];

        while ($r = $stt->fetch(\PDO::FETCH_ASSOC)) {
            $database           = $r['table_schema'];
            $table              = $r['table_name'];
            $name               = $r['constraint_name'];
            $type               = $r['constraint_type'];
            $column             = $r['column_name'];
            $position           = $r['ordinal_position'];
            $referencedDatabase = $r['referenced_table_schema'];
            $referencedTable    = $r['referenced_table_name'];
            $referencedColumn   = $r['referenced_column_name'];
            $referencedObject   = Functions::toLowerCamelCase($referencedColumn);

            $constraint = new \MonitoLib\Database\Model\Constraint();
            $constraint
                ->setName($name)
                ->setType($type)
                ->setDatabase($database)
                ->setTable($table)
                ->setColumn($column)
                ->setPosition($position)
                ->setReferencedDatabase($referencedDatabase)
                ->setReferencedTable($referencedTable)
                ->setReferencedColumn($referencedColumn)
                ->setReferencedObject($referencedObject);
            $data[] = $constraint;
        }

        return $data;
    }
    public function listTables(string $databaseName, array $tableName = [], ?string $prefix)
    {
        $sql = "SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA <> 'information_schema' AND "
            . "TABLE_SCHEMA = '$databaseName'";

        if (!empty($tableName)) {
            $sql .= " AND UPPER(table_name) IN (";

            foreach ($tableName as $table) {
                $sql .= "UPPER('$table'),";
            }

            $sql = substr($sql, 0, -1) . ')';
        }

        // foreach ($databases as $databaseName => $tables) {

        //     if (count((array)$tables) > 0) {
        //         $sql .= " AND TABLE_NAME IN (";

        //         foreach ($tables as $tableName => $columns) {
        //             $sql .= "'$tableName',";
        //         }

        //         $sql = substr($sql, 0, -1) . ')';
        //     }

        //     $sql .= ' OR (';
        // }

        // $sql = substr($sql, 0, -5) . ')';

        // \MonitoLib\Dev::ee($sql);

        $stt = $this->parse($sql);
        $stt->execute();

        $data = [];

        $database = new \MonitoMkr\Lib\Database();

        while ($r = $stt->fetch(\PDO::FETCH_ASSOC)) {
            $prefix   ??= '';

            $name     = $r['TABLE_NAME'];
            $type     = $r['TABLE_TYPE'] === 'table' ? 'table' : 'view';
            $alias    = $tableName;
            $nopname  = preg_replace('/^' . $prefix . '/', '', $name);


            $parts = explode('_', $nopname);

            $object = '';

            foreach ($parts as $p) {
                $object .= ucfirst(Functions::toSingular($p));
            }

            // $object   = Functions::toLowerCamelCase($nopname);

            $class    = ucfirst($object);
            $singular = '';
            $plural   = '';

            $table = new \MonitoLib\Database\Model\Table();
            $table
                ->setDatabase($database)
                ->setName($name)
                ->setType($type)
                ->setAlias($alias)
                ->setPrefix($prefix)
                ->setClass($class)
                ->setObject($object)
                ->setSingular($singular)
                ->setPlural($plural);
            $data[] = $table;
        }

        // \MonitoLib\Dev::pre($data);

        return $data;
    }
}