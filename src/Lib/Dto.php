<?php
namespace MonitoMkr\Lib;

use MonitoLib\App;

class Dto
{
    const VERSION = '1.0.0';
    /**
     * 1.0.0 - 2020-10-01
     * initial release
     */

    public function create(\MonitoLib\Database\Model\Table $table, ?\MonitoMkr\Type\Options $options) : string
    {
        if (is_null($options)) {
            $options = new \MonitoMkr\Type\Options();
        }

        $props = '';
        $gets  = '';
        $sets  = '';

        $namespace = $options->getNamespace();
        $classname = $table->getClass();
        $columns   = $table->getColumns();
        // \MonitoLib\Dev::pre($columns);

        $ordered = $columns;

        $props = implode('', array_map(fn($column) => $this->renderProp($column), $columns));


        usort($ordered, fn($a, $b) => strcmp($a->getId(), $b->getId()));


        foreach ($ordered as $column) {
            // $props .= $this->renderProp($column);
            // TODO: criar métodos "add" para arrays
            $gets  .= $this->renderGet($column);
            $sets  .= $this->renderSet($column);
        }

        $fs = "<?php\n"
            . "namespace {$namespace}\\Dto;\n"
            . "\n"
            . "class {$classname}\n"
            . "{\n"
            . "    const VERSION = '1.0.0';\n"
            . "    /**\n"
            . "     * 1.0.0 - " . date('Y-m-d') . "\n"
            . "     * Initial release\n"
            . "     *\n"
            . '     * ' . __CLASS__ . ' v' . self::VERSION . ' ' . App::now() . "\n"
            . "     */\n"
            . "\n"
            . $props
            . "\n"
            . $gets
            . $sets
            . '}';
        return $fs;

        // $outpath = App::getDocumentRoot() . 'src/' . str_replace('\\', '/', $namespace) . App::DS;
        // $file = App::createPath($outpath . 'Dto/') . $classname . '.php';

        // // \MonitoLib\Dev::vde($file);
        // file_put_contents($file, $f);

        // \MonitoLib\Dev::ee($f);
    }
    // public function createFromDatabase(\MonitoLib\Database\Model $model, \MonitoMkr\Type\Options $options)
    // {
    //     $fieldList = $model->getFields();

    //     \MonitoLib\Dev::pre($fieldList);

    //     if (empty($fieldList)) {
    //         \MonitoLib\Dev::ee('oooooooooops');
    //     }

    //     foreach ($fieldList as $field) {
    //     }
    // }
    // public function createFromModel(\MonitoLib\Database\Model $model, \MonitoMkr\Type\Options $options)
    // {
    //     $fieldList = $model->getFields();

    //     $fields = [];

    //     foreach ($fieldList as $field) {
    //         $id        = $field->getId();         // ;
    //         $name      = $field->getName();       // ;
    //         $auto      = $field->getAuto();       //  = false;
    //         $source    = $field->getSource();     // ;
    //         $type      = $field->getType();       //  = 'string';
    //         $format    = $field->getFormat();     // ;
    //         $charset   = $field->getCharset();    //  = 'utf8';
    //         $collation = $field->getCollation();  //  = 'utf8_general_ci';
    //         $default   = $field->getDefault();    // ;
    //         $label     = $field->getLabel();      //  = '';
    //         $maxLength = $field->getMaxLength();  //  = 0;
    //         $minLength = $field->getMinLength();  //  = 0;
    //         $maxValue  = $field->getMaxValue();   //  = 0;
    //         $minValue  = $field->getMinValue();   //  = 0;
    //         $precision = $field->getPrecision();  // ;
    //         $scale     = $field->getScale();      // ;
    //         $primary   = $field->getPrimary();    //  = false;
    //         $required  = $field->getRequired();   //  = false;
    //         $transform = $field->getTransform();  // ;
    //         $unique    = $field->getUnique();     //  = false;
    //         $unsigned  = $field->getUnsigned();   //  = false;

    //         $fieldObj = new \MonitoMkr\Dto\Field();
    //         $fieldObj->setProperty($id);
    //         $fieldObj->setType($type);

    //         $fieldObj->setName($name);

    //         $method = ucfirst($id);
    //         $fieldObj->setMethod($method);

    //         if (!$required) {
    //             $fieldObj->setNullMark('?');
    //         }

    //         // $fieldObj->setRestrict($restrict);

    //         if (!is_null($default)) {
    //             $value = $default;

    //             if ($type === 'string') {
    //                 $value = "'$value'";
    //             }

    //             if ($type === 'bool') {
    //                 $value = $default ? 'true' : 'false';
    //             }

    //             $value = " = $value";
    //             $fieldObj->setValue($value);
    //         }

    //         $fields[] = $fieldObj;
    //     }

    //     $this->create($model, $fields, $options);
    // }
    private function renderGet(\MonitoLib\Database\Model\Column $field) : string
    {
        $prop   = $field->getid();
        $method = 'get' . ucfirst($prop);
        $type   = str_replace('::class', '', $field->getType());

        $ms = <<<PHP
    /**
    * {$method}()
    *
    * @return {$type} \${$prop}
    */
    public function {$method}() : ?{$type}
    {
        return \$this->$prop;
    }
PHP;
        return $ms;
    }
    private function renderProp(\MonitoLib\Database\Model\Column $field) : string
    {
        $prop  = $field->getId();
        $value = $field->getDefault() ?? '';
        $value = $value === '' ? '' : " = '$value'";

        return "    private \${$prop}{$value};\n";
    }
    private function renderSet(\MonitoLib\Database\Model\Column $field) : string
    {
        $prop     = $field->getid();
        $method   = 'set' . ucfirst($prop);
        $type     = str_replace('::class', '', $field->getType());
        $nullMark = $field->getRequired()  ? '' : '?';

        $ms = "    /**\n"
            . "    * {$method}()\n"
            . "    *\n"
            . "    * @return \$this\n"
            . "    */\n"
            . "    public function {$method}({$nullMark}{$type} \${$prop}) : self\n"
            . "    {\n"
            . "        \$this->{$prop} = \${$prop};\n"
            . "        return \$this;\n"
            . "    }\n";
        return $ms;
    }
}