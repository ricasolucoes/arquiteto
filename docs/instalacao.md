# 📦 Instalação do Arquiteto

[← Voltar ao Índice](../README.md)

---

## Índice

- [Requisitos Mínimos](#requisitos-mínimos)
- [Instalação via Composer](#instalação-via-composer)
- [Configuração Inicial](#configuração-inicial)
- [Verificação da Instalação](#verificação-da-instalação)
- [Publicação de Assets](#publicação-de-assets-opcional)
- [Configuração do Banco de Dados](#configuração-do-banco-de-dados)
- [Troubleshooting](#troubleshooting)

---

## Requisitos Mínimos

### Ambiente

| Requisito | Versão Mínima | Versões Testadas |
|-----------|---------------|------------------|
| **PHP** | 7.2 | 7.2, 7.3, 7.4, 8.0, 8.1, 8.2 |
| **Laravel** | 6.x | 6.x, 7.x, 8.x, 9.x, 10.x |
| **Composer** | 2.0 | 2.0+ |
| **MySQL** | 5.7 | 5.7, 8.0+ |

### Extensões PHP Requeridas

As seguintes extensões PHP devem estar habilitadas:

```bash
- php-pdo
- php-pdo_mysql
- php-mbstring
- php-xml
- php-json
```

### Dependências do Composer

O Arquiteto instala automaticamente as seguintes dependências:

```json
{
    "doctrine/orm": ">=2.5",
    "doctrine/dbal": ">=2.5",
    "larapack/doctrine-support": ">=0.1",
    "league/flysystem": ">=1.0.41",
    "sierratecnologia/muleta": "^0.4",
    "symfony/inflector": ">=5.0"
}
```

---

## Instalação via Composer

### Passo 1: Adicionar o Repositório

Para projetos que usam repositórios privados ou internos da Rica Soluções, adicione ao `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/ricasolucoes/arquiteto"
        }
    ]
}
```

### Passo 2: Instalar o Pacote

Execute o comando no terminal:

```bash
composer require ricasolucoes/arquiteto
```

### Passo 3: Auto-discovery (Laravel 5.5+)

O Laravel detecta automaticamente o Service Provider. Você verá a mensagem:

```
Discovered Package: ricasolucoes/arquiteto
```

#### Registro Manual (Laravel < 5.5)

Se estiver usando Laravel anterior a 5.5, registre manualmente em `config/app.php`:

```php
'providers' => [
    // ...
    Arquiteto\ArquitetoProvider::class,
],

'aliases' => [
    // ...
    'Arquiteto' => Arquiteto\Facades\Arquiteto::class,
]
```

---

## Configuração Inicial

### Publicar Arquivos de Configuração

Publique o arquivo de configuração do Arquiteto:

```bash
php artisan vendor:publish --provider="Arquiteto\ArquitetoProvider" --tag="config"
```

Isso criará o arquivo `config/arquiteto.php`:

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Arquiteto Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações gerais da biblioteca Arquiteto
    |
    */

    // Configurações futuras serão adicionadas aqui
];
```

### Publicar Views (Opcional)

Para customizar views internas do Arquiteto:

```bash
php artisan vendor:publish --provider="Arquiteto\ArquitetoProvider" --tag="views"
```

### Publicar Traduções (Opcional)

Para customizar mensagens em português:

```bash
php artisan vendor:publish --provider="Arquiteto\ArquitetoProvider" --tag="lang"
```

### Publicar Todos os Assets

Para publicar tudo de uma vez:

```bash
php artisan vendor:publish --provider="Arquiteto\ArquitetoProvider"
```

---

## Verificação da Instalação

### Listar Comandos Disponíveis

Verifique se os comandos do Arquiteto foram registrados:

```bash
php artisan list arquiteto
```

**Saída esperada:**

```
Available commands:
  arquiteto:filter                Generate Eloquent model filter
  arquiteto:generate              Generate controller, model, and view
  arquiteto:migrationFromModel    Generate migration from Eloquent model
  arquiteto:migrationFromMysql    Generate model/migration from MySQL database
  arquiteto:request               Generate form request from MySQL table
```

### Verificar Dependências

Execute para garantir que todas as dependências foram instaladas:

```bash
composer show ricasolucoes/arquiteto
```

**Saída esperada:**

```
name     : ricasolucoes/arquiteto
descrip. : arquiteto
keywords : ricasolucoes, arquiteto
versions : * 0.4.4
type     : library
license  : MIT
homepage : https://ricasolucoes.com/packages/arquiteto/
source   : [git] https://github.com/ricasolucoes/arquiteto
...
```

---

## Configuração do Banco de Dados

O Arquiteto utiliza o banco de dados configurado no Laravel para realizar operações de introspecção.

### Configurar `.env`

Certifique-se de que seu arquivo `.env` possui as credenciais corretas:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nome_do_banco
DB_USERNAME=usuario
DB_PASSWORD=senha
```

### Permissões Necessárias

O usuário do banco de dados precisa ter permissões de leitura no `information_schema`:

```sql
GRANT SELECT ON information_schema.* TO 'usuario'@'localhost';
FLUSH PRIVILEGES;
```

Isso é necessário para que o Arquiteto possa analisar a estrutura das tabelas.

### Testar Conexão

Teste a conexão executando:

```bash
php artisan migrate:status
```

Se a conexão estiver correta, o comando listará as migrações.

---

## Publicação de Assets (Opcional)

### Publicar Stubs Personalizados

Se você deseja customizar os templates de geração de código, publique os stubs:

```bash
php artisan vendor:publish --provider="Arquiteto\ArquitetoProvider" --tag="stubs"
```

Isso copiará os arquivos `.stub` para:

```
resources/stubs/arquiteto/
├── controller.stub
├── model.stub
├── migration.stub
├── request.stub
└── filter.stub
```

### Customizar Templates

Edite os arquivos `.stub` conforme sua necessidade. Por exemplo, `model.stub`:

```php
<?php

namespace #NAMESPACE#;

use Illuminate\Database\Eloquent\Model;
#IMPORTS#

class #CLASS_NAME# extends Model {
    #TRAITS#

    protected $table = '#TABLE_NAME#';

    public $fillable = #FILLABLE#;

    #RELATIONS#
}
```

---

## Troubleshooting

### Erro: "Class 'Arquiteto\ArquitetoProvider' not found"

**Solução:**

```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Erro: "SQLSTATE[42000]: Access denied for user"

**Causa:** Usuário do banco não tem permissões no `information_schema`.

**Solução:**

```sql
GRANT SELECT ON information_schema.* TO 'seu_usuario'@'localhost';
FLUSH PRIVILEGES;
```

### Comandos não aparecem com `php artisan list`

**Solução:**

```bash
php artisan clear-compiled
composer dump-autoload
php artisan config:clear
```

### Erro: "Target class [ArquitetoService] does not exist"

**Causa:** Service Provider não foi carregado.

**Solução:**

Registre manualmente em `config/app.php`:

```php
'providers' => [
    Arquiteto\ArquitetoProvider::class,
],
```

### Dependência Muleta não encontrada

**Causa:** O pacote `sierratecnologia/muleta` não foi instalado.

**Solução:**

```bash
composer require sierratecnologia/muleta
```

---

## Próximos Passos

Após a instalação bem-sucedida:

1. 📖 Leia a [Arquitetura da Biblioteca](arquitetura.md)
2. 🛠️ Explore os [Comandos Disponíveis](comandos.md)
3. 💻 Veja [Exemplos de Uso Prático](uso-pratico.md)

---

[← Voltar ao Índice](../README.md) | [Próximo: Arquitetura →](arquitetura.md)
