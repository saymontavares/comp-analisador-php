<?php
/**
 * Class Analisador Léxico
 * @author Saymon Tavares
 * @author Matheus
 */
class Compilador {

    private $code;
    private $tokens;
    private $tableSerial;
    private $reservedWords = [
        1   => 'programa',
        2   => '{',
        3   => 'funcao',
        4   => 'inteiro',
        5   => 'escreva',
        6   => '(',
        7   => ')',
        8   => 'leia',
        9   => '=',
        10  => '/',
        11  => '%',
        12  => 'escreva'
    ];

    public function __construct($code)
    {
        $this->code = $code;
    }


    public function createTokens()
    {
        $this->tokens = str_replace(['  ', "\t"], '', $this->code);
        $this->tokens = explode("\n", $this->tokens);
        foreach ($this->tokens as $k => $v) $this->tokens[$k] = trim($v);
        $this->tokens = array_filter($this->tokens);
        foreach ($this->tokens as $k => $v) {

            echo "<pre>";
            echo "{$k} => ";
            print_r (strpos($v, '"'));
            echo "</pre>";

        }
        return $this->tokens;
    }

    public function createTableSerial()
    {
        $tokens = $this->createTokens();
        foreach ($tokens as $k => $v) {

            echo "<pre>";
            print_r ($v);
            echo "</pre>";

        }
    }

    public function compilar()
    {
        return $this->createTokens();
    }

}