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

    protected string $name   = 'create-dto';
    protected string $class  = \MonitoMkr\Cli\CreateDto::class;
    protected string $method = 'create';
    protected string $help   = 'Cria objeto dto baseado no model';

    public function __construct()
    {
        // Adiciona um parâmetro ao comando
        $this->addParam(
            new class extends Param
            {
                protected string $name     = 'model-name';
                protected string $help     = 'Nome completo da classe model';
                protected $required = true;
            }
        );

        // Adiciona uma opção ao comando
        $this->addOption(
            new class extends Option
            {
                protected ?string $name    = 'force';
                protected ?string $alias   = 'f';
                protected ?string $help    = 'Força a criação do objeto dto, se existir';
                protected ?string $type    = 'boolean';
                protected $default = false;
            }
        );
    }
}