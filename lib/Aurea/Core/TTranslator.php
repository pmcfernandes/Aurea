<?php
declare(strict_types=1);

namespace Aurea\Core;

/**
 * TTranslator — internacionalização mínima (pt/en).
 *
 *   TTranslator::setLanguage('en');
 *   echo _t('Record saved');      // "Registro salvo" em pt
 *   echo _t('Hello %s', $nome);   // sprintf
 *
 * Dicionários da aplicação podem ser adicionados com addDictionary().
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TTranslator
{
    protected static string $lang = 'pt';
    protected static array $dictionaries = [
        'en' => [
            'Record saved' => 'Record saved',
            'Record deleted' => 'Record deleted',
            'Do you want to delete the record?' => 'Do you want to delete the record?',
            'No records found' => 'No records found',
            'Save' => 'Save',
            'New' => 'New',
            'Search' => 'Search',
            'Actions' => 'Actions',
            'The field %s is required.' => 'The field %s is required.',
            'Cancel' => 'Cancel',
            'Delete' => 'Delete',
            'Edit' => 'Edit',
            'Close' => 'Close',
            'Confirm' => 'Confirm',
            'Yes' => 'Yes',
            'No' => 'No',
            'Export' => 'Export',
            'Filters' => 'Filters',
            'Filter' => 'Filter',
            'Back' => 'Back',
            'Next' => 'Next',
            'Previous' => 'Previous',
            'Finish' => 'Finish',
            'Loading...' => 'Loading...',
            'Welcome' => 'Welcome',
            'Login' => 'Login',
            'Logout' => 'Logout',
            'Password' => 'Password',
            'Dashboard' => 'Dashboard',
            'Settings' => 'Settings',
            'Records' => 'Records',
            'Page' => 'Page',
            'of' => 'of',
        ],
        'pt' => [
            'Record saved' => 'Registro salvo',
            'Record deleted' => 'Registro excluído',
            'Do you want to delete the record?' => 'Deseja excluir o registro?',
            'No records found' => 'Nenhum registro encontrado',
            'Save' => 'Salvar',
            'New' => 'Novo',
            'Search' => 'Buscar',
            'Actions' => 'Ações',
            'The field %s is required.' => 'O campo %s é obrigatório.',
            'Cancel' => 'Cancelar',
            'Delete' => 'Excluir',
            'Edit' => 'Editar',
            'Close' => 'Fechar',
            'Confirm' => 'Confirmar',
            'Yes' => 'Sim',
            'No' => 'Não',
            'Export' => 'Exportar',
            'Filters' => 'Filtros',
            'Filter' => 'Filtro',
            'Back' => 'Voltar',
            'Next' => 'Seguinte',
            'Previous' => 'Anterior',
            'Finish' => 'Concluir',
            'Loading...' => 'A carregar...',
            'Welcome' => 'Bem-vindo',
            'Login' => 'Entrar',
            'Logout' => 'Sair',
            'Password' => 'Senha',
            'Dashboard' => 'Painel',
            'Settings' => 'Definições',
            'Records' => 'registos',
            'Page' => 'Página',
            'of' => 'de',
        ],
    ];

    /**
     * Define o idioma ativo.
     *
     * @param string $lang Código do idioma
     */
    public static function setLanguage(string $lang): void
    {
        self::$lang = $lang;
    }

    /**
     * Obtém o idioma ativo.
     *
     * @return string Código do idioma ativo
     */
    public static function getLanguage(): string
    {
        return self::$lang;
    }

    /**
     * Adiciona mensagens ao dicionário de um idioma.
     *
     * @param string $lang Código do idioma
     * @param array $messages Mensagens do dicionário
     */
    public static function addDictionary(string $lang, array $messages): void
    {
        self::$dictionaries[$lang] = array_merge(self::$dictionaries[$lang] ?? [], $messages);
    }

    /**
     * Traduz uma mensagem com sprintf opcional.
     *
     * @param string $message Mensagem original
     * @param mixed $args Valores para sprintf
     * @return string Mensagem traduzida
     */
    public static function translate(string $message, mixed ...$args): string
    {
        $text = self::$dictionaries[self::$lang][$message] ?? $message;
        return $args ? sprintf($text, ...$args) : $text;
    }
}
