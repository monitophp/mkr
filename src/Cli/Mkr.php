<?php
namespace MonitoMkr\Cli;

use \MonitoLib\App;
use \MonitoLib\Database\Connector;
use \MonitoLib\Exception\BadRequest;
use \MonitoLib\Functions;
use \MonitoLib\Mcl\Request;

class Mkr extends \MonitoLib\Mcl\Controller
{
    const VERSION = '1.0.0';
    /**
     * 1.0.0 - 2020-10-01
     * initial release
     */

    private $baseUrl;
    private $columns = [];
    private $connectionName;
    private $controllerMethods = false;
    private $createController = false;
    private $createDao = false;
    private $createDto = false;
    private $createModel = false;
    private $createRoute = false;
    private $createTest = false;
    private $dbms;
    private $force = false;
    private $namespace;
    private $onlyRequired = false;
    private $tables = [];
    private $prefix;

    public function create()
    {
        $objects = Request::getParam('objects')->getValue();
        $baseUrl = Request::getOption('base-url')->getValue();
        $noRoute = Request::getOption('no-route')->getValue() ?? false;
        $noTest  = Request::getOption('no-test')->getValue() ?? false;

        $this->connectionName    = Request::getOption('connection-name')->getValue();
        $this->namespace         = Request::getOption('namespace')->getValue() ?? 'App';
        $this->controllerMethods = Request::getOption('controller-methods')->getValue() ?? false;
        $this->onlyRequired      = Request::getOption('only-required')->getValue() ?? false;
        $this->force             = Request::getOption('force')->getValue() ?? false;
        $this->prefix            = Request::getOption('prefix')->getValue() ?? '';

        // \MonitoLib\Dev::vde(Request::getOptions());






        if (!is_null($objects)) {
            $objects = explode(',', $objects);

            foreach ($objects as $object) {
                $on = 'create' . ucfirst($object);
                if (isset($this->$on)) {
                    $this->$on = true;
                }
            }
        } else {
            $this->createController = true;
            $this->createDao = true;
            $this->createDto = true;
            $this->createModel = true;
            $this->createRoute = true;
            $this->createTest = true;
        }

        if ($noRoute) {
            $this->createRoute = false;
        }

        if ($noTest) {
            $this->createTest = false;
        }

        $tables  = Request::getOption('tables')->getValue();
        $columns = Request::getOption('columns')->getValue();

        if (!is_null($tables)) {
            $this->tables = explode(',', $tables);
        }

        if (!is_null($columns)) {
            $this->columns = explode(',', $columns);
        }

        if (is_null($baseUrl)) {
            $this->baseUrl = '/' . strtolower(str_replace('\\', '/', $this->namespace)) . '/';
        } else {
            $this->baseUrl = str_replace('//', '/', $baseUrl . '/');
        }

        // Importa as tabelas do banco
        $tables = $this->importTables();

        // Gera os arquivos
        $this->generate($tables);
    }

    public function importTables() : array
    {
        // Define a conexão que será usada
        if (!is_null($this->connectionName)) {
            Connector::setConnectionName($this->connectionName);
        }

        $connection   = Connector::getConnection();
        $databaseName = $connection->getDatabase();
        $this->dbms   = $connection->getType();
        $class        = '\MonitoMkr\Dao\\' . $this->dbms;
        $database     = new $class($connection);

        $tableList = $database->listTables($databaseName, $this->tables, $this->prefix);

        if (empty($tableList)) {
            throw new BadRequest('Nenhuma tabela encontrada!');
        }

        $tableCount = count($tableList);

        // Conta as tabelas
        if ($tableCount > 10) {
            if (!$this->question("Foram listadas $tableCount tabelas. Deseja continuar (y/N)?", false)) {
                exit;
            }
        }

        $tables = [];

        // Lista as tabelas
        foreach ($tableList as $table) {
            $columnList = $database->listColumns($databaseName, $table->getName(), $this->columns, $this->onlyRequired);
            $table->setColumns($columnList);

            $constraintList = $database->listConstraints($databaseName, $table->getName());
            $table->setConstraints($constraintList);

            $tables[] = $table;
        }

        // \MonitoLib\Dev::ee('casa');

        // \MonitoLib\Dev::pre($tables);

        return $tables;
    }
    public function generate(array $tables)
    {
        // $path = App::getStoragePath('MonitoMkr/' . $options->namespace);
        $database   = new \MonitoMkr\Lib\Database();
        $controller = new \MonitoMkr\Lib\Controller();
        $dao        = new \MonitoMkr\Lib\Dao();
        $dto        = new \MonitoMkr\Lib\Dto();
        $model      = new \MonitoMkr\Lib\Model();
        $postman    = new \MonitoMkr\Lib\Postman();
        $route      = new \MonitoMkr\Lib\Route();

        $namespace = $this->namespace;

        $options = new \stdClass();
        $options->connectionName    = $this->connectionName;
        $options->controllerMethods = $this->controllerMethods;
        $options->dbms              = $this->dbms;
        $options->force             = $this->force;
        $options->namespace         = $this->namespace;
        $options->baseUrl           = $this->baseUrl;

        // Create options
        $options = new \MonitoMkr\Type\Options();
        $options
            // ->setForce($force)
            // ->setClassname($classname)
            ->setConnection($this->connectionName)
            ->setDbms($this->dbms)
            ->setNamespace($namespace);

        foreach ($tables as $table) {
            $objectName = $table->getObject();
            $className  = $table->getClass();
            $tableName  = $table->getName();

            // $table['url'] = $this->baseUrl . str_replace('_', '-', $tableName);

            echo "gerando tabela $namespace\\$tableName\n";

            $outpath = App::getDocumentRoot() . 'src/' . str_replace('\\', '/', $namespace) . App::DS;

            // Verifica se o controller será gerado
            if ($this->createController) {
                $file = App::createPath($outpath . 'Controller/') . $className . '.php';

                // Verifica se o arquivo já existe
                if (file_exists($file) && !$this->force) {
                    echo "Controller já existe\n";
                } else {
                    $f = $controller->render($table, $options);
                    file_put_contents($file, $f);
                    echo "Controller gerado\n";
                }
            }

            if ($this->createDao) {
                $file = App::createPath($outpath . 'Dao/') . $className . '.php';

                // Verifica se o arquivo já existe
                if (file_exists($file) && !$this->force) {
                    echo "Dao já existe\n";
                } else {
                    $string = $dao->create($table, $options);
                    file_put_contents($file, $string);
                    echo "Dao gerado\n";
                }
            }

            if ($this->createDto || $this->createModel) {
                $file   = App::createPath($outpath . 'Dto/') . $className . '.php';
                $string = $dto->create($table, $options);
                file_put_contents($file, $string);
                echo "Dto gerado\n";
            }

            if ($this->createDto || $this->createModel) {
                $file   = App::createPath($outpath . 'Model/') . $className . '.php';
                $string = $model->create($table, $options);
                // \MonitoLib\Dev::ee($string);
                file_put_contents($file, $string);
                echo "Model gerado\n";
            }

            // if ($this->createRoute) {
            //     $file   = App::getRoutesPath() . substr(str_replace('/', '.', str_replace('//', '/', $table['url'])), 1) . '.php';
            //     $string = $route->create($table, $options);
            //     file_put_contents($file, $string);
            //     echo "Rotas geradas\n";
            // }

            // if ($this->createTest) {
            //     $file   = App::createPath(App::getDocumentRoot() . 'test/Postman') . '/' . str_replace('\\', '_', $namespace) . '_' . $className . '.json';
            //     $string = $postman->create($table, $options);
            //     file_put_contents($file, $string);
            //     echo "Testes gerados\n";
            // }
        }

        echo "processo concluido\n";
    }
}
