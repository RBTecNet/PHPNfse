<?php

use Rbtecnet\Phpnfse\Provedores\NotaCarioca\Operations\Consultar\ConsultarNfse;

require "vendor/autoload.php";
try{
    //rotina de teste de consulta

    $ambiente = "homologacao";
    $certificado = "c:/certificados/telefonia.pfx";
    $senha = "Brasinorte155";

    $dados = [
        'Prestador' => [
            'Cnpj' => '03287545000119',
            'InscricaoMunicipal' => '02615789',
        ],
        'PeriodoEmissao' => [
            'DataInicial' => '2023-02-14',
            'DataFinal' => '2023-02-14',
        ],
        'Tomador' =>[

        ],];

    $operacao = new ConsultarNfse();
    $retorno = $operacao->ConsultarNfse($ambiente,$dados,$certificado,$senha);
    var_dump($retorno);







}catch (Exception $e){
    dd($e);
}
