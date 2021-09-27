<?php
namespace MonitoMkr\Command\Mkr;

use \MonitoLib\Mcl\Option;
use \MonitoLib\Mcl\Param;

class AddColumn extends \MonitoLib\Mcl\Command
{
    const VERSION = '1.0.0';
    /**
     * 1.0.0 - 2021-07-01
     * Initial release
     */

    protected $name   = 'add-column';
    protected $class  = \MonitoMkr\Cli\Column::class;
    protected $method = 'add';
    protected $help   = 'Adiciona uma coluna a um modelo';

    public function __construct()
    {
        // Adiciona um parâmetro ao comando
        $this->addParam(
            new class extends Param
            {
                protected $name     = 'model-file';
                protected $help     = 'Arquivo da classe model';
                protected $required = true;
            }
        );

        // Adiciona uma opção ao comando
        $this->addOption(
            new class extends Option
            {
                protected $name     = 'column';
                protected $alias    = 'c';
                protected $help     = 'Nome da coluna a ser adicionada';
                protected $required = true;
                // protected $type    = 'boolean';
                // protected $default = false;
            }
        );
    }
}