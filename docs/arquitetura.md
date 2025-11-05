# 🏗️ Arquitetura e Estrutura Interna

[← Voltar ao Índice](../README.md)

---

## Índice

- [Visão Geral](#visão-geral)
- [Estrutura de Diretórios](#estrutura-de-diretórios)
- [Namespaces e Organização](#namespaces-e-organização)
- [Padrões de Arquitetura](#padrões-de-arquitetura)
- [Principais Classes](#principais-classes)
- [Traits e Helpers](#traits-e-helpers)
- [Service Provider](#service-provider)
- [Fluxo de Execução](#fluxo-de-execução)
- [Integração com Laravel](#integração-com-laravel)

---

## Visão Geral

O **Arquiteto** é estruturado como um **Service Provider Laravel** que utiliza o **padrão de geração de código baseado em templates** e **introspecção de banco de dados**.

### Princípios Arquiteturais

```
┌─────────────────────────────────────────────────────────────┐
│                  ARQUITETURA DO ARQUITETO                    │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌───────────────┐         ┌─────────────────┐             │
│  │  Artisan CLI  │────────▶│  Console        │             │
│  │  Commands     │         │  Commands       │             │
│  └───────────────┘         └─────────────────┘             │
│         │                           │                        │
│         │                           ▼                        │
│         │                  ┌─────────────────┐             │
│         │                  │  Abstract       │             │
│         │                  │  Generator      │             │
│         │                  │  Command        │             │
│         │                  └─────────────────┘             │
│         │                           │                        │
│         │                           ▼                        │
│         │                  ┌─────────────────┐             │
│         │                  │  ManipuleFile   │             │
│         │                  │  Trait          │             │
│         │                  └─────────────────┘             │
│         │                           │                        │
│         ▼                           ▼                        │
│  ┌──────────────────────────────────────────┐              │
│  │     Database Introspection (MySQL)        │              │
│  │     information_schema queries            │              │
│  └──────────────────────────────────────────┘              │
│         │                                                    │
│         ▼                                                    │
│  ┌──────────────────────────────────────────┐              │
│  │     Template Processing (Regex)           │              │
│  └──────────────────────────────────────────┘              │
│         │                                                    │
│         ▼                                                    │
│  ┌──────────────────────────────────────────┐              │
│  │     File Generation (Filesystem)          │              │
│  └──────────────────────────────────────────┘              │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

### Métricas do Projeto

| Métrica | Valor |
|---------|-------|
| Total de Linhas PHP | 2,108 |
| Métodos Públicos | 36 |
| Métodos Privados | 48 |
| Métodos Protegidos | 20 |
| Classes | 12 |
| Traits | 2 |
| Comandos Console | 6 |

---

## Estrutura de Diretórios

### Árvore Completa

```
ricasolucoes/arquiteto/
│
├── src/                                    # Código fonte principal
│   │
│   ├── Arquiteto.php                       # Classe principal (placeholder)
│   ├── ArquitetoProvider.php              # Service Provider Laravel
│   │
│   ├── Console/                            # Comandos Artisan
│   │   └── Commands/
│   │       ├── Generate.php                # Gera controller + model + view
│   │       ├── GenerateModelFromMySQL.php  # Gera model do MySQL
│   │       ├── GenerateMigrationFromMySQL.php # Gera migration do MySQL
│   │       ├── GenerateMigrationFromModel.php # Gera migration do model
│   │       ├── GenerateRequestFromMySQL.php   # Gera request do MySQL
│   │       └── MakeEloquentFilter.php         # Cria filtro Eloquent
│   │
│   ├── Contracts/                          # Abstrações e contratos
│   │   ├── AbstractGeneratorCommand.php    # Base para geradores
│   │   └── Traits/
│   │       ├── ManipuleFile.php           # Manipulação de arquivos
│   │       └── CommandGeneratorTrait.php  # Wrapper do ManipuleFile
│   │
│   ├── Facades/                            # Facades Laravel
│   │   └── Arquiteto.php                   # Facade principal
│   │
│   └── Services/                           # Serviços da aplicação
│       └── ArquitetoService.php           # Serviço principal
│
├── publishes/                              # Assets publicáveis
│   └── config/
│       └── arquiteto.php                   # Arquivo de configuração
│
├── database/                               # Migrações do pacote
│   └── migrations/
│
├── resources/                              # Resources publicáveis
│   ├── views/                              # Views do pacote
│   └── lang/                               # Traduções
│       └── pt_BR/
│
├── routes/                                 # Rotas do pacote (se aplicável)
│
├── tests/                                  # Testes automatizados
│
├── composer.json                           # Dependências Composer
├── README.md                               # Documentação principal
└── LICENSE                                 # Licença MIT
```

---

## Namespaces e Organização

### Estrutura de Namespaces

```php
Arquiteto\
│
├── Arquiteto                               # Classe principal
├── ArquitetoProvider                       # Service Provider
│
├── Console\Commands\                       # Comandos console
│   ├── Generate
│   ├── GenerateModelFromMySQL
│   ├── GenerateMigrationFromMySQL
│   ├── GenerateMigrationFromModel
│   ├── GenerateRequestFromMySQL
│   └── MakeEloquentFilter
│
├── Contracts\                              # Abstrações
│   ├── AbstractGeneratorCommand
│   └── Traits\
│       ├── ManipuleFile
│       └── CommandGeneratorTrait
│
├── Facades\                                # Facades
│   └── Arquiteto
│
└── Services\                               # Serviços
    └── ArquitetoService
```

### Autoloading PSR-4

Configurado em `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "Arquiteto\\": "src/"
        }
    }
}
```

---

## Padrões de Arquitetura

### 1. Service Provider Pattern

O Arquiteto utiliza o **Service Provider Pattern** do Laravel para registro e bootstrap:

```php
// src/ArquitetoProvider.php
class ArquitetoProvider extends ServiceProvider
{
    public function register()
    {
        // Registra singletons no container
        $this->app->singleton('arquiteto', function () {
            return new Arquiteto();
        });

        $this->app->singleton(ArquitetoService::class, function ($app) {
            return new ArquitetoService(Config::get('arquiteto'));
        });
    }

    public function boot()
    {
        // Bootstrap de recursos
        $this->registerDirectories();
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
```

### 2. Facade Pattern

Fornece acesso estático aos serviços:

```php
// src/Facades/Arquiteto.php
class Arquiteto extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'arquiteto';
    }
}

// Uso
Arquiteto::gerarModelo('Product');
```

### 3. Template Method Pattern

`AbstractGeneratorCommand` define estrutura comum:

```php
// src/Contracts/AbstractGeneratorCommand.php
abstract class AbstractGeneratorCommand extends Command
{
    use ManipuleFile;

    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    // Métodos abstratos implementados pelas subclasses
    abstract protected function dispatch();
}
```

### 4. Trait-Based Composition

Reutilização de funcionalidades via traits:

```php
// src/Contracts/Traits/ManipuleFile.php
trait ManipuleFile
{
    protected function getNamespacePath($name) { /* ... */ }
    protected function getClassAndNamespace($name) { /* ... */ }
    protected function getParserClass($nameClass) { /* ... */ }
    // ... mais métodos utilitários
}
```

---

## Principais Classes

### 1. ArquitetoProvider

**Localização:** `/src/ArquitetoProvider.php`

**Responsabilidades:**
- Registrar bindings no container Laravel
- Carregar configurações, views e traduções
- Registrar comandos console automaticamente
- Carregar rotas e migrations do pacote

**Métodos Principais:**

| Método | Descrição |
|--------|-----------|
| `register()` | Registra serviços no container |
| `boot()` | Inicializa recursos após todos os providers carregados |
| `registerDirectories()` | Publica configurações e views |
| `routes()` | Carrega rotas do pacote |
| `provides()` | Lista serviços fornecidos |

**Exemplo de Uso:**

```php
// Registrado automaticamente via composer.json
"extra": {
    "laravel": {
        "providers": [
            "Arquiteto\\ArquitetoProvider"
        ]
    }
}
```

---

### 2. AbstractGeneratorCommand

**Localização:** `/src/Contracts/AbstractGeneratorCommand.php`

**Responsabilidades:**
- Classe base para todos os geradores
- Integração com `Filesystem` para operações de arquivos
- Implementa trait `ManipuleFile`

**Estrutura:**

```php
<?php

namespace Arquiteto\Contracts;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Arquiteto\Contracts\Traits\ManipuleFile;

abstract class AbstractGeneratorCommand extends Command
{
    use ManipuleFile;

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Método abstrato implementado por subclasses
     */
    abstract public function handle();
}
```

---

### 3. GenerateModelFromMySQL

**Localização:** `/src/Console/Commands/GenerateModelFromMySQL.php:1`

**Responsabilidades:**
- Gerar modelos Eloquent a partir de tabelas MySQL
- Detectar relacionamentos automaticamente
- Gerar fillables, soft deletes e timestamps

**Fluxo de Execução:**

```
1. Parse table name argument
2. Query information_schema.tables
3. Iterate matched tables:
   a. Get table fields
   b. Get solo relations (hasOne)
   c. Get multi relations (hasMany)
   d. Generate fillable array
   e. Generate relationship methods
   f. Check for soft deletes
4. Process template with replacements
5. Write file to app/Models/
```

**Queries SQL Utilizadas:**

```sql
-- Listar tabelas
SELECT TABLE_NAME AS name
FROM information_schema.tables
WHERE TABLE_SCHEMA='database' AND TABLE_NAME LIKE 'pattern';

-- Colunas da tabela
SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, COLUMN_TYPE
FROM information_schema.columns
WHERE TABLE_SCHEMA='database' AND TABLE_NAME='table';

-- Relacionamentos One-to-Many
SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA='database' AND TABLE_NAME='table'
AND REFERENCED_TABLE_NAME IS NOT NULL;

-- Relacionamentos Many-to-One (inverso)
SELECT TABLE_NAME, COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE REFERENCED_TABLE_SCHEMA='database'
AND REFERENCED_TABLE_NAME='table';
```

---

### 4. GenerateMigrationFromMySQL

**Localização:** `/src/Console/Commands/GenerateMigrationFromMySQL.php:1`

**Responsabilidades:**
- Gerar migrations Laravel a partir de tabelas MySQL
- Mapear tipos MySQL para métodos Laravel Schema
- Gerar foreign keys e indexes

**Mapeamento de Tipos:**

| MySQL Type | Laravel Schema Method |
|------------|----------------------|
| BIGINT | `bigInteger()` |
| VARCHAR | `string($length)` |
| TEXT | `text()` |
| LONGTEXT | `longText()` |
| INT | `integer()` |
| DECIMAL | `decimal($precision, $scale)` |
| DATETIME | `dateTime()` |
| TIMESTAMP | `timestamp()` |
| ENUM | `enum([$values])` |
| JSON | `json()` |
| BOOLEAN | `boolean()` |

**Template de Migration:**

```php
<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Create#CLASS_NAME#Table extends Migration
{
    public function up()
    {
        Schema::create('#TABLE_NAME#', function (Blueprint $table) {
            $table->increments('id');
            #FIELD_DESCRIPTORS#
        });
        #FOREIGN_KEYS#
    }

    public function down()
    {
        Schema::drop('#TABLE_NAME#');
    }
}
```

---

## Traits e Helpers

### ManipuleFile Trait

**Localização:** `/src/Contracts/Traits/ManipuleFile.php:1`

**Métodos Principais:**

```php
trait ManipuleFile
{
    /**
     * Obtém ou instancia ComposerParser
     * @return ComposerParser
     */
    protected function getComposerParser();

    /**
     * Extrai namespace de um caminho de arquivo
     * @param string $filePath
     * @return string
     */
    protected function getNamespaceFromFilePath($filePath);

    /**
     * Resolve namespace para caminho de arquivo
     * @param string $name
     * @return string
     */
    protected function getNamespacePath($name);

    /**
     * Divide nome completo da classe em partes
     * @param string $name
     * @return array [className, namespace]
     */
    protected function getClassAndNamespace($name);

    /**
     * Encontra diretório de migration para uma classe
     * @param string $name
     * @return string
     */
    protected function getPathForMigration($name);

    /**
     * Parse e valida classe model
     * @param string $nameClass
     * @return ParseModelClass
     */
    protected function getParserClass($nameClass);

    /**
     * Exibe regras de confirmação ao usuário
     * @return void
     */
    protected function commentRules();
}
```

**Exemplo de Uso:**

```php
class MeuGerador extends AbstractGeneratorCommand
{
    public function handle()
    {
        $name = $this->argument('name');

        // Obtém classe e namespace
        [$class, $namespace] = $this->getClassAndNamespace($name);

        // Resolve caminho do arquivo
        $path = $this->getNamespacePath($name);

        // Valida modelo
        $parser = $this->getParserClass($name);

        // ... geração de código
    }
}
```

---

## Service Provider

### Registro de Comandos Automático

O `ArquitetoProvider` usa a trait `ConsoleTools` do pacote Muleta para auto-discovery de comandos:

```php
// src/ArquitetoProvider.php:124
$this->registerCommandFolders([
    base_path('vendor/ricasolucoes/arquiteto/src/Console/Commands')
        => '\Arquiteto\Console\Commands',
]);
```

Isso registra automaticamente todos os comandos em `Console/Commands/` sem necessidade de listá-los manualmente.

### Singleton Bindings

```php
// Binding do serviço principal
$this->app->singleton('arquiteto', function () {
    return new Arquiteto();
});

// Binding do ArquitetoService
$this->app->singleton(ArquitetoService::class, function ($app) {
    return new ArquitetoService(
        \Illuminate\Support\Facades\Config::get('arquiteto')
    );
});
```

### Publicação de Assets

```php
// Configuração
$this->publishes([
    $this->getPublishesPath('config/arquiteto.php')
        => config_path('arquiteto.php'),
], ['config', 'sitec', 'sitec-config']);

// Views
$this->publishes([
    $viewsPath => base_path('resources/views/vendor/arquiteto'),
], ['views', 'sitec', 'sitec-views']);

// Traduções
$this->publishes([
    $this->getResourcesPath('lang')
        => resource_path('lang/vendor/arquiteto')
], ['lang', 'sitec', 'sitec-lang', 'translations']);
```

---

## Fluxo de Execução

### Exemplo: Geração de Modelo MySQL

```
┌─────────────────────────────────────────────────────────┐
│ 1. Usuário Executa Comando                              │
│    $ php artisan arquiteto:migrationFromMysql products  │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│ 2. Laravel Dispatcher                                    │
│    - Resolve classe GenerateModelFromMySQL               │
│    - Injeta Filesystem via construtor                    │
│    - Chama método handle()                               │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│ 3. Parse Arguments                                       │
│    - Extrai database e table name                        │
│    - Valida formato (database.table)                     │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│ 4. Database Introspection                                │
│    - Query information_schema.tables                     │
│    - Query information_schema.columns                    │
│    - Query information_schema.KEY_COLUMN_USAGE           │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│ 5. Confirmação do Usuário                                │
│    - Lista tabelas que serão geradas                     │
│    - Exibe regras e avisos                               │
│    - Aguarda confirmação [yes|no]                        │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│ 6. Template Processing                                   │
│    - Carrega template do modelo                          │
│    - Substitui #CLASS_NAME#                              │
│    - Substitui #TABLE_NAME#                              │
│    - Substitui #FILLABLE#                                │
│    - Substitui #SOLO_RELATIONAL_FUNCTIONS#               │
│    - Substitui #MULTI_RELATIONAL_FUNCTIONS#              │
│    - Substitui #IMPORT_SOFT_DELETE# e #USE_SOFT_DELETE# │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│ 7. File Generation                                       │
│    - Cria diretório app/Models/ se necessário            │
│    - Escreve arquivo Product.php                         │
│    - Exibe mensagem de sucesso                           │
└─────────────────────────────────────────────────────────┘
```

---

## Integração com Laravel

### Container IoC

O Arquiteto integra-se ao container de serviços do Laravel:

```php
// Resolver via container
$service = app(ArquitetoService::class);

// Usar via Facade
Arquiteto::method();

// Injeção de dependência
public function __construct(ArquitetoService $arquiteto) {
    $this->arquiteto = $arquiteto;
}
```

### Artisan Console

Comandos são automaticamente registrados e disponíveis via Artisan:

```bash
php artisan list arquiteto
php artisan arquiteto:migrationFromMysql --help
```

### Filesystem

Utiliza `Illuminate\Filesystem\Filesystem` para todas as operações de arquivo:

```php
$this->files->exists($path);
$this->files->put($path, $content);
$this->files->makeDirectory($path, 0755, true);
```

### Database

Acessa o banco via `DB` facade:

```php
use DB;

$tables = DB::select("SELECT * FROM information_schema.tables WHERE ...");
```

---

## Convenções do Ecossistema Rica Soluções

### Estrutura de Pastas Padrão

```
app/
├── Models/              # Modelos Eloquent
├── Http/
│   ├── Controllers/     # Controllers MVC
│   ├── Requests/        # Form Requests
│   └── Resources/       # API Resources
├── Services/            # Lógica de negócio
├── Repositories/        # Acesso a dados
├── ModelFilters/        # Filtros Eloquent
└── UseCases/            # Casos de uso (Clean Architecture)
```

### Naming Conventions

| Tipo | Convenção | Exemplo |
|------|-----------|---------|
| Model | PascalCase singular | `Product` |
| Table | snake_case singular | `product` |
| Controller | PascalCase + "Controller" | `ProductController` |
| Request | PascalCase + "Request" | `ProductRequest` |
| Filter | PascalCase + "Filter" | `ProductFilter` |
| Migration | snake_case + timestamp | `2024_01_01_create_product_table` |

---

[← Voltar ao Índice](../README.md) | [Próximo: Comandos →](comandos.md)
