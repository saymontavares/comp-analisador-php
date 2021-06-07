<?php
/**
 * Class Compilador
 * @author Saymon Souza Tavares
 * @author Mateus Brognoli Silvano
 */
class Compilador {

    private $code;
    private $tokens;
    private $tabelaTokens = [];
    private $palavrasReservadas = [
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

    // função construtora, recebe apenas um parametro obrigatório o código fonte
    public function __construct($code)
    {
        $this->code = $code;
        set_error_handler("funcaoMostraErros");
    }

    // -- ANÁLISE LÉXICA
    // Cria os tokens
    public function scannerLinhasCodigo()
    {
        $this->tokens = str_replace(['  ', "\t"], '', $this->code); // remove os espaços em excesso e as tabulações (Tab)
        $this->tokens = explode("\n", $this->tokens); // cria uma posição na array para cada linha do código
        foreach ($this->tokens as $k => $v) $this->tokens[$k] = trim($v); // percorre cada linha e remove os espaços do começo e fim com trim
        $this->tokens = array_filter($this->tokens); // garatimos que não vai ter posição em branco no array de tokens removendo as mesmas
        return $this->tokens;
    }
    // Fim criação tokens

    // função que gera a tabela de tokens juntamente com a análise léxica
    public function analiseLexica()
    {
        $tokens = $this->scannerLinhasCodigo(); // pega o codigo limpo
        foreach ($tokens as $k => $v) { // percorre todas as linhas
            if (strpos($v, '"') && strripos($v, '"')) { // verifica se existe string "" na linha
                if (strripos($v, '"') <= strpos($v, '"') || substr_count($v, '"') % 2 != 0) { // verificar se abriru e fechou as aspas da string se não gera um erro
                    trigger_error("Erro na string (\")", E_USER_ERROR);
                } elseif (strripos($v, '"') > strpos($v, '"')) {
                    // se a string estiver correta classificamos tudo que tiver dentro de aspas "" como string
                    // 13 é o código referencia da tabela de palavras reservadas ($palavrasReservadas) para strings
                    // fazemos o push para dentro da array de tokens como 13 => String
                    array_push($this->tabelaTokens, [
                        13 => substr($v, (strpos($v, '"')+1), (strripos($v, '"')-9))
                    ]);
                    $tokens[$k] = substr_replace($v, '', strpos($v, '"'), strripos($v, '"')-7); // removemos as aspas que sobraram da string
                    $tokens[$k] = substr_replace($v, '', strpos($v, '('), strripos($v, ')')); // removemos os parênteses que sobraram da linha

                    // pegamos as palavras que sobraram e buscamos na array de palavras reservadas
                    // se encontrou faz o push para a array de tokens se não um erro léxico é gerado
                    $search = array_search($tokens[$k], $this->palavrasReservadas);
                    if ($search !== false) {
                        array_push($this->tabelaTokens, [
                            $search => $tokens[$k]
                        ]);
                    } else {
                        trigger_error("Token não existe: {$tokens[$k]}", E_USER_ERROR);
                    }
                }
            } else {
                // aqui verficamos as linhas que não possuem string

                // na variavel $exp quebramos as linhas em palavras formando um array de palavras
                // desse modo percorremos palavra por palavra e comparamos com nossa array de palavras reservadas
                $exp = explode(' ', $v);
                foreach ($exp as $k => $v) {
                    $search = array_search($v, $this->palavrasReservadas);

                    // se encontrou a palavra coloca em nossa array de tokens e remove a palavra da array $exp
                    if ($search !== false) {
                        array_push($this->tabelaTokens, [
                            $search => $v
                        ]);
                        unset($exp[$k]);
                    } else {
                        // aqui verificamos as palavras dentro dos parênteses
                        // se são palavras reservadas ou variáveis
                        // também verificamos se não possui nenhum caracteres especial nos nomes de variaveis ou palavras reservadas pois não pode, isso vai gerar um erro léxico
                        if (strpos($v, '(') !== false && strripos($v, ')') !== false && strpos($v, '(') < strripos($v, ')')) {
                            $strF = substr($v, strpos($v, '('), strripos($v, ')'));
                            if (strpos($v, '(')+1 == strripos($v, ')')) {
                                $exp[$k] = substr_replace($v, '', strpos($v, '('), strripos($v, ')'));
                                $search = array_search($exp[$k], $this->palavrasReservadas);
                                if ($search !== false) {
                                    array_push($this->tabelaTokens, [
                                        $search => $exp[$k]
                                    ]);
                                    unset($exp[$k]);
                                }
                            } else {
                                $var = substr($v, strpos($v, '(')+1, strripos($v, ')')-5);
                                $exp[$k] = substr_replace($v, '', strpos($v, '('), strripos($v, ')'));
                                // verifica se o nome da variável é válida e não possui nenhum caracter não permitido para esse tipo
                                // classifica como 16 => Var da nossa tabela de tokens
                                if (isset($exp[$k]) && !preg_match('/[\'^£$%&*()}{@#~?><>,|=+¬-]/', $var)) {
                                    array_push($this->tabelaTokens, [
                                        16 => $var
                                    ]);
                                }
                                $search = array_search($exp[$k], $this->palavrasReservadas);
                                if ($search !== false) {
                                    array_push($this->tabelaTokens, [
                                        $search => $exp[$k]
                                    ]);
                                    unset($exp[$k]);
                                } else {
                                    trigger_error("Um erro lexico foi encontrado: {$exp[$k]}", E_USER_ERROR);
                                }
                            }
                        } else {
                            // verificar o valor da variavel se for numerico classifica como 20 => Number
                            $exp[$k] = str_replace(',', '', $exp[$k]);
                            if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=+¬-]/', $exp[$k])) {
                                if (is_numeric($exp[$k])) {
                                    array_push($this->tabelaTokens, [
                                        20 => $exp[$k]
                                    ]);
                                } else {
                                    array_push($this->tabelaTokens, [
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

                // aqui fazemos uma última verificação caso ainda sobre alguma palavra/variável para ser analisada
                foreach ($exp as $k => $v) {
                    $exp[$k] = str_replace(',', '', $exp[$k]);
                    if (is_numeric($exp[$k])) {
                        array_push($this->tabelaTokens, [
                            20 => $exp[$k]
                        ]);
                        unset($exp[$k]);
                    }
                    if (isset($exp[$k]) && !preg_match('/[\'^£$%&*()}{@#~?><>,|=+¬-]/', $v)) {
                        array_push($this->tabelaTokens, [
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

        return $this->tabelaTokens;
    }
    // -- FIM ANÁLISE LÉXICA

    // -- ANÁLISE SINTÁTICA
    public function analiseSintatica()
    {
        $lex = $this->analiseLexica();
        foreach ($lex as $k => $linha) {
            foreach ($linha as $el) {
                if ($k == 0 && $el != 'programa') trigger_error("Um erro sintático foi encontrado: '{$el}' deve começar com 'programa'", E_USER_ERROR);
                if ($k == 1 && $el != '{') trigger_error("Um erro sintático foi encontrado: '{$el}' deve começar com 'programa'", E_USER_ERROR);
                if ($k == 2 && $el != 'funcao') trigger_error("Um erro sintático foi encontrado: '{$el}' sugerimos 'funcao'", E_USER_ERROR);
                if ($k == 3 && $el != 'inicio') trigger_error("Um erro sintático foi encontrado: '{$el}' sugerimos 'funcao'", E_USER_ERROR);
                if ($k == 4 && $el != '{') trigger_error("Um erro sintático foi encontrado: '{$el}' sugerimos '{'", E_USER_ERROR);
            }
        }

        if (!isset($lex[count($lex)-1][14]) || $lex[count($lex)-1][14] != '}') trigger_error("Um erro sintático foi encontrado, a função deve terminar com '}'", E_USER_ERROR);
        if (!isset($lex[count($lex)-2][14]) || $lex[count($lex)-2][14] != '}') trigger_error("Um erro sintático foi encontrado, o programa deve terminar com '}'", E_USER_ERROR);

        return $lex;
    }
    // -- FIM ANÁLISE SINTÁTICA

    // INICIA COMPILAÇÃO
    public function compilar()
    {
        return $this->analiseSintatica();
    }
    // FIM COMPILAÇÃO

    // RETORNA APENAS A ARRAY DE TOKENS PRONTA
    public function tokensArr()
    {
        $serial = $this->compilar();
        $reserv = $this->palavrasReservadas;
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
    // FIM RETORNA APENAS A ARRAY DE TOKENS PRONTA

}

// nossa função para gerar os erros ao dev
function funcaoMostraErros($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) return false;

    $errstr = htmlspecialchars($errstr);

    switch ($errno) {
    case E_USER_ERROR:
        echo "<p style='font-size:1.3em'><b>Erro gerado:</b> $errstr<br />\n";
        echo "  Encontramos problemas na linha <b>{$errline}</b> arquivo <b>{$errfile}</b><br />";
        echo "<small>PHP " . PHP_VERSION . "</small><br /></p>\n";
        exit(1);
        break;
    }

    return true;
}