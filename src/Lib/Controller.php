<?php
namespace MonitoMkr\Lib;

use MonitoLib\App;
use MonitoLib\Functions;

class Controller
{
    const VERSION = '1.0.0';
    /**
     * 1.0.0 - 2020-10-01
     * initial release
     */

    public function render(\MonitoLib\Database\Model\Table $table, ?\MonitoMkr\Type\Options $options)
    // public function create(array $table, \stdClass $options)
    {
        // \MonitoLib\Dev::pre($table);
        $primaryKeys = [];

        $tableName = $table->getName();
        $className = $table->getClass();
        $tableType = $table->getType();
        $columns = $table->getColumns();

        // foreach ($columns as $column) {
        //     if ($column['primary']) {
        //         $primaryKeys[$column['name']] = $column['object'];
        //     }
        // }

        // if ($tableType === 'table' && empty($primaryKeys)) {
        //     throw new \MonitoLib\Exception\BadRequest("Não existe chave primária na tabela {$tableName}!");
        // }

        // Payload métodos
        $payload = '';
        $equals  = '';

        $i = 0;

        foreach ($primaryKeys as $keyName => $keyObject) {
            if ($i > 0) {
                $equals .= '            ';
            }

            $payload .= '$' . $keyObject . ', ';
            $equals  .= "->equal('$keyName', \$$keyObject)\n";
            $i++;
        }

        $use = '';

        $payload = substr($payload, 0, -2);

        $objectName  = $table->getObject();
        $namespace   = $options->getNamespace();
        $objectDao   = $objectName . 'Dao';
        $objectDto   = $objectName . 'Dto';
        $objectModel = $objectName . 'Model';

        if ($options->controllerMethods) {
            $use = "use \\MonitoLib\\Exception\\NotFound;\n"
                . "use \\MonitoLib\\Request;\n"
                . "use \\MonitoLib\\Response;\n"
                . "\n";
        }

        $fs = "<?php\n"
            . "\n"
            . "namespace $namespace\\Controller;\n"
            . "\n"
            . $use
            . "class $className extends \\MonitoLib\\Controller\n"
            . "{\n"
            . "    const VERSION = '1.0.0';\n"
            . "    /**\n"
            . "     * 1.0.0 - " . date('Y-m-d') . "\n"
            . "     * Initial release\n"
            . "     *\n"
            . '     * ' . __CLASS__ . ' v' . self::VERSION . ' ' . App::now() . "\n"
            . "     */\n";

        if ($options->controllerMethods) {
            $fs .= "\n";

             if ($tableType === 'table') {
                $fs .= $this->create($namespace, $className, $objectDao, $objectDto)
                    . $this->delete();
            }

            $fs .= $this->get();

            if ($tableType === 'table') {
                $fs .= $this->update();
            }
        }

        $fs .= '}';

        return $fs;
    }
    private function create(string $namespace, string $className, string $objectDao, string $objectDto)
    {
        // Create function
        $ms = "    public function create(\$mix = null)\n"
            . "    {\n"
            . "        if (is_null(\$mix)) {\n"
            . "            \$json[] = Request::getJson();\n"
            . "        } else {\n"
            . "            if (is_array(\$mix)) {\n"
            . "                \$json = \$mix;\n"
            . "            } else {\n"
            . "                \$json[] = \$mix;\n"
            . "            }\n"
            . "        }\n"
            . "\n"
            . "        \$$objectDao = new \\{$namespace}\\Dao\\{$className};\n"
            . "\n"
            . "        foreach (\$json as \$j) {\n"
            . "            \$$objectDto = \$this->jsonToDto(new \\{$namespace}\\Dto\\{$className}, \$j);\n"
            . "            \${$objectDao}->insert(\$$objectDto);\n"
            . "        }\n"
            . "\n"
            . "        Response::setHttpResponseCode(201);\n"
            . "    }\n";
        return $ms;
    }
    private function delete()
    {
        // Delete function
        $ms = "    public function delete(...\$keys)\n"
            . "    {\n"
            . "        if (empty(\$keys)) {\n"
            . "            throw new BadRequest('Não é possível deletar sem parâmetros!');\n"
            . "        }\n"
            . "\n"
            . "        \$$objectDao   = new \\{$namespace}\\Dao\\{$className};\n"
            . "        \$$objectModel = new \\{$namespace}\\Model\\{$className};\n"
            . "\n"
            . "        \$primaryKeys = \${$objectModel}->getPrimaryKeys();\n"
            . "\n"
            . "        \$i = 0;\n"
            . "\n"
            . "        foreach (\$primaryKeys as \$field) {\n"
            . "            \${$objectDao}->equal(\$field, \$keys[\$i], \$$objectDao::FIXED);\n"
            . "            \$i++;\n"
            . "        }\n"
            . "\n"
            . "        \${$objectDao}->delete();\n"
            . "\n"
            . "        Response::setHttpResponseCode(204);\n"
            . "    }\n";
        return $ms;
    }
    private function get()
    {
        // Get method
        $ms = "    public function get(...\$keys)\n"
            . "    {\n"
            . "        \$$objectDao   = new \\{$namespace}\\Dao\\{$className};\n"
            . "        \$$objectModel = new \\{$namespace}\\Model\\{$className};\n"
            . "\n"
            . "        if (!empty(\$keys)) {\n"
            . "            \$primaryKeys = \${$objectModel}->getPrimaryKeys();\n"
            . "\n"
            . "            \$i = 0;\n"
            . "\n"
            . "            foreach (\$primaryKeys as \$field) {\n"
            . "                \${$objectDao}->equal(\$field, \$keys[\$i], \$$objectDao::FIXED);\n"
            . "                \$i++;\n"
            . "            }\n"
            . "        }\n"
            . "\n"
            . "        \$dataset = \$this->dataset ?? Request::asDataset();\n"
            . "        \$fields  = \$this->fields  ?? Request::getFields();\n"
            . "        \$orderBy = \$this->orderBy ?? Request::getOrderBy();\n"
            . "        \$page    = \$this->page    ?? Request::getPage();\n"
            . "        \$perPage = \$this->perPage ?? Request::getPerPage();\n"
            . "        \$query   = \$this->query   ?? Request::getQuery();\n"
            . "\n"
            . "        \${$objectDao}->setFields(\$fields)\n"
            . "            ->setQuery(\$query);\n"
            . "\n"
            . "        if (empty(\$keys)) {\n"
            . "            \${$objectDao}->setPerPage(\$perPage)\n"
            . "                ->setPage(\$page)\n"
            . "                ->setOrderBy(\$orderBy);\n"
            . "\n"
            . "            if (\$dataset) {\n"
            . "                return \${$objectDao}->dataset();\n"
            . "            } else {\n"
            . "                return \${$objectDao}->list();\n"
            . "            }\n"
            . "        } else {\n"
            . "            \$dto = \${$objectDao}->get();\n"
            . "\n"
            . "            if (is_null(\$dto)) {\n"
            . "                throw new NotFound(\$this->notFound ?? 'Registro não encontrado!');\n"
            . "            }\n"
            . "\n"
            . "            return \$dto;\n"
            . "        }\n"
            . "    }\n";
        return $ms;
    }
    private function update()
    {
        // Update method
        $ms = "    public function update($payload, \$mix = null)\n"
            . "    {\n"
            . "        \$json  = \$mix ?? Request::getJson();\n"
            . "        \$$objectDao   = new \\{$namespace}\\Dao\\{$className};\n"
            . "        \$$objectModel = new \\{$namespace}\\Model\\{$className};\n"
            . "\n"
            . "        \$$objectDto = \$$objectDao{$equals}"
            . "            ->get();\n"
            . "\n"
            . "        if (is_null(\$$objectDto)) {\n"
            . "            throw new NotFound(\$this->notFound ?? 'Registro não encontrado!');\n"
            . "        }\n"
            . "\n"
            . "        \$$objectDto = \$this->jsonToDto(\$$objectDto, \$json);\n"
            . "        \${$objectDao}->update(\$$objectDto);\n"
            . "\n"
            . "        Response::setHttpResponseCode(201);\n"
            . "    }\n";
        return $ms;
    }
}
