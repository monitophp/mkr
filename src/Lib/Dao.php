<?php
namespace MonitoMkr\Lib;

use MonitoLib\App;

class Dao
{
    const VERSION = '1.0.0';
    /**
     * 1.0.0 - 2020-10-01
     * Initial release
     */

    public function create(\MonitoLib\Database\Model\Table $table, ?\MonitoMkr\Type\Options $options)
    {
        $dbms       = $options->getDbms();
        $connection = $options->getConnection();
        $namespace  = $options->getNamespace();
        $classname  = $table->getClass();

        $cs = '';

        if (!is_null($connection)) {
            $cs = "\n"
                . "    protected \$connectionName = '{$connection}';";
        }

        $today   = App::today();
        $class   = __CLASS__;
        $version = self::VERSION;
        $now     = App::now();

        $fs = <<<PHP
<?php

namespace $namespace\\Dao;

class {$classname} extends \\MonitoLib\\Database\\$dbms\\Dao
{
    const VERSION = '1.0.0';
    /**
     * 1.0.0 - $today
     * Initial release
     *
     * $class v{$version} $now
     */
$cs
}
PHP;
        return $fs;
    }
}
