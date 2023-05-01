<?php

namespace Rbtecnet\Phpnfse\Provedores\Saquarema\Operations\Gerar;

use Garden\Schema\Schema;
use Garden\Schema\ValidationException;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class GerarXmlGerar
{
    function GerarXmlGerar(array $dados=[]){
        $data = [
            'EnviarLoteRpsEnvio'=>[
                'LoteRps'=>[
                    '@Id'=>'L'.$dados['NumeroLote'],
                    '@versao'=>'2.02',
                    'NumeroLote'=>$dados['NumeroLote'],
                    'CpfCnpj'=>[
                        'Cnpj'=>$dados['Prestador']['CpfCnpj']['Cnpj'],
                    ],
                    'InscricaoMunicipal'=>$dados['Prestador']['InscricaoMunicipal'],
                    'QuantidadeRps'=>$dados['QuantidadeRps'],
                    'ListaRps'=>[
                        'Rps'=>[
                            'InfDeclaracaoPrestacaoServico'=>[
                                '@Id'=>$dados['NumeroLote'],
                                'Rps'=>[
                                    'IdentificacaoRps'=>[
                                        'Numero'=>$dados['Rps']['IdentificacaoRps']['Numero'],
                                        'Serie'=>$dados['Rps']['IdentificacaoRps']['Serie'],
                                        'Tipo'=>$dados['Rps']['IdentificacaoRps']['Tipo'],
                                    ],
                                    'DataEmissao'=>$dados['Rps']['DataEmissao'],
                                    'Status'=>$dados['Rps']['Status'],
                                ],
                                'Competencia'=>$dados['Competencia'],
                                'Servico'=>$dados['Servico'],
                                'Prestador'=>$dados['Prestador'],
                                'Tomador'=>$dados['Tomador'],
                                'OptanteSimplesNacional'=>$dados['OptanteSimplesNacional'],
                                'IncentivoFiscal'=>$dados['IncentivoFiscal'],
                            ],
                        ],

                    ],
                ],
            ],
        ];
        $encode = new XmlEncoder();
        //valida o array baseado na estrutura
        $estrutura=$this->getSchemaStructure();
        try{
            $schema = Schema::parse($estrutura);
            $schema->validate($data);
        }catch (ValidationException $ve){
            return $ve->getMessage();
        }
        $xml = $encode->encode($data,'xml', ['xml_root_node_name' => 'rootnode', 'remove_empty_tags' => true]);
        $xml = str_replace('<?xml version="1.0"?>', '', $xml);
        $xml = str_replace('<rootnode>', '', $xml);
        $xml = str_replace('</rootnode>', '', $xml);
        $this->addEnvelope($xml);
        return $xml;
    }
    public function getSchemaStructure(): array
    {
        $schema = [
            'EnviarLoteRpsEnvio'=>[
                'LoteRps'=>[
                    '@Id',
                    '@versao',
                    'NumeroLote',
                    'CpfCnpj' => ['Cpf?', 'Cnpj?'],
                    'InscricaoMunicipal',
                    'QuantidadeRps',
                    'ListaRps'=>[
                        'Rps'=>[
                            'InfDeclaracaoPrestacaoServico '=>[
                                '@Id',
                                'Rps'=>[
                                    'IdentificacaoRps'=>[
                                        'Numero',
                                        'Serie',
                                        'Tipo',
                                    ],
                                    'DataEmissao',
                                    'Status',
                                ],
                                'Competencia',
                                'Servico',
                                'Prestador',
                                'Tomador',
                                'OptanteSimplesNacional',
                                'IncentivoFiscal',
                            ],
                        ],

                    ],
                ],
            ],
        ];
        return $schema;
    }
    public function addEnvelope(string &$content)
    {
        $env ="<soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/' xmlns:tem='http://tempuri.org/'>
		<soapenv:Header>
			<tem:cabecalho versao='202'>
				<tem:versaoDados>2.02</tem:versaoDados>
			</tem:cabecalho>
		</soapenv:Header>
		<soapenv:Body>
			<tem:EnviarLoteRpsSincrono>
				<tem:xmlEnvio>
					<![CDATA[
                        PLACEHOLDER
                    ]]>
				</tem:xmlEnvio>
			</tem:EnviarLoteRpsSincrono>
		</soapenv:Body>
	</soapenv:Envelope>";
        $content = str_replace('PLACEHOLDER', $content, $env);
    }





}