<?php

use Rbtecnet\Phpnfse\Provedores\NotaCarioca\Operations\Cancelar\CancelarNfse;

require "vendor/autoload.php";
//rotina de teste de consulta

$ambiente = "homologacao";
$certificado = "c:/certificados/telefonia.pfx";
$senha = "Brasinorte155";


$dados = [
    'IdentificacaoNfse' => [
        'Numero' => '23494',
        'Cnpj' => '03287545000119',
        'InscricaoMunicipal' => '02615789',
        'CodigoMunicipio' => '3304557',
    ],
    'CodigoCancelamento' => '1',
];

$operacao = new CancelarNfse();
$retorno = $operacao->CancelarNfse($ambiente,$dados,$certificado,$senha);
var_dump($retorno);