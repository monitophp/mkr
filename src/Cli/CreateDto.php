<?php
namespace MonitoMkr\Cli;

use \MonitoLib\Exception\BadRequest;
use \MonitoLib\Functions;
use \MonitoLib\Mcl\Request;

class CreateDto extends \MonitoLib\Mcl\Controller
{
    const VERSION = '1.0.0';
    /**
     * 1.0.0 - 2021-06-22
     * Initial release
     */

    public function create()
    {
        $modelFile = Request::getParam('model-name')->getValue();
        $force     = Request::getOption('force')->getValue() ?? false;

        $modelName = Functions::getClassnameFromFile($modelFile);

        if (!class_exists($modelName)) {
            throw new BadRequest("Model $modelName not found");
        }

        $model = new $modelName();


        $fields = $model->getColumns();



        // private $id;
        // private $name;
        // private $auto = false;
        // private $source;
        // private $type = 'string';
        // private $format;
        // private $charset = 'utf8';
        // private $collation = 'utf8_general_ci';
        // private $default;
        // private $label = '';
        // private $maxLength = 0;
        // private $minLength = 0;
        // private $maxValue = 0;
        // private $minValue = 0;
        // private $precision;
        // private $restrict = [];
        // private $scale;
        // private $primary = false;
        // private $required = false;
        // private $transform;
        // private $unique = false;
        // private $unsigned = false;


        // [id:MonitoLib\Database\Model\Field:private] => fileId
        // [name:MonitoLib\Database\Model\Field:private] => fileId
        // [auto:MonitoLib\Database\Model\Field:private] =>
        // [source:MonitoLib\Database\Model\Field:private] =>
        // [type:MonitoLib\Database\Model\Field:private] => oid
        // [format:MonitoLib\Database\Model\Field:private] =>
        // [charset:MonitoLib\Database\Model\Field:private] => utf8
        // [collation:MonitoLib\Database\Model\Field:private] => utf8_general_ci
        // [default:MonitoLib\Database\Model\Field:private] =>
        // [label:MonitoLib\Database\Model\Field:private] =>
        // [maxLength:MonitoLib\Database\Model\Field:private] => 0
        // [minLength:MonitoLib\Database\Model\Field:private] => 0
        // [maxValue:MonitoLib\Database\Model\Field:private] => 0
        // [minValue:MonitoLib\Database\Model\Field:private] => 0
        // [precision:MonitoLib\Database\Model\Field:private] =>
        // [restrict:MonitoLib\Database\Model\Field:private] => Array
        // [scale:MonitoLib\Database\Model\Field:private] =>
        // [primary:MonitoLib\Database\Model\Field:private] =>
        // [required:MonitoLib\Database\Model\Field:private] => 1
        // [transform:MonitoLib\Database\Model\Field:private] =>
        // [unique:MonitoLib\Database\Model\Field:private] =>
        // [unsigned:MonitoLib\Database\Model\Field:private] =>






        // \MonitoLib\Dev::pre($fields);

        // \MonitoLib\Dev::pre($model);

        $classname = Functions::getClassname($modelName);
        $namespace = Functions::getNamespace($modelName, 1);
        $tablename = $model->getTablename();

        $table = new \MonitoMkr\Dto\Table();
        $table
            ->setName($tablename)
            // ->setDatabase($database)
            // ->setAlias($alias)
            ->setClass($modelName)
            // ->setObject($object)
            // ->setPlural($plural)
            // ->setSingular($singular)
            // ->setPrefix($prefix)
            // ->setType($type)
            // ->setColumns($columns)
            // ->setConstraints($constraints)
            ;
        // \MonitoLib\Dev::pre($table);

        // Create options
        $options = new \MonitoMkr\Type\Options();
        $options->setForce($force)
            ->setClassname($classname)
            ->setNamespace($namespace);

        // Cria o arquivo
        $dto = new \MonitoMkr\Lib\Dto();
        $fs = $dto->create($table, $options);

        \MonitoLib\Dev::ee($fs);

        // $file   = App::createPath($outpath . 'Dto/') . $className . '.php';
        // $string = $dto->create($table, $options);
        // file_put_contents($file, $string);
        // echo "Dto gerado\n";

        echo "file created\n";
    }
}
