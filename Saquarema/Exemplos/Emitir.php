<?php
use Rbtecnet\Phpnfse\Provedores\Saquarema\Operations\Gerar\GerarNfse;
require "vendor/autoload.php";
try {
    //rotina de teste de consulta

    $ambiente = "homologacao";
    $certificado = "c:/certificados/telefonia.pfx";
    $senha = "Brasinorte155";
    $notas = "";

    $numerolote = '20230501008';
    $idrps = $numerolote;
    $dados = [
        'NumeroLote'=>$numerolote,
        'Prestador' => [
            'CpfCnpj'=>[
                'Cnpj' => '03287545000119',
             ],
             'InscricaoMunicipal' => '02615789', // optional
        ],
        'QuantidadeRps'=>'1',
        'Rps'=>[
            'IdentificacaoRps'=>[
                'Numero'=>$idrps,
                'Serie'=>'ABC',
                'Tipo'=>1,
            ],
            'DataEmissao'=>date('Y-m-d'),
            'Status'=>'1',
        ],
        'Competencia'=>date('Y-m-d'),
        'Servico'=>[
                'Valores'=>[
                    'ValorServicos'=>'350.00',
                    'ValorDeducoes'=>'0',
                    'ValorPis'=>'2.28',
                    'ValorCofins'=>'10.50',
                    'ValorInss'=>'0',
                    'ValorIr'=>'0',
                    'ValorCsll'=>'3.50',
                    'ValorIss'=>'17.50',
                    'Aliquota'=>'5',
                    'DescontoIncondicionado'=>'0',
                    'DescontoCondicionado'=>'0',
                ],
                'IssRetido'=>'1',
                'ResponsavelRetencao'=>'1',
                'ItemListaServico'=>'14.02',
                'CodigoTributacaoMunicipio'=>'9512600',
                'Discriminacao'=>'Nota de Teste de Emissão',
                'CodigoMunicipio'=>'3304557',
                'CodigoPais'=>'1058',
                'ExigibilidadeISS'=>'1',
                'MunicipioIncidencia'=>'3304557',
            ],
                'Prestador'=>[
                    'CpfCnpj'=>[
                        'Cnpj'=>'18890963000173',
                    ],
                    'InscricaoMunicipal'=>'12717179',
                ],
                'Tomador'=>[
                    'IdentificacaoTomador'=>[
                        'CpfCnpj'=>[
                            'Cnpj'=>'10473194000104',
                        ],
                    ],
                    'RazaoSocial'=>'LBMS Serviços de Manutenção e Informatica Ltda',
                    'Endereco'=>[
                        'Endereco'=>'Av Demétrio Ribeiro',
                        'Numero'=>'425',
                        'Complemento'=>'Casa 15',
                        'Bairro'=>'Chacaras Rio Petrópolis',
                        'CodigoMunicipio'=>'3301702',
                        'Uf'=>'RJ',
                        'Cep'=>'25230020',
                    ],
                ],
        'OptanteSimplesNacional'=>'2',
        'IncentivoFiscal'=>'2',
    ];
    $operacao = new GerarNfse();
    $retorno = $operacao->GerarNfse($ambiente, $dados, $certificado, $senha);
    var_dump($retorno);
    return;









}catch (\Exception $ex){

}