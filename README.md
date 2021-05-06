
# API desafio CPF/CNPJ
> Desenvolver uma API em PHP de CRUD de CPF/CNPJ que deve conter a possibilidade de filtro, reordenação e marcar alguns itens em uma black list.

API que executa o CRUD de Pessoa podendo ser Física ou Jurídica (CPF/CNPJ) pelos end-points (rotas), utilizando:
* [PHP](https://www.php.net/)
* [Symfony](https://symfony.com/releases/5.2)
* [MYSQL](https://www.mysql.com/)
* [Doctrine](https://www.doctrine-project.org/)
* [Docker](https://www.docker.com/)
* [PHP Unit](https://phpunit.de/)
* [Postman](https://www.postman.com/)

### Pré requisitos

Para executar o projeto é necessário ter:
```
Docker Desktop (ou docker tools)
```


### Instalação

Comece clonando o repositório:
```
$ git clone https://github.com/antonio-junior-aj/api-desafio.git
```

Copie o .env-example e renomeie para .env

Acesse a pasta do projeto através do PowerShell e execute o comando
```
docker-compose up --build -d
```

Após criado e iniciado o contêiner execute o comando para abrir um terminal no webserver:
```
docker exec -it api-desafio-webserver sh
```

Dentro do terminal, você estará na pasta `/app`, instale as dependências do projeto com o comando:
```
composer install
```

Após a instalação é necessário executar a migration do banco com o comando:
```
php bin/console doctrine:migrations:migrate
```


### Teste Unitário

Ainda com o terminal aberto execute os comandos para criar o banco de teste e copiar o schema:
```
php bin/console doctrine:migrations:migrate
```

```
php bin/console -e test doctrine:schema:create
```

Para testar a aplicação é utilizado o PHPUnit, através do comando:
```
php bin/phpunit
```

Para testar um método específico:
```
php bin/phpunit --filter {MethodName} tests/PersonControllerTest.php
```

<!-- TABLE OF CONTENTS -->
### Executando a API

Para executar as pode usar um cliente (recomendo postman) e chamar a rota desejada, disponibilizei o arquivo do postman para importar e executar as rotas em:
```
_docs/api-desafio.postman_collection.json
```

<details open="open">
  <summary>Rotas da Aplicação (baseUrl = localhost:8080/)</summary>
  <ol>
    <li>
      <a href="#rota1">
Rota: Para recuperar todos as pessoas, pode ser passado o parâmetro ORDER para voltar ordenado pelo campo solicitado, também o parâmetro PAGE para caso queira uma página específica - padrão id (caso não tenha dados, retornará NULL). Também pode ser passado os parâmetros de busca: id, type, value, blacklist:
      </a>
      <ul>
        <li>
GET (baseUrl + 'persons?ORDER=id&PAGE=1', (request, response) => {});
        </li>
        <li>
            <a href="#built-with">Request</a>
            <br/>
            <pre>
{    
    // SEM NADA ENVIADO recupera direto do banco
}
            </pre>
        </li>
        <li>
            <a href="#built-with">Response</a>
            <br/>
            <pre>
HTTP STATUS: 200
{
    "data": [
        {
            "id": 1 (int),
            "type": "F" (ENUM - Física ou Jurídica),
            "value": "000.000.000-00" (string)
            "blacklist": false (bool),
            "blacklistReason": NULL (text),
            "orderNumber": 1 (int)
        },
        {
            "id": 2 (int),
            "type": "J" (ENUM - Física ou Jurídica),    
            "value": "00.000.000/0000-00" (string)
            "blacklist": true (bool),
            "blacklistReason": "text if blacklist is true" (text),
            "orderNumber": 2 (int)
        }
    ]
}
            </pre>
        </li>
      </ul>
    </li>
    <li>
      <a href="#rota2">
Rota: Para recuperar pessoa pelo ID (caso não recupere, retornará NULL):
      </a>
      <ul>
        <li>
GET (baseUrl + 'persons/:id', (request, response) => {});
        </li>
        <li>
            <a href="#built-with">Request</a>
            <br/>
            <pre>
{    
    "id": 1 (int) // id para recuperar
}
            </pre>
        </li>
        <li>
            <a href="#built-with">Response</a>
            <br/>
            <pre>
HTTP HTTP STATUS: 200
{
    "data": {
        "id": 2 (int),
        "type": "J" (ENUM - Física ou Jurídica),    
        "value": "00.000.000/0000-00" (string)
        "blacklist": true (bool),
        "blackistReason": "text if blacklist is true" (text),
        "orderNumber": 2 (int)
    }
}
            </pre>
        </li>
      </ul>
    </li>
    <li>
      <a href="#rota3">
Rota: Para cadastrar pessoa (CPF ou CNPJ):
      </a>
      <ul>
        <li>
POST (baseUrl + 'persons', (request, response) => {});
        </li>
        <li>
            <a href="#built-with">Request</a>
            <br/>
            <pre>
{
    "type": "F" (ENUM - Física ou Jurídica),
    "value": "000.000.000-00" (string)
}
            </pre>
        </li>
        <li>
            <a href="#built-with">Response</a>
            <br/>
            <pre>
HTTP STATUS: 200
{
    "data": "Pessoa cadastrada"
}
            </pre>
        </li>
      </ul>
    </li>
    <li>
      <a href="#rota4">
Rota: Para editar pessoa pelo ID:
      </a>
      <ul>
        <li>
PUT (baseUrl + 'persons/:id', (request, response) => {});
        </li>
        <li>
            <a href="#built-with">Request</a>
            <br/>
            <pre>
{
    "id": 1 (int),
    "type": "J" (ENUM - Física ou Jurídica),
    "value": "00.000.000/0000-00" (string)
}
            </pre>
        </li>
        <li>
            <a href="#built-with">Response</a>
            <br/>
            <pre>
HTTP STATUS: 200
{
    "data": "Pessoa editada"
}
            </pre>
        </li>
      </ul>
    </li>
    <li>
      <a href="#rota5">
Rota: Para excluir pessoa pelo ID:
      </a>
      <ul>
        <li>
DELETE (baseUrl + 'persons/:id', (request, response) => {});
        </li>
        <li>
            <a href="#built-with">Request</a>
            <br/>
            <pre>
{    
    "id": 1 (int) // id para deleção
}
            </pre>
        </li>
        <li>
            <a href="#built-with">Response</a>
            <br/>
            <pre>
HTTP STATUS: 200
{
    "data": "Pessoa excluída"
}
            </pre>
        </li>
      </ul>
    </li>
    <li>
      <a href="#rota6">
Rota: Para marcar/desmarcar na blacklist pelo ID:
      </a>
      <ul>
        <li>
PUT (baseUrl + 'persons/:id/blacklist', (request, response) => {});
        </li>
        <li>
            <a href="#built-with">Request</a>
            <br/>
            <pre>
{        
    "id": 1 (int),
    "blacklist": true (bool),
    "blacklist_reason": "text if blacklist is true" (text - opcional e caso seja false limpa este capo),
}
            </pre>
        </li>
        <li>
            <a href="#built-with">Response</a>
            <br/>
            <pre>
HTTP STATUS: 200
{
    "data": "Pessoa marcada/desmarcada na blacklist"
}
            </pre>
        </li>
      </ul>
    </li>
    <li>
      <a href="#rota7">
Rota: Para ordenar as pessoas, enviar um array com os ids que devem estar na ordem (no exemplo abaixo o ID 2 fica na posição 1 e o ID 1 fica na posição 2):
      </a>
      <ul>
        <li>
POST (baseUrl + 'persons/reorder', (request, response) => {});
        </li>
        <li>
            <a href="#built-with">Request</a>
            <br/>
            <pre>
{    
    "ids": [2, 1]
}
            </pre>
        </li>
        <li>
            <a href="#built-with">Response</a>
            <br/>
            <pre>
HTTP STATUS: 200
{
    "data": "Pessoas reordenadas"
}
            </pre>
        </li>
      </ul>
    </li>
  </ol>
</details>