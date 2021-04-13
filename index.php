<?php

require_once 'Compilador.class.php';

$code = 'programa
        {
            funcao inicio()
            {
                inteiro metade_inteira, resto, valor

                escreva("Digite um valor: ")
                leia(valor)

                metade_inteira = valor
                resto = valor % 3

                escreva("\nA metade inteira do numero é: ", metade_inteira)
                escreva("\nO resto (mod) da divisão por 3 é: ", resto)
            }
        }';

$code = 'programa
        {
            funcao inicio()
            {
                real a, b, soma, sub, mult, div

                escreva("Digite o primeiro número: ")
                leia(a)

                escreva("Digite o segundo número: ")
                leia(b)

                soma = a + b
                sub = a - b
                mult = a * b
                div = a / b

                escreva("\nA soma dos números é igual a: ", soma)
                escreva("\nA subtração dos números é igual a: ", sub)
                escreva("\nA multiplicação dos números é igual a: ", mult)
                escreva("\nA divisão dos números é igual a: ", div)
            }
        }';

$code = '
programa
{
    funcao inicio()
    {
        real resultado

        resultado = 5.0 + 4.0 * 2.0
        escreva("Operação: 5 + 4 * 2 = ", resultado)

        resultado = 5.0 + 4.0 * 2.0
        escreva("\nOperação: (5 + 4) * 2 = ", resultado)

        resultado = 1.0 + 2.0 / 3.0 * 4.0
        escreva("\nOperação: 1 + 2 / 3 * 4 = ", resultado)

        resultado = 1.0 + 2.0 / 3.0 * 4.0
        escreva("\nOperação: (1 + 2) / (3 * 4) = ", resultado)
    }
}
';

$compilador = new Compilador($code);
echo "<pre style='background-color:black;color:#FFF;padding:17px;width:50%'>";
print_r ($code);
echo "</pre>";
echo "<pre>";
print_r ($compilador->compilar());
echo "</pre>";