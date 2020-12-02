<?php
namespace MonitoMkr\Dao;

use \MonitoLib\Functions;

class Oracle extends \MonitoLib\Database\Dao\Oracle
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
                'type'         => 'string',
                'default'      => null,
                'maxLength'    => 0,
                'precision'    => null,
                'scale'        => null,
                'collation'    => null,
                'charset'      => null,
                'isPrimary'    => 0,
                'isRequired'   => 0,
                'isBinary'     => 0,
                'isUnsigned'   => 1,
                'isUnique'     => 0,
                'isZerofilled' => 0,
                'isAuto'       => 0,
                'isForeign'    => 0,
                'active'       => 1
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
    public function listColumns(?string $database, string $tableName, array $columns = [], bool $onlyRequired = false) : array
    {
        $sql = 'SELECT LOWER(cl.column_name) AS column_name, cl.data_type, cl.data_precision, cl.data_scale, cl.nullable, cl.column_id, '
            . 'cl.default_length, cl.data_default, cl.character_set_name, cl.char_length, ('
            . "SELECT CASE WHEN cc.constraint_name IS NULL THEN 'N' ELSE 'Y' END "
            . 'FROM user_cons_columns cc LEFT JOIN user_constraints ct ON ct.constraint_name = cc.constraint_name '
            . "WHERE cc.table_name = cl.table_name AND cc.column_name = cl.column_name AND ct.constraint_type = 'P') AS is_primary, "
            . "'N' AS is_foreign, 'N' AS is_unique, "
            . 'CASE WHEN sq.sequence_name IS NOT NULL THEN 1 ELSE 0 END AS auto, '
            . "CASE WHEN sq.sequence_name IS NOT NULL THEN 'SEQUENCE.' || sq.sequence_name END AS source "
            . 'FROM user_tab_columns cl '
            . "LEFT JOIN user_sequences sq ON sq.sequence_name = SUBSTR(UPPER('SEQ_' || cl.table_name || '_' || cl.column_name), 1, 30) "
            . "WHERE UPPER(cl.table_name) = UPPER('$tableName')";

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

        // \MonitoLib\Dev::ee($sql);

        $stt = $this->parse($sql);
        $exe = $this->execute($stt);

        $data = [];

        $database = new \MonitoMkr\Lib\Database();

        while ($r = oci_fetch_assoc($stt)) {
            $dataType = $r['DATA_TYPE'];
            $dataScale = $r['DATA_SCALE'];

            switch ($dataType) {
                case 'DATE': {
                    $type = 'date';
                    break;
                }
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

            $column               = [];
            $column['name']       = $r['COLUMN_NAME'];
            $column['object']     = Functions::toLowerCamelCase($column['name']);
            $column['type']       = $type;
            $column['format']     = null;
            $column['label']      = $database->labelIt($r['COLUMN_NAME']);
            $column['dataType']   = $r['DATA_TYPE'];
            $column['default']    = $defaultValue;
            $column['maxLength']  = is_null($r['CHAR_LENGTH']) ? $r['DATA_PRECISION'] : $r['CHAR_LENGTH'];
            $column['precision']  = $r['DATA_PRECISION'];
            $column['scale']      = $r['DATA_SCALE'];
            $column['collation']  = $r['CHARACTER_SET_NAME'];
            $column['charset']    = $r['CHARACTER_SET_NAME'];
            $column['primary']    = $r['IS_PRIMARY'] === 'Y' ? 1 : 0;
            $column['required']   = $r['NULLABLE'] === 'Y' ? 0 : 1;
            $column['binary']     = 0;
            $column['unsigned']   = 0;
            $column['unique']     = $r['IS_UNIQUE'] === 'Y' ? 1 : 0;
            $column['zerofilled'] = 0;
            $column['auto']       = $r['AUTO'];
            $column['source']     = $r['SOURCE'];
            $column['foreign']    = $r['IS_FOREIGN'] === 'Y' ? 1 : 0;
            $column['active']     = 1;

            if ($column['default'] == 'NULL' || ($column['default'] === '' && !$column['required'])) {
                $column['default'] = null;
            }

            if ($column['type'] === 'date') {
                $column['format'] = 'Y-m-d H:i:s';
            }

            $data[] = $column;
        }

        return $data;
    }
    public function listConstraints($database, $tableName, $columName = null)
    {
        $sql = 'SELECT c0.owner AS table_schema, c0.table_name, c0.constraint_name, c0.constraint_type, c1.column_name, c1.position AS ordinal_position, '
            . "'oracle' AS referenced_table_schema, c2.table_name AS referenced_table_name, c2.column_name AS referenced_column_name FROM ("
            . 'SELECT c.owner, c.table_name, c.constraint_name, c.constraint_type, c.r_owner, c.r_constraint_name '
            . "FROM user_constraints c WHERE c.constraint_type IN ('P','R','U')) c0 "
            . 'LEFT JOIN user_cons_columns c1 ON c0.owner = c1.owner AND c0.constraint_name = c1.constraint_name '
            . 'LEFT JOIN user_cons_columns c2 ON c0.r_owner = c2.owner AND c0.r_constraint_name = c2.constraint_name '
            . "WHERE UPPER(c0.table_name) = UPPER('{$tableName}') " . (is_null($columName) ? '' : "AND UPPER(c1.column_name) = UPPER('{$columName}') ")
            . 'ORDER BY c0.constraint_name, c1.position';
        // \MonitoLib\Dev::ee($sql);
        $stt = $this->connection->parse($sql);
        $exe = $this->connection->execute($stt);

        $data = [];

        while ($r = oci_fetch_assoc($stt)) {
            $constraint = new \stdClass;
            $constraint->tableSchema           = $r['TABLE_SCHEMA'];
            $constraint->tableName             = $r['TABLE_NAME'];
            $constraint->constraintName        = $r['CONSTRAINT_NAME'];
            $constraint->constraintType        = $r['CONSTRAINT_TYPE'] === 'R' ? 'F' : $r['CONSTRAINT_TYPE'];
            $constraint->columnName            = Functions::toLowerCamelCase($r['COLUMN_NAME']);
            $constraint->ordinalPosition       = $r['ORDINAL_POSITION'];
            $constraint->referencedTableSchema = $r['REFERENCED_TABLE_SCHEMA'];
            $constraint->referencedTableName   = $r['REFERENCED_TABLE_NAME'];
            $constraint->referencedColumnName  = Functions::toLowerCamelCase($r['REFERENCED_COLUMN_NAME']);
            $data[] = $constraint;
        }

        return $data;
    }
    public function listTables(?string $databaseName, array $tableName = [])
    {
        $sql = "SELECT 'oracle' AS table_schema, table_type, table_name FROM ("
            . "SELECT 'view' AS table_type, LOWER(view_name) AS table_name FROM user_views "
            . 'UNION ALL '
            . "SELECT 'table' AS table_type, LOWER(table_name) AS table_name FROM user_tables)";

        if (!empty($tableName)) {
            $sql .= " WHERE UPPER(table_name) IN (";

            foreach ($tableName as $table) {
                $sql .= "UPPER('$table'),";
            }

            $sql = substr($sql, 0, -1) . ')';
        }

        // \MonitoLib\Dev::ee($sql);

        // \MonitoLib\Dev::pre($this->getConnection());

        // $stt = oci_parse($this->connection, $sql);
        $stt = $this->parse($sql);
        $exe = $this->execute($stt);

        $data = [];

        $database = new \MonitoMkr\Lib\Database;

        while ($r = oci_fetch_assoc($stt)) {
            $data[] = $database->table($r);
        }

        // \MonitoLib\Dev::pre($data);

        return $data;
    }
}