<?php
use Rbtecnet\Phpnfse\Provedores\NotaCarioca\Operations\Gerar\GerarNfse;
require "vendor/autoload.php";
try {
    //rotina de teste de consulta

    $ambiente = "homologacao";
    $certificado = "c:/certificados/telefonia.pfx";
    $senha = "Brasinorte155";
    $notas = "";

    for ($i = 1; $i <= 1; $i++) {
        $idrps = 20230430001205+$i;
        $dados = [
            'IdentificacaoRps' => [
                'Numero' => $idrps,
                'Serie' => 'A',
                'Tipo' => 1,
            ],
            'DataEmissao' => date('Y-m-d') . 'T' . date('H:i:s'),
            'NaturezaOperacao' => 1,
            // 1 – Tributação no município
            // 2 - Tributação fora do município
            // 3 - Isenção
            // 4 - Imune
            // 5 – Exigibilidade suspensa por decisão judicial
            // 6 – Exigibilidade suspensa por procedimento administrativo

            //'RegimeEspecialTributacao' => 6, // optional
            // 1 – Microempresa municipal
            // 2 - Estimativa
            // 3 – Sociedade de profissionais
            // 4 – Cooperativa
            // 5 – MEI – Simples Nacional
            // 6 – ME EPP – Simples Nacional
            'OptanteSimplesNacional' => 1, // 1 - Sim 2 - Não
            'IncentivadorCultural' => 1, // 1 - Sim 2 - Não
            'Status' => 1, // 1 – Normal  2 – Cancelado
            'Prestador' => [
                'Cnpj' => '03287545000119',
                'InscricaoMunicipal' => '02615789', // optional
            ],
            'Tomador' => [
                'IdentificacaoTomador' => [
                    'CpfCnpj' => [
                        'Cpf' => '09294692752'
                    ]
                ],
                'RazaoSocial' => 'Bruno Almeida de Magalhães',
                'Endereco' => [
                    'Endereco' => 'Av Demetrio Ribeiro',
                    'Numero' => 425,
                    'Bairro' => 'Chacaras Rio Petrópolis',
                    'CodigoMunicipio' => '3301702',
                    'Uf' => 'RJ'
                ],
            ],
            'Servico' => [
                'ItemListaServico' => '1401', // Primeiros 4 digitos - https://notacarioca.rio.gov.br/files/leis/Resolucao_2617_2010_anexo2.pdf
                'CodigoTributacaoMunicipio' => '140115', // 6 digitos - https://notacarioca.rio.gov.br/files/leis/Resolucao_2617_2010_anexo2.pdf
                'Discriminacao' => 'Serviços de Teste',
                'CodigoMunicipio' => '3301702',
                'Valores' => [
                    'ValorServicos' => 495.32,
                    'IssRetido' => 2, // 1 para ISS Retido - 2 para ISS não Retido,
                ],
            ],
        ];

        $operacao = new GerarNfse();
        $retorno = $operacao->GerarNfse($ambiente, $dados, $certificado, $senha);
        var_dump($retorno);
        return;
        $notas = $notas . $retorno['CNPJ'].' - '. $retorno['RAZAOSOCIAL'].' - '. $retorno['Numero'].'<br/>';
    }


    echo $notas;




}catch (\Exception $ex){

}