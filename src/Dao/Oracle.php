<?php
namespace MonitoMkr\Dao;

use \MonitoLib\Functions;

class Oracle extends \MonitoLib\Database\Oracle\Dao
{
    const VERSION = '1.0.0';
    /**
     * 1.0.0 - 2020-10-01
     * initial release
     */

    public function listColumns(?string $database, string $tableName, array $columns = [], bool $onlyRequired = false) : array
    {
        $sql = <<<SQL
SELECT
    LOWER(cl.column_name) AS column_name,
    cl.data_type,
    cl.data_precision,
    cl.data_scale,
    cl.nullable,
    cl.column_id,
    cl.default_length,
    cl.data_default,
    cl.character_set_name,
    cl.char_length,
    (
        SELECT
            CASE WHEN cc.constraint_name IS NULL THEN 'N' ELSE 'Y' END
        FROM user_cons_columns cc
        LEFT JOIN user_constraints ct ON ct.constraint_name = cc.constraint_name
        WHERE cc.table_name = cl.table_name AND cc.column_name = cl.column_name AND ct.constraint_type = 'P'
    ) AS is_primary,
    'N' AS is_foreign,
    'N' AS is_unique,
CASE WHEN sq.sequence_name IS NOT NULL THEN 1 ELSE 0 END AS auto,
CASE WHEN sq.sequence_name IS NOT NULL THEN 'SEQUENCE.' || sq.sequence_name END AS source
FROM user_tab_columns cl
LEFT JOIN user_sequences sq ON sq.sequence_name = SUBSTR(UPPER('SEQ_' || cl.table_name || '_' || cl.column_name), 1, 30)
WHERE UPPER(cl.table_name) = UPPER('$tableName')
SQL;

        if ($onlyRequired) {
            $sql .= " AND cl.nullable = 'N'";
        } else {
            if (!empty($columns)) {
                $sql .= "AND (cl.nullable = 'N' OR LOWER(cl.column_name) IN (";

                foreach ($columns as $column) {
                    $sql .= "LOWER('$column'),";
                }

                $sql = substr($sql, 0, -1) . '))';
            }
        }

        $sql .= ' ORDER BY cl.column_id';

        $stt = $this->parse($sql);
        $this->execute($stt);

        $data = [];

        $database = new \MonitoMkr\Lib\Database();

        while ($r = oci_fetch_assoc($stt)) {
            $dataType = $r['DATA_TYPE'];
            $dataScale = $r['DATA_SCALE'];

            switch ($dataType) {
                case 'DATE':
                    $type = 'date';
                    break;
                case 'NUMBER':
                    $type = 'int';

                    if ($dataScale > 0) {
                        $type = 'double';
                    }

                    break;
                default:
                    $type = 'string';
            }

            $defaultValue = trim(trim(trim($r['DATA_DEFAULT']), "'"));
            $defaultValue = $defaultValue === '' ? null : $defaultValue;

            $name       = $r['COLUMN_NAME'];
            $id         = Functions::toLowerCamelCase($name);
            $type       = $type;
            $format     = null;
            $label      = $database->labelIt($r['COLUMN_NAME']);
            $dataType   = $r['DATA_TYPE'];
            $default    = $defaultValue;
            $maxLength  = is_null($r['CHAR_LENGTH']) ? $r['DATA_PRECISION'] : $r['CHAR_LENGTH'];
            $precision  = $r['DATA_PRECISION'] ?? 0;
            $scale      = $r['DATA_SCALE'] ?? 0;
            $collation  = $r['CHARACTER_SET_NAME'];
            $charset    = $r['CHARACTER_SET_NAME'];
            $primary    = $r['IS_PRIMARY'] === 'Y' ? 1 : 0;
            $required   = $r['NULLABLE'] === 'Y' ? 0 : 1;
            $binary     = 0;
            $unsigned   = 0;
            $unique     = $r['IS_UNIQUE'] === 'Y' ? 1 : 0;
            $zerofilled = 0;
            $auto       = $r['AUTO'];
            $source     = $r['SOURCE'];
            $foreign    = $r['IS_FOREIGN'] === 'Y' ? 1 : 0;
            $active     = 1;

            if ($default == 'NULL' || ($default === '' && !$required)) {
                $default = null;
            }

            if ($type === 'date') {
                $format = 'Y-m-d H:i:s';
            }

            $column = new \MonitoLib\Database\Model\Column();
            // $field = new \MonitoMkr\Dto\Column();
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

        return $data;
    }
    public function listConstraints($database, $tableName, $columName = null)
    {
        $andColumnName = is_null($columName) ? '' : "AND UPPER(c1.column_name) = UPPER('{$columName}') ";

        $sql = <<<SQL
SELECT
    c0.owner AS table_schema,
    c0.table_name,
    c0.constraint_name,
    c0.constraint_type,
    c1.column_name,
    c1.position AS ordinal_position,
    'oracle' AS referenced_table_schema,
    c2.table_name AS referenced_table_name,
    c2.column_name AS referenced_column_name
FROM (
    SELECT
        c.owner,
        c.table_name,
        c.constraint_name,
        c.constraint_type,
        c.r_owner,
        c.r_constraint_name
    FROM user_constraints c
    WHERE c.constraint_type IN ('P','R','U')
) c0
LEFT JOIN user_cons_columns c1 ON c0.owner = c1.owner AND c0.constraint_name = c1.constraint_name
LEFT JOIN user_cons_columns c2 ON c0.r_owner = c2.owner AND c0.r_constraint_name = c2.constraint_name
WHERE UPPER(c0.table_name) = UPPER('$tableName') $andColumnName
ORDER BY c0.constraint_name, c1.position
SQL;

        $stt = $this->parse($sql);
        $this->execute($stt);

        $data = [];

        while ($r = oci_fetch_assoc($stt)) {
            $database           = $r['TABLE_SCHEMA'];
            $table              = $r['TABLE_NAME'];
            $name               = $r['CONSTRAINT_NAME'];
            $type               = $r['CONSTRAINT_TYPE'] === 'R' ? 'F' : $r['CONSTRAINT_TYPE'];
            $column             = Functions::toLowerCamelCase($r['COLUMN_NAME']);
            $position           = $r['ORDINAL_POSITION'];
            $referencedDatabase = $r['REFERENCED_TABLE_SCHEMA'];
            $referencedTable    = $r['REFERENCED_TABLE_NAME'];
            $referencedColumn   = Functions::toLowerCamelCase($r['REFERENCED_COLUMN_NAME']);
            $referencedObject   = Functions::toLowerCamelCase($r['referenced_column_name']);

            $constraint = new \MonitoMkr\Dto\Constraint();
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
    public function listTables(?string $databaseName, array $tableName = [])
    {
        $sql = <<<SQL
SELECT
    'oracle' AS table_schema,
    table_type,
    table_name
FROM (
    SELECT
        'view' AS table_type,
        LOWER(view_name) AS table_name
    FROM user_views
    UNION ALL
    SELECT
        'table' AS table_type,
        LOWER(table_name) AS table_name
    FROM user_tables
)
SQL;

        if (!empty($tableName)) {
            $sql .= " WHERE UPPER(table_name) IN (";

            foreach ($tableName as $table) {
                $sql .= "UPPER('$table'),";
            }

            $sql = substr($sql, 0, -1) . ')';
        }

        $stt = $this->parse($sql);
        $this->execute($stt);

        $data = [];

        $database = new \MonitoMkr\Lib\Database;

        while ($r = oci_fetch_assoc($stt)) {
            $name     = $r['TABLE_NAME'];
            $type     = $r['TABLE_TYPE'] === 'table' ? 'table' : 'view';
            $alias    = $tableName;
            $prefix   = null;
            $object   = Functions::toLowerCamelCase($name);
            $class    = ucfirst($object);
            $singular = '';
            $plural   = '';

            // $frag = explode('_', $r['TABLE_NAME']);

            // if (preg_match('/^([a-z]{3})_/', $tableName, $m)) {
            //     $tablePrefix = isset($m[1]) ? $m[1] : null;
            // }

            // $i = 0;

            // foreach ($frag as $f) {
            //     $className    .= $this->toSingular(ucfirst($f));

            //     if ($i !== 0 || ($i === 0 && $frag[0] !== $tablePrefix)) {
            //         $singularName .= self::toSingular(ucfirst($f)) . ' ';
            //         $pluralName   .= self::toPlural(ucfirst($f)) . ' ';
            //     }

            //     $i++;
            // }

            $table = new \MonitoLib\Database\Model\Table();
            // $table = new \MonitoMkr\Dto\Table();
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

        return $data;
    }
}