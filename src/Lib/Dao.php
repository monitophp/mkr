<?php
namespace MonitoMkr\Lib;

use MonitoLib\App;

class Dao
{
    const VERSION = '1.0.0';
    /**
     * 1.0.0 - 2020-10-01
     * initial release
     */

    public function create(array $table, \stdClass $options)
    {
        $dbms           = $options->dbms;
        $connectionName = $options->connectionName;
        $namespace      = $options->namespace;

        $f = "<?php\n"
            . "namespace $namespace\\Dao;\n"
            . "\n"
            . "class {$table['class']} extends \\MonitoLib\\Database\\Dao\\$dbms\n"
            . "{\n"
            . "    const VERSION = '1.0.0';\n"
            . "    /**\n"
            . "     * 1.0.0 - " . date('Y-m-d') . "\n"
            . "     * initial release\n"
            . "     *\n"
            . '     * ' . __CLASS__ . ' v' . self::VERSION . ' ' . App::now() . "\n"
            . "     */\n";

        if (!is_null($connectionName)) {
            $f .= "\n"
                . "    public function __construct()\n"
                . "    {\n"
                . "        \\MonitoLib\Database\Connector::setConnectionName('$connectionName');\n"
                . "        parent::__construct();\n"
                . "    }\n";
        }

        $f .= '}';

        return $f;
    }
}
