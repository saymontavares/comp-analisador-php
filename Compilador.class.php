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
        12  => 'escreva',
        // 13 => String,
        14  => '}',
        15  => 'inicio',
        // 16  => Var,
        17  => '+',
        18  => '-',
        19  => '*'
        // 20  => Number
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
        return $this->tokens;
    }

    public function createTableSerial()
    {
        $tokens = $this->createTokens();
        foreach ($tokens as $k => $v) {
            if (strpos($v, '"') && strripos($v, '"')) {
                if (strripos($v, '"') <= strpos($v, '"') || substr_count($v, '"') % 2 != 0) {
                    trigger_error("Erro na string (\")", E_USER_ERROR);
                } elseif (strripos($v, '"') > strpos($v, '"')) {
                    array_push($this->tableSerial, [
                        13 => substr($v, (strpos($v, '"')+1), (strripos($v, '"')-9))
                    ]);
                    $tokens[$k] = substr_replace($v, '', strpos($v, '"'), strripos($v, '"')-7);
                    $tokens[$k] = substr_replace($v, '', strpos($v, '('), strripos($v, ')'));
                    $search = array_search($tokens[$k], $this->reservedWords);
                    if ($search !== false) {
                        array_push($this->tableSerial, [
                            $search => $tokens[$k]
                        ]);
                    } else {
                        trigger_error("Erro na string: {$tokens[$k]}", E_USER_ERROR);
                    }
                }
            } else {
                $exp = explode(' ', $v);
                foreach ($exp as $k => $v) {
                    $search = array_search($v, $this->reservedWords);
                    if ($search !== false) {
                        array_push($this->tableSerial, [
                            $search => $v
                        ]);
                        unset($exp[$k]);
                    } else {
                        if (strpos($v, '(') !== false && strripos($v, ')') !== false && strpos($v, '(') < strripos($v, ')')) {
                            $strF = substr($v, strpos($v, '('), strripos($v, ')'));
                            if (strpos($v, '(')+1 == strripos($v, ')')) {
                                $exp[$k] = substr_replace($v, '', strpos($v, '('), strripos($v, ')'));
                                $search = array_search($exp[$k], $this->reservedWords);
                                if ($search !== false) {
                                    array_push($this->tableSerial, [
                                        $search => $exp[$k]
                                    ]);
                                    unset($exp[$k]);
                                }
                            } else {
                                $var = substr($v, strpos($v, '(')+1, strripos($v, ')')-5);
                                $exp[$k] = substr_replace($v, '', strpos($v, '('), strripos($v, ')'));
                                if (isset($exp[$k]) && !preg_match('/[\'^£$%&*()}{@#~?><>,|=+¬-]/', $var)) {
                                    array_push($this->tableSerial, [
                                        16 => $var
                                    ]);
                                }
                                $search = array_search($exp[$k], $this->reservedWords);
                                if ($search !== false) {
                                    array_push($this->tableSerial, [
                                        $search => $exp[$k]
                                    ]);
                                    unset($exp[$k]);
                                } else {
                                    trigger_error("Um erro lexico foi encontrado: {$exp[$k]}", E_USER_ERROR);
                                }
                            }
                        } else {
                            $exp[$k] = str_replace(',', '', $exp[$k]);
                            if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=+¬-]/', $exp[$k])) {
                                if (is_numeric($exp[$k])) {
                                    array_push($this->tableSerial, [
                                        20 => $exp[$k]
                                    ]);
                                } else {
                                    array_push($this->tableSerial, [
                                        16 => $exp[$k]
                                    ]);
                                }
                                unset($exp[$k]);
                            } else {
                                trigger_error("Um erro lexico foi encontrado: {$exp[$k]}", E_USER_ERROR);
                            }
                        }
                    }
                }

                foreach ($exp as $k => $v) {
                    $exp[$k] = str_replace(',', '', $exp[$k]);
                    if (is_numeric($exp[$k])) {
                        array_push($this->tableSerial, [
                            20 => $exp[$k]
                        ]);
                        unset($exp[$k]);
                    }
                    if (isset($exp[$k]) && !preg_match('/[\'^£$%&*()}{@#~?><>,|=+¬-]/', $v)) {
                        array_push($this->tableSerial, [
                            16 => $exp[$k]
                        ]);
                        unset($exp[$k]);
                    }
                }

                if (!empty($exp)) {
                    trigger_error("Um erro lexico foi encontrado: {$exp[$k]}", E_USER_ERROR);
                }
            }
        }

        return $this->tableSerial;
    }

    public function compilar()
    {
        return $this->createTableSerial();
    }

    public function tokensArr()
    {
        $serial = $this->createTableSerial();
        $reserv = $this->reservedWords;
        $reserv[13] = 'String';
        $reserv[16] = 'Var';
        $reserv[20] = 'Number';
        $tableTokens = [];
        foreach ($serial as $a) {
            foreach ($a as $k => $b) {
                $tableTokens[] = [
                    'code' => array_search($b, $a),
                    'value' => $b,
                    'tipo' => $reserv[array_search($b, $a)]
                ];
            }
        }

        return $tableTokens;
    }

}