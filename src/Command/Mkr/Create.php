<?php
namespace MonitoMkr\Command\Mkr;

use \MonitoLib\Mcl\Option;
use \MonitoLib\Mcl\Param;

class Create extends \MonitoLib\Mcl\Command
{
    const VERSION = '1.0.0';
    /**
     * 1.0.0 - 2021-07-01
     * Initial release
     */

    protected string $name   = 'create';
    protected string $class  = \MonitoMkr\Cli\Mkr::class;
    protected string $method = 'create';
    protected string $help   = 'Cria objetos baseados em tabelas';

    public function __construct()
    {
        // Adiciona um parâmetro ao comando
        $this->addParam(
            new class extends Param
            {
                protected string $name     = 'objects';
                protected string $help     = 'Lista dos tipos de objetos que serão criados';
                // protected $required = true;
            }
        );
        // Adiciona uma opção ao comando
        $this->addOption(
            new class extends Option
            {
                protected ?string $name     = 'connection-name';
                protected ?string $alias    = 'c';
                protected ?string $help     = 'Nome da conexão com o banco de dados';
                // protected $required = true;
                protected ?string $type     = 'string';
            }
        );
        // Adiciona uma opção ao comando
        $this->addOption(
            new class extends Option
            {
                protected ?string $name     = 'namespace';
                protected ?string $alias    = 'n';
                protected ?string $help     = 'Namespace onde serão criados os objetos';
                // protected string $required = true;
                protected ?string $type     = 'string';
            }
        );
        // Adiciona uma opção ao comando
        $this->addOption(
            new class extends Option
            {
                protected ?string $name     = 'tables';
                protected ?string $alias    = 't';
                protected ?string $help     = 'Tabelas que serão importadas. Se não informada, todas as tabelas da conexão serão importadas.';
                // protected string $required = true;
                protected ?string $type     = 'string';
            }
        );
        // Adiciona uma opção ao comando
        $this->addOption(
            new class extends Option
            {
                protected ?string $name     = 'base-url';
                protected ?string $help     = 'Url base das rotas. Se não informado será usado o namespace.';
                protected ?string $type     = 'string';
            }
        );
        // Adiciona uma opção ao comando
        $this->addOption(
            new class extends Option
            {
                protected ?string $name     = 'controller-methods';
                protected ?string $help     = 'Indica que o controller deve ser gerado com os métodos';
                protected ?string $type     = 'boolean';
                protected $default  = false;
            }
        );
        // Adiciona uma opção ao comando
        $this->addOption(
            new class extends Option
            {
                protected ?string $name     = 'no-route';
                protected ?string $help     = 'Indica que as rotas não devem ser geradas';
                protected ?string $type     = 'boolean';
                protected $default  = false;
            }
        );
        // Adiciona uma opção ao comando
        $this->addOption(
            new class extends Option
            {
                protected ?string $name     = 'no-test';
                protected ?string $help     = 'Indica que os testes não devem ser gerados';
                protected ?string $type     = 'boolean';
                protected $default  = false;
            }
        );
        // Adiciona uma opção ao comando
        $this->addOption(
            new class extends Option
            {
                protected ?string $name     = 'only-required';
                protected ?string $help     = 'Indica que somente as colunas obrigatórias serão consideradas';
                protected ?string $type     = 'boolean';
                protected $default  = false;
            }
        );
        // Adiciona uma opção ao comando
        $this->addOption(
            new class extends Option
            {
                protected ?string $name     = 'force';
                protected ?string $help     = 'Força a geração controllers e daos existentes';
                protected ?string $type     = 'boolean';
                protected $default  = false;
            }
        );
        // Adiciona uma opção ao comando
        $this->addOption(
            new class extends Option
            {
                protected ?string $name  = 'columns';
                // protected ?string $alias = 'c';
                protected ?string $help  = 'Colunas que serão importadas';
            }
        );
        // Adiciona uma opção ao comando
        $this->addOption(
            new class extends Option
            {
                protected ?string $name     = 'prefix';
                protected ?string $alias    = 'p';
                protected ?string $help     = 'Prefixo das tabelas a ser ignorado';
                protected ?string $type     = 'string';
            }
        );
    }
}