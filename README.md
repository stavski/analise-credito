#### Problemática: 
A empresa x realiza a intermediação de contratos de aluguel entre inquilinos e imobiliárias. Uma das funcionalidades que o sistema do x utiliza para isto é a análise de crédito, que analisa informações financeiras do cliente para tomar a decisão de aprovação do contrato de locação.

#### Escopo: 
Deverão ser desenvolvidos no mínimo dois endpoints.
##### * O primeiro deles deverá receber os seguintes dados:
| Dado |Tipo |Formato Recebido  |
|--|--|--|
| Nome | varchar(75) | Texto plano |
| CPF | varchar(14) | xxx.xxx.xxx-xx  |
| Negativado | boolean | true ou false |
| Salário | float | xxxx.yy |
| Limite do cartão | float | xxxx.yy |
| Valor do Aluguel | float | xxxx.yy |
| Rua | varchar(120) | Texto plano |
| Número | int | x |
| Municipio | varchar(75) | texto plano |
| Unidade Federativa | varchar(2) | AA |
| CEP | varchar(9) | xxxxx-xxx |

Após a recepção, o CEP deverá ter sua existência validada junto ao serviço [ViaCEP](https://viacep.com.br/), caso não exista, ou não corresponda a Município e UF informados, retornar um erro para o usuário da API. Para fins de simplificação, não se faz necessário validar Rua e Número, apenas Município e UF.
Após passar pelas validações iniciais, daremos início a análise de crédito, para tal, devemos considerar a seguinte mecânica: 
Toda análise se inicia com 100 (cem) pontos. a cada parâmetro aplicado, em ordem, a pontuação pode ser diminuída uma determinada porcentagem (%), a depender do conjunto de parâmetros e da pontuação final o cliente pode ser Aprovado, Negado ou Derivado para análise manual.
Os parâmetros são os seguintes: 
 1. Caso o valor do aluguel ultrapasse 30% do salário, sua pontuação deve ser decrescida em 18%;
 2. Caso o cliente esteja com seu CPF negativado, sua pontuação deve ser decrescida em 31%.
 3. Caso o limite disponível no cartão do cliente seja menor ou igual ao valor mensal de aluguel, sua pontuação deve ser decrescida em 15%.
 4. Caso o cliente já tenha realizada uma análise de crédito nos últimos 90 dias e esta análise que tenha sido reprovada, sua pontuação deve ser decrescida em 10%.

Após aplicar os parâmetros, caso a pontuação resultante seja decimal, o valor deve ser arredondado para cima, ou seja, 96,123 vira 97.
Em seguida, deverá ser aplicado as seguintes regras:
 1. Caso o cliente tenha sido penalizado pelos parâmetros 1 e 2, este deverá ser Reprovado;
 2. Caso o cliente tenha 30 pontos ou menos, este deve ser Reprovado;
 3. Caso o cliente tenha mais de 30 pontos e menos de 60 pontos, este deverá ser Derivado;
 4. Caso o cliente tenha 60 ou mais, deverá ser Aprovado;

Ao final do processo, deverá ser retornado pelo endpoint, em formato JSON, os seguintes dados: 
* Código de referência da análise;
* Pontuação final do Cliente;
* Resultado do Processamento (Reprovado, Derivado, Aprovado);

##### Alguns payloads e seus resultados esperados
| Negativado | Salário | Limite do Cartão  |  Aluguel  | Reprovado < 90 dias |  Pontuação Final  |   Resultado  |
|--|--|--|--|--|--|--|
| false| 24256,00 | 65000,00 | 4500,00 | false | 100 | Aprovado|
| false| 3950,00 | 1150,00 | 1157,00 | true| 77 | Aprovado |
| true| 2550,00 | 500,00 | 750,00 | true| 53 | Derivado |
| true | 1200,00 | 1200,00 | 500,00 | false | 57 | Reprovado |

##### * O segundo endpoint deverá:
Receber um CPF, em formato padrão (XXX.XXX.XXX-XX), e retornar o resultado da última análise de crédito realizada para o CPF informado.

#### Requisitos Para Aceite:
 1. Deverá ser construída uma API RESTful, utilizando o framework PHP Laravel em sua versão mais recente.  
 2. Deverá ser utilizado o PHP em sua a versão mais recente, preferencialmente conteinerizado em Docker.
 3. Deverá ser utilizado banco de dados MariaDB ou PostgreSQL.
 4. Deverá ser utilizada alguma forma de geração de banco de dados, seja um .sql disponibilizado no Git, ou o uso das Migrations do próprio Laravel. 
 5. O Código deverá ser disponibilizado como um repositório privado do GitHub, compartilhado com o email do solicitante do teste.
 6. O arquivo README.md deverá conter os passos necessários para rodar o projeto em outras máquinas.

#### Requisitos Opcionais: 
Os requisitos a seguir são opcionais, as suas realizações não são obrigatórias, mas contam como pontos positivos na avaliação.
 * 100% de cobertura com testes unitários
 * Documentação em Swagger
 * Documentação em formato de Collection do Postman ou Insomnia
 * API hospedada em uma cloud pública (AWS, GCP, Digital Ocean, Azure, Contabo, e semelhantes)
 * Disponibilizar configuração em ambiente utilizando docker-compose.
 * Ser aderente a [OWASP Top Ten](https://owasp.org/www-project-top-ten/)
 
#### Observações: 
 * Dúvidas a respeito do teste podem ser sanadas via e-mail, com o solicitante do mesmo;
 * Plágio parcial ou total acarreta na desclassificação imediata do candidato;
 * Por favor, não faça um fork deste repositório e não publique sua solução online! 
