<?php

namespace Rbtecnet\Phpnfse\Provedores\NotaCarioca\Operations\Consultar;

use Garden\Schema\Schema;
use Garden\Schema\ValidationException;
use http\Exception;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class GerarXmlConsulta
{
    function GerarXmlConsulta(array $dados=[]){
        $encode = new XmlEncoder();
        $estrutura = $this->getSchemaStructure();
        $data = [
            'ConsultarNfseEnvio' => [
                '@xmlns' => 'http://www.abrasf.org.br/ABRASF/arquivos/nfse.xsd',
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
        return;
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
        $env = '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
            <soap:Body>
                <ConsultarNfseRequest xmlns="http://notacarioca.rio.gov.br/">
                    <inputXML>
                    <![CDATA[
                        PLACEHOLDER
                    ]]>
                    </inputXML>
                </ConsultarNfseRequest>
            </soap:Body>
        </soap:Envelope>';
        $content = str_replace('PLACEHOLDER', $content, $env);
    }

}