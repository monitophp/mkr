<?php
namespace MonitoMkr\Command;

use \MonitoLib\Mcl\Command;
use \MonitoLib\Mcl\Module;
use \MonitoLib\Mcl\Option;
use \MonitoLib\Mcl\Param;

class Mkr extends Module
{
    const VERSION = '1.0.0';
    /**
     * 1.0.0 - 2020-10-01
     * initial release
     */

    protected $name = 'mkr';
    protected $help = 'Cria aplicações da MonitoLib';

    public function setup()
    {
        // Adiciona um comando
        $command = $this->addCommand(
            new class extends Command
            {
                protected $name   = 'create';
                protected $class  = '\MonitoMkr\Cli\Mkr';
                protected $method = 'create';
                protected $help   = 'Cria objetos baseados em tabelas';
            }
        );
        // Adiciona um parâmetro ao comando
        $command->addParam(
            new class extends Param
            {
                protected $name     = 'objects';
                protected $help     = 'Lista dos tipos de objetos que serão criados';
                // protected $required = true;
            }
        );
        // Adiciona uma opção ao comando
        $command->addOption(
            new class extends Option
            {
                protected $name     = 'connection-name';
                protected $alias    = 'c';
                protected $help     = 'Nome da conexão com o banco de dados';
                // protected $required = true;
                protected $type     = 'string';
            }
        );
        // Adiciona uma opção ao comando
        $command->addOption(
            new class extends Option
            {
                protected $name     = 'namespace';
                protected $alias    = 'n';
                protected $help     = 'Namespace onde serão criados os objetos';
                // protected $required = true;
                protected $type     = 'string';
            }
        );
        // Adiciona uma opção ao comando
        $command->addOption(
            new class extends Option
            {
                protected $name     = 'tables';
                protected $alias    = 't';
                protected $help     = 'Tabelas que serão importadas. Se não informada, todas as tabelas da conexão serão importadas.';
                // protected $required = true;
                protected $type     = 'string';
            }
        );
        // Adiciona uma opção ao comando
        $command->addOption(
            new class extends Option
            {
                protected $name     = 'base-url';
                protected $help     = 'Url base das rotas. Se não informado será usado o namespace.';
                protected $type     = 'string';
            }
        );
        // Adiciona uma opção ao comando
        $command->addOption(
            new class extends Option
            {
                protected $name     = 'controller-methods';
                protected $help     = 'Indica que o controller deve ser gerado com os métodos';
                protected $type     = 'boolean';
                protected $default  = false;
            }
        );
        // Adiciona uma opção ao comando
        $command->addOption(
            new class extends Option
            {
                protected $name     = 'no-route';
                protected $help     = 'Indica que as rotas não devem ser geradas';
                protected $type     = 'boolean';
                protected $default  = false;
            }
        );
        // Adiciona uma opção ao comando
        $command->addOption(
            new class extends Option
            {
                protected $name     = 'no-test';
                protected $help     = 'Indica que os testes não devem ser gerados';
                protected $type     = 'boolean';
                protected $default  = false;
            }
        );
        // Adiciona uma opção ao comando
        $command->addOption(
            new class extends Option
            {
                protected $name     = 'only-required';
                protected $help     = 'Indica que somente as colunas obrigatórias serão consideradas';
                protected $type     = 'boolean';
                protected $default  = false;
            }
        );
        // Adiciona uma opção ao comando
        $command->addOption(
            new class extends Option
            {
                protected $name     = 'force';
                protected $help     = 'Força a geração controllers e daos existentes';
                protected $type     = 'boolean';
                protected $default  = false;
            }
        );
        // Adiciona uma opção ao comando
        $command->addOption(
            new class extends Option
            {
                protected $name  = 'columns';
                // protected $alias = 'c';
                protected $help  = 'Colunas que serão importadas';
            }
        );
    }
}