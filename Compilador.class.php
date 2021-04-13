<?php
/**
 * Class Analisador Léxico
 * @author Saymon Tavares
 * @author Matheus
 */
class Compilador {

    private $code;
    private $tokens;
    private $tableSerial = [];
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
        // 13 => string
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
            if (strpos($v, '"') && strripos($v, '"')) {
                if (strripos($v, '"') <= strpos($v, '"') || substr_count($v, '"') % 2 != 0) {
                    trigger_error("Erro na string ('\"')", E_USER_ERROR);
                } elseif (strripos($v, '"') > strpos($v, '"')) {
                    array_push($this->tableSerial, [
                        13 => substr($v, (strpos($v, '"')+1), (strripos($v, '"')-9))
                        ]);
                        substr_replace($v, '', 0, 10);
                }
            } else {
                echo array_search($v, $this->reservedWords);
            }
        }
        print_r($this->tokens);
        return $this->tableSerial;
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