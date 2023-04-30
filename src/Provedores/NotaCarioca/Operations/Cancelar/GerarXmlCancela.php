<?php

namespace Rbtecnet\Phpnfse\Provedores\NotaCarioca\Operations\Cancelar;

use Garden\Schema\Schema;
use Garden\Schema\ValidationException;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class GerarXmlCancela
{
    function GerarXmlCancela(array $dados=[]){
        $encode = new XmlEncoder();
        $estrutura = $this->getSchemaStructure();

        $data = [
            'CancelarNfseEnvio' => [
                '@xmlns' => 'http://www.abrasf.org.br/ABRASF/arquivos/nfse.xsd',
                'Pedido' => [
                    '@xmlns' => 'http://www.abrasf.org.br/ABRASF/arquivos/nfse.xsd',
                    'InfPedidoCancelamento' => [
                        'IdentificacaoNfse' => $dados['IdentificacaoNfse'],
                        'CodigoCancelamento' => $dados['CodigoCancelamento'],
                    ],
                ],
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
            'CancelarNfseEnvio' => [
                'Pedido' => [
                    'InfPedidoCancelamento' => [
                        'IdentificacaoNfse' => [
                            'Numero',
                            'Cnpj',
                            'InscricaoMunicipal',
                            'CodigoMunicipio',
                        ],
                        'CodigoCancelamento',
                    ],
                ],
            ],
        ];
    }

    public function addEnvelope(string &$content)
    {
        $env = '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
            <soap:Body>
                <CancelarNfseRequest xmlns="http://notacarioca.rio.gov.br/">
                    <inputXML>
                    <![CDATA[
                        PLACEHOLDER
                    ]]>
                    </inputXML>
                </CancelarNfseRequest>
            </soap:Body>
        </soap:Envelope>';
        $content = str_replace('PLACEHOLDER', $content, $env);
    }

}