<?php

namespace Rbtecnet\Phpnfse\Provedores\Saquarema\Operations\Consultar;

use Garden\Schema\Schema;
use Garden\Schema\ValidationException;
use http\Exception;
use Rbtecnet\Phpnfse\Provedores\Saquarema\Soap;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class GerarXmlConsulta extends Soap
{
    function GerarXmlConsulta(array $dados=[]){
        $encode = new XmlEncoder();
        $estrutura = $this->getSchemaStructure();
        $data = [
            'ConsultarNfseEnvio' => [
                'Prestador' => $dados['Prestador'],
                'PeriodoEmissao' => $dados['PeriodoEmissao'],
                'Tomador' => $dados['Tomador'],
            ],
        ];
        //valida o array baseado na estrutura
        try{
            $schema = Schema::parse($estrutura);
            $schema->validate($data);
        }catch (ValidationException $ve){
            throw new \Exception(__FILE__.':'.__LINE__.' - '.$ve->getMessage());
        }
        $xml = $encode->encode($data,'xml', ['xml_root_node_name' => 'rootnode', 'remove_empty_tags' => true]);
        $xml = str_replace('<?xml version="1.0"?>', '', $xml);
        $xml = str_replace('<rootnode>', '', $xml);
        $xml = str_replace('</rootnode>', '', $xml);
        $this->addEnvelope($xml);
        return $xml;
    }
    public function getSchemaStructure()
    {
        return [
            'ConsultarNfseEnvio' => [
                'Prestador' => ['Cnpj', 'InscricaoMunicipal'],
                'PeriodoEmissao' => ['DataInicial', 'DataFinal'],
                'Tomador' => [
                    'CpfCnpj' => [
                        'Cpf?',
                        'Cnpj?',
                    ],
                ],
            ],
        ];
    }
    public function addEnvelope(string &$content)
    {
        $env = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
                    <soapenv:Body>
                        <ConsultarNfse xmlns="http://tempuri.org/">
                              <xmlEnvio><![CDATA[ 
                                  PLACEHOLDER
                    ]]>
                    </xmlEnvio>
                        </ConsultarNfse>
                    </soapenv:Body>
                </soapenv:Envelope>';
        $content = str_replace('PLACEHOLDER', $content, $env);
    }

}