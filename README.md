# Documentação da API de Acesso de Crachás do IME-USP
## Introdução
Esta API foi desenvolvida em Laravel para fornecer informações de crachás de acesso às portas do prédio da Computação no Instituto de Matemática e Estatística da Universidade de São Paulo (IME-USP). A API foi testada utilizando a biblioteca `GuzzleHttp`.
## Endpoints e Rotas
### 1. Consulta individual de crachá
**Endpoint:** `/api/acesso/individual`
**Tipo de Requisição:** GET
**Parâmetros:**
- `codpes` (obrigatório): Código do funcionário/estudante.
**Headers:**
- `Authorization`: Bearer token de acesso.
**Exemplo de Requisição com GuzzleHttp:**
```php
use GuzzleHttp\Client;
$client = new Client();
$headers = [
'Authorization' => 'Bearer YOUR_API_TOKEN',
];
$response = $client->request('GET', 'https://dados.ime.usp.br/api/acesso/individual', [
'headers' => $headers,
'query' => ['codpes' => '1234567']
]);
$body = (string) $response->getBody();
```
### 2. Verificação de crachá ativo
**Endpoint:** `/api/acesso/ativo`
**Tipo de Requisição:** GET
**Parâmetros:**
- `numserchi` (obrigatório): Número do crachá.
**Headers:**
- `Authorization`: Bearer token de acesso.
**Exemplo de Requisição com GuzzleHttp:**
```php
use GuzzleHttp\Client;
$client = new Client();
$headers = [
'Authorization' => 'Bearer YOUR_API_TOKEN',
];
$response = $client->request('GET', 'https://dados.ime.usp.br/api/acesso/ativo', [
'headers' => $headers,
'query' => ['numserchi' => '123456']
]);
$body = (string) $response->getBody();
```
### 3. Consulta de crachás de alunos de graduação
**Endpoint:** `/api/acesso/lote/grad`
**Tipo de Requisição:** GET
**Parâmetros:**
- `codcur` (obrigatório): Código do curso.
- `anoing` (obrigatório): Ano de ingresso.
**Headers:**
- `Authorization`: Bearer token de acesso.
**Exemplo de Requisição com GuzzleHttp:**
```php
use GuzzleHttp\Client;
$client = new Client();
$headers = [
'Authorization' => 'Bearer YOUR_API_TOKEN',
];
$response = $client->request('GET', 'https://dados.ime.usp.br/api/acesso/lote/grad', [
'headers' => $headers,
'query' => ['codcur' => '45031', 'anoing' => '2020']
]);
$body = (string) $response->getBody();
```
### 4. Consulta de crachás de alunos de pós-graduação
**Endpoint:** `/api/acesso/lote/pos`
**Tipo de Requisição:** GET
**Parâmetros:**
- `codare` (obrigatório): Código da área.
**Headers:**
- `Authorization`: Bearer token de acesso.
**Exemplo de Requisição com GuzzleHttp:**
```php
use GuzzleHttp\Client;
$client = new Client();
$headers = [
'Authorization' => 'Bearer YOUR_API_TOKEN',
];
$response = $client->request('GET', 'https://dados.ime.usp.br/api/acesso/lote/pos', [
'headers' => $headers,
'query' => ['codare' => '45131']
]);
$body = (string) $response->getBody();
```
### 5. Consulta de crachás de docentes
**Endpoint:** `/api/acesso/lote/doc`
**Tipo de Requisição:** GET
**Parâmetros:**
- `codset` (obrigatório): Código do setor.
**Headers:**
- `Authorization`: Bearer token de acesso.
**Exemplo de Requisição com GuzzleHttp:**
```php
use GuzzleHttp\Client;
$client = new Client();
$headers = [
'Authorization' => 'Bearer YOUR_API_TOKEN',
];
$response = $client->request('GET', 'https://dados.ime.usp.br/api/acesso/lote/doc', [
'headers' => $headers,
'query' => ['codset' => '1665']
]);
$body = (string) $response->getBody();
```
### 6. Consulta de crachás de funcionários
**Endpoint:** `/api/acesso/lote/func`
**Tipo de Requisição:** GET
**Headers:**
- `Authorization`: Bearer token de acesso.
**Exemplo de Requisição com GuzzleHttp:**
```php
use GuzzleHttp\Client;
$client = new Client();
$headers = [
'Authorization' => 'Bearer YOUR_API_TOKEN',
];
$response = $client->request('GET', 'https://dados.ime.usp.br/api/acesso/lote/func', [
'headers' => $headers
]);
$body = (string) $response->getBody();
```
## Informações adicionais
### Código dos Cursos de Graduação e Nomes
- 45031 - Matemática - Bacharelado (Integral)
- 45052 - Bacharelado em Ciência da Computação (Integral)
- 45062 - Estatística - Bacharelado (Integral)
- 45042 - Matemática Aplicada - Bacharelado (Integral)
- 45070 - Bacharelado em Matemática Aplicada e Computacional (Noturno)
- 45024 - Matemática - Licenciatura (Matutino)
- 45024 - Matemática - Licenciatura (Noturno)

### Código das Áreas da Pós-Graduação e Nomes
- 45131 - Matemática
- 45132 - Matemática Aplicada
- 45134 - Ciência da Computação
- 45499 - Instituto de Matemática e Estatística
- 45133 - Probabilidade e Estatística
- 45135 - Ensino de Matemática

### Código dos Setores e Nomes
- 1664 - Ciência da Computação
- 1665 - Estatística
- 1666 - Matemática Aplicada
- 1667 - Matemática

