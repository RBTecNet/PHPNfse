<?php

use Rbtecnet\Phpnfse\Provedores\Saquarema\Operations\Consultar\ConsultarNfse;

require "vendor/autoload.php";
try{
    //rotina de teste de consulta

    $ambiente = "homologacao";
    $certificado = "c:/certificados/telecom.pfx";
    $senha = "Brasinorte155";

    $dados = [
        'Prestador' => [
            'Cnpj' => '18890963000173',
            'InscricaoMunicipal' => '12717179',
        ],
        'PeriodoEmissao' => [
            'DataInicial' => '2023-05-01',
            'DataFinal' => '2023-05-01',
        ],
        'Tomador' =>[

        ],];

    $operacao = new ConsultarNfse();
    $retorno = $operacao->ConsultarNfse($ambiente,$dados,$certificado,$senha);
    var_dump($retorno);







}catch (Exception $e){
    dd($e);
}
