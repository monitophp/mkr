<?php
namespace MonitoMkr\Command;

class Mkr extends \MonitoLib\Mcl\Module
{
    const VERSION = '1.0.0';
    /**
     * 1.0.0 - 2020-10-01
     * initial release
     */

    protected string $name = 'mkr';
    protected string $help = 'Cria aplicações da MonitoLib';

    public function setup()
    {
        // Cria arquivos baseados em tabelas do banco de dados
        $this->addCommand(new Mkr\Create());

        // Cria uma classe dto baseada em um model
        // $this->addCommand(new Mkr\CreateDto());

        // Adiciona uma coluna a um modelo
        // $this->addCommand(new Mkr\AddColumn());

        // Remove uma columa de um modelo
        // $this->addCommand(new Mkr\DelColumn());

        // Deleta um modelo e seus arquivos de uma aplicação
        // $this->addCommand(new Mkr\Delete());

        // Atualiza as colunas de um modelo
        // $this->addCommand(new Mkr\Update());
    }
}