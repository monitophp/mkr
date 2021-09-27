<?php
namespace MonitoMkr\Lib;

use MonitoLib\App;
use MonitoLib\Functions;

class Model
{
    const VERSION = '1.0.0';
    /**
     * 1.0.0 - 2020-10-01
     * initial release
     */

    public function create(\MonitoLib\Database\Model\Table $table, ?\MonitoMkr\Type\Options $options)
    {
        // \MonitoLib\Dev::pre($table);
        $output = '';
        $keys   = '';

        $tablename = $table->getName();
        $classname = $table->getClass();
        $columns   = $table->getColumns();
        $namespace = $options->getNamespace();
        $dbms      = $options->getDbms();
        // $namespace      = $options->getNamespace();

        $columns     = $table->getColumns();
        $constraints = $table->getConstraints();

        $tableString = "    protected \$table = [\n"
            . "        'name' => '{$tablename}',\n"
            . "    ];\n";

        foreach ($columns as $column) {
            $object    = $column->getId();
            $name      = $column->getName();
            $source    = $column->getSource();
            $type      = $column->getType();
            $format    = $column->getFormat();
            $default   = $column->getDefault();
            $label     = $column->getLabel();
            $maxLength = $column->getMaxLength();
            $primary   = $column->getPrimary();
            $required  = $column->getRequired();
            $unsigned  = $column->getUnsigned();
            $auto      = $column->getAuto();

            $cl = strlen($object);
            $ci = $cl;//$bi + $cl;
            $it = floor($ci / 4);
            $is = $ci % 4;
            $li = "            ";//$util->indent($it, $is);

            $output .= "        '" . $object . "' => [\n";


            if ($object !== $name) {
                $output .= "$li'name'      => '{$name}',\n";
            }

            if ($auto) {
                $output .= "$li'auto'      => true,\n";

                if ($source !== 'auto') {
                    $output .= "$li'source'    => '{$source}',\n";
                }
            }

            // if ($column['getType']() == 'char') {
            //     if ($column['getCharset']() != $modelDefault->getDefaults('charset')) {
            //         $output .= "$li'charset'   => '{$column['getCharset']()}',\n";
            //     }
            //     if ($column['getCollation']() != $modelDefault->getDefaults('collation')) {
            //         $output .= "$li'collation' => '{$column['getCollation']()}',\n";
            //     }
            // }
            if ($type !== 'string') {
                // \MonitoLib\Dev::vd($type);

                if (!class_exists($type)) {
                    $type = "'{$type}'";
                }

                $output .= "$li'type'      => {$type},\n";
            }
            if (!is_null($format)) {
                $output .= "$li'format'    => '{$format}',\n";
            }
            if (!is_null($default)) {
                $output .= "$li'default'   => '{$default}',\n";
            }
            if (!is_null($label) && ($label !== '' || $label !== $name)) {
                // $output .= "$li'label'     => '{$label}',\n";
            }
            if (!is_null($maxLength) && $maxLength > 0) {
                $output .= "$li'maxLength' => {$maxLength},\n";
            }
            if ($primary) {
                $keys .= "'" . $name . "',";
                $output .= "$li'primary'   => true,\n";
            }
            if ($required) {
                $output .= "$li'required'  => true,\n";
            }
            // if ($modelDefault->getDefaults('type')) {
            // if ($modelDefault->getDefaults('type') != $column['datatype']) {
            // }
            // if ($modelDefault->getDefaults('unique') != $column['getIsUnique']()) {
            //     $output .= "$li'unique' => {$column['getIsUnique']()},\n";
            // }

            if (in_array($type, ['int', 'double']) && $unsigned) {
                $output .= "$li'unsigned'  => true,\n";
            }

            if ($dbms === 'Oracle') {
                if (in_array($name, [
                    'dtalt',
                    'dtinc',
                    'usralt',
                    'usrinc',
                ])) {
                    $output .= "$li'auto'      => true,\n";

                    switch ($name) {
                        case 'dtalt':
                            $source = 'UPDATE.now';
                            break;
                        case 'dtinc':
                            $source = 'INSERT.now';
                            break;
                        case 'usralt':
                            $source = 'UPDATE.userId';
                            break;
                        case 'usrinc':
                            $source = 'INSERT.userId';
                            break;
                        default:
                            $source = null;
                    }

                    $output .= "$li'source'    => '$source',\n";
                }
            }


        //'maxValue'         => 0,
        //'minValue'         => 0,
        //'numericPrecision' => null,
        //'numericScale'     => null,

            $output .= "        ],\n";
        }

        $keys = substr($keys, 0, -1);



        $keysString = '';

        if (!empty($keys)) {
            $keysString = "    protected \$keys = [$keys];\n"
                . "\n";
        }

        $fs = "<?php\n"
            . "namespace $namespace\\Model;\n"
            . "\n"
            . "class {$classname} extends \\MonitoLib\\Database\\Model\n"
            . "{\n"
            . "    const VERSION = '1.0.0';\n"
            . "    /**\n"
            . "     * 1.0.0 - " . date('Y-m-d') . "\n"
            . "     * initial release\n"
            . "     *\n"
            . '     * ' . __CLASS__ . ' v' . self::VERSION . ' ' . App::now() . "\n"
            . "     */\n"
            . "\n"
            . $tableString
            . "\n"
            . "    protected \$columns = [\n"
            . $output
            . "    ];\n"
            . "\n"
            . $keysString
            . $this->renderConstraints($constraints)
            . "}"
            ;
        return $fs;
    }
    private function renderConstraints(array $constraints) : string
    {
        $constraintString = '';

        // Constraints
        if (!empty($constraints)) {
            // \MonitoLib\Dev::pre($table['constraints']);
            // foreach ($table['constraints'] as $constraint) {

            // }
            $constraintString =  "    protected \$constraints = [\n";

            $gambiarraTemporaria = 0;

            $currentType = '';
            $currentName = '';

            $closeType = "        ],\n";
            $closeName = "            ],\n";

            foreach ($constraints as $constraint) {
                $name               = $constraint->getName();
                $type               = $constraint->getType();
                $database           = $constraint->getDatabase();
                $table              = $constraint->getTable();
                $column             = $constraint->getColumn();
                $position           = $constraint->getPosition();
                $referencedDatabase = $constraint->getReferencedDatabase();
                $referencedTable    = $constraint->getReferencedTable();
                $referencedColumn   = $constraint->getReferencedColumn();
                $referencedObject   = $constraint->getReferencedObject();

                if ($type !== $currentType) {
                    if ($currentType !== '') {
                        $constraintString .= $closeName
                            . $closeType;
                    }

                    $constraintString .= "        '$type' => [\n";
                    $currentType = $type;
                    $currentName = '';
                }

                if ($name !== $currentName) {
                    if ($currentName !== '') {
                        $constraintString .= $closeName;
                    }

                    $constraintString .= "            '$name' => [\n";
                    $currentName = $name;
                }

                $constraintString .= "                '" . Functions::toLowerCamelCase($column) . "',\n";
                // }

                // if ($type === 'U') {
                //     // $key = key($cv);
                //     // foreach ($cv->$key as $ck => $c) {
                //         // \MonitoLib\Dev::pre($c);
                //     // }

                //     $gambiarraTemporaria++;
                // }
            }

            // $constraintString .=  "    ];\n";

            // if ($gambiarraTemporaria === 0) {
            //     $constraintString = '';
            // }
        }

        if ($constraintString !== '') {
            $constraintString .= $closeName
                . $closeType
                . "    ];\n";
        }

        return $constraintString;
    }
}
