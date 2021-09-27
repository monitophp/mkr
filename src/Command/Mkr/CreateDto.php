<?php
namespace MonitoMkr\Command\Mkr;

use \MonitoLib\Mcl\Option;
use \MonitoLib\Mcl\Param;

class CreateDto extends \MonitoLib\Mcl\Command
{
    const VERSION = '1.0.0';
    /**
     * 1.0.0 - 2021-06-22
     * Initial release
     */

    protected $name   = 'create-dto';
    protected $class  = \MonitoMkr\Cli\CreateDto::class;
    protected $method = 'create';
    protected $help   = 'Cria objeto dto baseado no model';

    public function __construct()
    {
        // Adiciona um parâmetro ao comando
        $this->addParam(
            new class extends Param
            {
                protected $name     = 'model-name';
                protected $help     = 'Nome completo da classe model';
                protected $required = true;
            }
        );

        // Adiciona uma opção ao comando
        $this->addOption(
            new class extends Option
            {
                protected $name    = 'force';
                protected $alias   = 'f';
                protected $help    = 'Força a criação do objeto dto, se existir';
                protected $type    = 'boolean';
                protected $default = false;
            }
        );
    }
}