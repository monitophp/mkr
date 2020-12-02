<?php
namespace MonitoMkr\Lib;

class Database
{
    const VERSION = '1.0.0';
    /**
     * 1.0.0 - 2020-10-01
     * initial release
     */

    protected $connection;

    public function __construct($connection = null)
    {
        $this->connection = $connection;
    }

    public function columnDefaults()
    {
        return [
            'name'       => null,
            'object'     => null,
            'type'       => 'string',
            'format'     => null,
            'label'      => null,
            'default'    => null,
            'maxLength'  => null,
            'precision'  => null,
            'scale'      => null,
            'collation'  => null,
            'charset'    => null,
            'primary'    => false,
            'required'   => false,
            'binary'     => false,
            'unsigned'   => false,
            'unique'     => false,
            'zerofilled' => false,
            'auto'       => false,
            'source'     => null,
            'foreign'    => false,
            'active'     => true,
        ];
    }
    public function labelIt($label)
    {
        if ($label ==  'id') {
            $label = '#';
        } else {
            $frag = NULL;

            if (preg_match('/_id$/', $label)) {
                //$frag  = '# ';
                $label = substr($label, 0, -3);
            }

            $parts = explode('_', $label);
            $label = '';

            foreach ($parts as $p) {
                $label .= ucfirst($p) . ' ';
            }

            $label = substr($label, 0, -1);
        }

        return $label;
    }
    public function table($table)
    {
        $tableName    = $table['TABLE_NAME'];
        $tableType    = $table['TABLE_TYPE'] === 'view' ? 'view' : 'table';
        $tableAlias   = $tableName;
        $tablePrefix  = null;
        $className    = '';
        $singularName = '';
        $pluralName   = '';

        $frag = explode('_', $table['TABLE_NAME']);

        if (preg_match('/^([a-z]{3})_/', $tableName, $m)) {
            $tablePrefix = isset($m[1]) ? $m[1] : null;
        }

        $i = 0;

        foreach ($frag as $f) {
            $className    .= $this->toSingular(ucfirst($f));

            if ($i !== 0 || ($i === 0 && $frag[0] !== $tablePrefix)) {
                $singularName .= self::toSingular(ucfirst($f)) . ' ';
                $pluralName   .= self::toPlural(ucfirst($f)) . ' ';
            }

            $i++;
        }

        $objectName = strtolower(substr($className, 0, 1)) . substr($className, 1);

        $tableDto = [];
        $tableDto['database'] = $table['TABLE_SCHEMA'];
        $tableDto['name']     = $tableName;
        $tableDto['type']     = $tableType;
        $tableDto['alias']    = $tableAlias;
        $tableDto['prefix']   = $tablePrefix;
        $tableDto['class']    = $className;
        $tableDto['object']   = $objectName;
        $tableDto['singular'] = $singularName;
        $tableDto['plural']   = $pluralName;

        return $tableDto;
    }
    public function toLowerCamelCase($string)
    {
        $frag  = explode('_', strtolower($string));
        $count = count($frag);
        $newString = $frag[0];
        
        for ($i = 1; $i < $count; $i++) {
            $newString .= ucfirst($frag[$i]);
        }
        
        return $newString;
    }
    public function toPlural($string)
    {
        return $string;
    }
    public function toSingular($string)
    {
        if (in_array(strtolower($string), ['status', 'tokens', 'wms'])) {
            return $string;
        }
        if (preg_match('/ens$/', $string)) {
            $string = substr($string, 0, -3) . 'em';
        }
        if (preg_match('/oes$/', $string)) {
            $string = substr($string, 0, -3) . 'ao';
        }
        if (preg_match('/ais$/', $string)) {
            $string = substr($string, 0, -3) . 'al';
        }
        if (preg_match('/res$/', $string)) {
            $string = substr($string, 0, -2);
        }
        if (preg_match('/tchs$/', $string)) {
            $string = substr($string, 0, -1);
        }
        if (preg_match('/[adeiouglmnprt]s$/', $string)) {
            $string = substr($string, 0, -1);
        }

        return $string;
    }
}