<?php

namespace Rbtecnet\Phpnfse\Provedores\NotaCarioca\Operations\Gerar;

use Symfony\Component\Serializer\Encoder\XmlEncoder;

class GerarXmlGerar
{
    function GerarXmlGerar(array $dados=[]){
        $encode = new XmlEncoder();
        $estrutura = $this->getSchemaStructure();
        $data = [
            'InfRps' => [
                '@xmlns' => 'http://www.abrasf.org.br/ABRASF/arquivos/nfse.xsd',
                '@Id' => $this->rps['IdentificacaoRps']['Numero'],
                'IdentificacaoRps' => $this->rps['IdentificacaoRps'],
                'DataEmissao' => $this->rps['DataEmissao'],
                'NaturezaOperacao' => $this->rps['NaturezaOperacao'],
                'RegimeEspecialTributacao' => isset($this->rps['RegimeEspecialTributacao']) ? $this->rps['RegimeEspecialTributacao'] : null,
                'OptanteSimplesNacional' => $this->rps['OptanteSimplesNacional'],
                'IncentivadorCultural' => $this->rps['IncentivadorCultural'],
                'Status' => $this->rps['Status'],
                'RpsSubstituido' => isset($this->rps['RpsSubstituido']) ? $this->rps['RpsSubstituido'] : null,
                'Servico' => [
                    'Valores' => $this->rps['Servico']['Valores'],
                    'ItemListaServico' => $this->rps['Servico']['ItemListaServico'],
                    'CodigoTributacaoMunicipio' => $this->rps['Servico']['CodigoTributacaoMunicipio'],
                    'Discriminacao' => $this->rps['Servico']['Discriminacao'],
                    'CodigoMunicipio' => $this->rps['Servico']['CodigoMunicipio'],
                ],
                'Prestador' => $this->rps['Prestador'],
                'Tomador' => $this->rps['Tomador'],
                'IntermediarioServico' => isset($this->rps['IntermediarioServico']) ? $this->rps['IntermediarioServico'] : null,
                 'ConstrucaoCivil' => isset($this->rps['ConstrucaoCivil']) ? $this->rps['ConstrucaoCivil'] : null,
            ],
        ];




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
}