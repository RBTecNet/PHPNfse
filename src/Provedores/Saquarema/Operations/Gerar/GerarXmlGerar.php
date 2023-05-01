<?php

namespace Rbtecnet\Phpnfse\Provedores\NotaCarioca\Operations\Gerar;

use Garden\Schema\Schema;
use Garden\Schema\ValidationException;
use Rbtecnet\Phpnfse\Provedores\NotaCarioca\Soap;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class GerarXmlGerar
{
    function GerarXmlGerar(array $dados=[]){
        $encode = new XmlEncoder();
        $estrutura = $this->getSchemaStructure();
        $data = [
            'InfRps' => [
                '@xmlns' => 'http://www.abrasf.org.br/ABRASF/arquivos/nfse.xsd',
                '@Id' => $dados['IdentificacaoRps']['Numero'],
                'IdentificacaoRps' => $dados['IdentificacaoRps'],
                'DataEmissao' => $dados['DataEmissao'],
                'NaturezaOperacao' => $dados['NaturezaOperacao'],
                'RegimeEspecialTributacao' => isset($dados['RegimeEspecialTributacao']) ? $dados['RegimeEspecialTributacao'] : null,
                'OptanteSimplesNacional' => $dados['OptanteSimplesNacional'],
                'IncentivadorCultural' => $dados['IncentivadorCultural'],
                'Status' => $dados['Status'],
                'RpsSubstituido' => isset($dados['RpsSubstituido']) ? $dados['RpsSubstituido'] : null,
                'Servico' => [
                    'Valores' => $dados['Servico']['Valores'],
                    'ItemListaServico' => $dados['Servico']['ItemListaServico'],
                    'CodigoTributacaoMunicipio' => $dados['Servico']['CodigoTributacaoMunicipio'],
                    'Discriminacao' => $dados['Servico']['Discriminacao'],
                    'CodigoMunicipio' => $dados['Servico']['CodigoMunicipio'],
                ],
                'Prestador' => $dados['Prestador'],
                'Tomador' => $dados['Tomador'],
                'IntermediarioServico' => isset($dados['IntermediarioServico']) ? $dados['IntermediarioServico'] : null,
                 'ConstrucaoCivil' => isset($dados['ConstrucaoCivil']) ? $dados['ConstrucaoCivil'] : null,
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


    public function getSchemaStructure(): array
    {
        return [
            'InfRps' => [
                'IdentificacaoRps' => ['Numero', 'Serie', 'Tipo'],
                'DataEmissao',
                'NaturezaOperacao',
                'RegimeEspecialTributacao:?',
                'OptanteSimplesNacional',
                'IncentivadorCultural',
                'Status',
                'RpsSubstituido?' => ['Numero', 'Serie', 'Tipo'],
                'Servico' => [
                    'Valores' => [
                        'ValorServicos',
                        'ValorDeducoes?',
                        'ValorPis?',
                        'ValorCofins?',
                        'ValorInss?',
                        'ValorIr?',
                        'ValorCsll?',
                        'IssRetido',
                        'ValorIss?',
                        'OutrasRetencoes?',
                        'Aliquota?',
                        'DescontoIncondicionado?',
                        'DescontoCondicionado?',
                    ],
                    'ItemListaServico',
                    'CodigoTributacaoMunicipio',
                    'Discriminacao',
                    'CodigoMunicipio',
                ],
                'Tomador' => [
                    'IdentificacaoTomador?' => [
                        'CpfCnpj' => ['Cpf?', 'Cnpj?'],
                    ],
                    'RazaoSocial?',
                    'Endereco?' => ['Endereco?', 'Numero?', 'Complemento?', 'Bairro?', 'CodigoMunicipio?', 'Uf?', 'Cep?'],
                ],
                'Prestador' => ['Cnpj', 'InscricaoMunicipal?'],
                'IntermediarioServico?' => [
                    'CpfCnpj' => ['Cpf?', 'Cnpj?'],
                    'RazaoSocial',
                    'InscricaoMunicipal?',
                ],
                'ConstrucaoCivil?' => ['CodigoObra', 'Art'],
            ],
        ];
    }

    public function addEnvelope(string &$content)
    {
        $content = '<GerarNfseEnvio xmlns="http://notacarioca.rio.gov.br/WSNacional/XSD/1/nfse_pcrj_v01.xsd"><Rps>'.$content.'</Rps></GerarNfseEnvio>';
        $env = '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
            <soap:Body>
                <GerarNfseRequest xmlns="http://notacarioca.rio.gov.br/">
                    <inputXML>
                    <![CDATA[
                        PLACEHOLDER
                    ]]>
                    </inputXML>
                </GerarNfseRequest >
            </soap:Body>
        </soap:Envelope>';
        $content = str_replace('PLACEHOLDER', $content, $env);
    }





}