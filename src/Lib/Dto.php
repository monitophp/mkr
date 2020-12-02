<?php
namespace MonitoMkr\Lib;

use MonitoLib\App;
use MonitoLib\Functions;

class Dto
{
    const VERSION = '1.0.0';
    /**
     * 1.0.0 - 2020-10-01
     * initial release
     */

    public function create(array $table, \stdClass $options)
    {
        $p = '';
        $g = '';
        $s = '';

        $namespace      = $options->namespace;

        foreach ($table['columns'] as $column) {
            $col = $column['object'];
            $get = 'get' . ucfirst($col);
            $set = 'set' . ucfirst($col);

            $p .= "    private \$$col;\n";
            $g .= "    /**\n"
                . "    * $get()\n"
                . "    *\n"
                . "    * @return \$$col\n"
                . "    */\n"
                . "    public function $get() {\n"
                . "        return \$this->$col;\n"
                . "    }\n";
            $s .= "    /**\n"
                . "    * $set()\n"
                . "    *\n"
                . "    * @return \$this\n"
                . "    */\n"
                . "    public function $set(\$$col) {\n"
                . "        \$this->$col = \$$col;\n"
                . "        return \$this;\n"
                . "    }\n";
        }

        $f = "<?php\n"
            . "namespace $namespace\\Dto;\n"
            . "\n"
            . "class {$table['class']}\n"
            . "{\n"
            . "    const VERSION = '1.0.0';\n"
            . "    /**\n"
            . "     * 1.0.0 - " . date('Y-m-d') . "\n"
            . "     * initial release\n"
            . "     *\n"
            . '     * ' . __CLASS__ . ' v' . self::VERSION . ' ' . App::now() . "\n"
            . "     */\n"
            . "\n"
            . $p
            . $g
            . $s
            . '}';
        return $f;
    }
}
