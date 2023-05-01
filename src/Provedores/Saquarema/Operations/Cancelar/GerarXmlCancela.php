<?php

namespace Rbtecnet\Phpnfse\Provedores\Saquarema\Operations\Cancelar;

use Garden\Schema\Schema;
use Garden\Schema\ValidationException;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class GerarXmlCancela
{
    function GerarXmlCancela(array $dados=[]){
        $encode = new XmlEncoder();
        $data = [
            'CancelarNfseEnvio' => [
                'Pedido' => [
                    'InfPedidoCancelamento' => [
                        'IdentificacaoNfse' => $dados['IdentificacaoNfse'],
                        'CodigoCancelamento' => $dados['CodigoCancelamento'],
                    ],
                ],
            ],
        ];
        $xml = $encode->encode($data,'xml', ['xml_root_node_name' => 'rootnode', 'remove_empty_tags' => true]);
        $xml = str_replace('<?xml version="1.0"?>', '', $xml);
        $xml = str_replace('<rootnode>', '', $xml);
        $xml = str_replace('</rootnode>', '', $xml);
        $this->addEnvelope($xml);
        return $xml;
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
			<tem:CancelarNfse>
				<tem:xmlEnvio>
					<![CDATA[
                        PLACEHOLDER
                    ]]>
				</tem:xmlEnvio>
			</tem:CancelarNfse>
		</soapenv:Body>
	</soapenv:Envelope>";
        $content = str_replace('PLACEHOLDER', $content, $env);
    }

}