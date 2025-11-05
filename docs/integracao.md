# 🔗 Integração com Ecossistema Rica Soluções

[← Voltar ao Índice](../README.md)

---

## Índice

- [Visão Geral do Ecossistema](#visão-geral-do-ecossistema)
- [Pacotes Relacionados](#pacotes-relacionados)
- [Integração com Muleta](#integração-com-muleta)
- [Integração com Support](#integração-com-support)
- [Integração com Pedreiro](#integração-com-pedreiro)
- [Padrões de Desenvolvimento](#padrões-de-desenvolvimento)
- [Microserviços Laravel](#microserviços-laravel)
- [CI/CD e Versionamento](#cicd-e-versionamento)

---

## Visão Geral do Ecossistema

O **Arquiteto** é parte integrante do ecossistema de bibliotecas Laravel da Rica Soluções, projetado para trabalhar em conjunto com outras ferramentas internas.

### Diagrama do Ecossistema

```
┌───────────────────────────────────────────────────────────────┐
│                 ECOSSISTEMA RICA SOLUÇÕES                      │
├───────────────────────────────────────────────────────────────┤
│                                                                │
│  ┌─────────────────┐      ┌─────────────────┐               │
│  │   ARQUITETO     │─────▶│     MULETA      │               │
│  │ Code Generation │      │ Utility Traits  │               │
│  └─────────────────┘      └─────────────────┘               │
│           │                        │                          │
│           ▼                        ▼                          │
│  ┌─────────────────┐      ┌─────────────────┐               │
│  │    SUPPORT      │      │    PEDREIRO     │               │
│  │ Parsers & Utils │      │  Exceptions     │               │
│  └─────────────────┘      └─────────────────┘               │
│           │                        │                          │
│           └────────────┬───────────┘                          │
│                        │                                      │
│                        ▼                                      │
│  ┌────────────────────────────────────────┐                  │
│  │         APLICAÇÕES LARAVEL              │                  │
│  │  • APIs REST/GraphQL                    │                  │
│  │  • Microserviços                        │                  │
│  │  • Dashboards Administrativos           │                  │
│  │  • Sistemas Corporativos                │                  │
│  └────────────────────────────────────────┘                  │
│                                                                │
└───────────────────────────────────────────────────────────────┘
```

### Propósito de Cada Pacote

| Pacote | Responsabilidade | Uso pelo Arquiteto |
|--------|------------------|-------------------|
| **Arquiteto** | Geração de código | Pacote principal |
| **Muleta** | Traits e ferramentas reutilizáveis | `ConsoleTools` trait para registro de comandos |
| **Support** | Parsers de código e utilitários | `ClassReader`, `ComposerParser`, `ParseModelClass` |
| **Pedreiro** | Gestão de exceções | `SetterGetterException` |

---

## Pacotes Relacionados

### 1. Muleta (sierratecnologia/muleta)

**Descrição:** Biblioteca de traits e utilitários reutilizáveis para Laravel.

**GitHub:** https://github.com/sierratecnologia/muleta

#### Como o Arquiteto Usa

```php
// src/ArquitetoProvider.php
use Muleta\Traits\Providers\ConsoleTools;

class ArquitetoProvider extends ServiceProvider
{
    use ConsoleTools; // Trait do Muleta

    public function register()
    {
        // Método fornecido pelo ConsoleTools
        $this->registerCommandFolders([
            base_path('vendor/ricasolucoes/arquiteto/src/Console/Commands')
                => '\Arquiteto\Console\Commands',
        ]);
    }
}
```

#### Funcionalidades Fornecidas

- **ConsoleTools:** Auto-discovery de comandos console
- **PublishableTrait:** Helpers para publicação de assets
- **PathHelpers:** Métodos para resolução de caminhos

#### Instalar Separadamente

```bash
composer require sierratecnologia/muleta
```

---

### 2. Support (ricasolucoes/support)

**Descrição:** Parsers de código PHP e utilitários para análise de classes.

**Funcionalidades Principais:**

| Classe | Propósito |
|--------|-----------|
| `ClassReader` | Lê e analisa estrutura de classes PHP |
| `ComposerParser` | Parse de composer.json e autoloading PSR-4 |
| `ParseModelClass` | Valida e extrai informações de modelos Eloquent |

#### Como o Arquiteto Usa

```php
// src/Contracts/Traits/ManipuleFile.php
use Support\Patterns\Parser\ClassReader;
use Support\Patterns\Parser\ComposerParser;
use Support\Patterns\Parser\ParseModelClass;

trait ManipuleFile
{
    protected function getComposerParser()
    {
        if (!$this->composerParser) {
            $this->composerParser = resolve(ComposerParser::class);
        }
        return $this->composerParser;
    }

    protected function getParserClass($nameClass): ParseModelClass
    {
        $parserModelClass = new ParseModelClass($nameClass);
        if (!$parserModelClass->typeIs('model')) {
            $this->error("\n\n\tClass nao eh um modelo!\n");
            die;
        }
        return $parserModelClass;
    }
}
```

#### Exemplo de Uso em Comandos Personalizados

```php
use Support\Patterns\Parser\ComposerParser;

class MeuComando extends Command
{
    public function handle()
    {
        $parser = resolve(ComposerParser::class);

        // Obter namespace da aplicação
        $namespace = $parser->getAppNamespace();

        // Obter caminho de arquivo a partir de classe
        $path = $parser->getFilePathFromClass('App\Models\Product');

        // Obter namespace de arquivo
        $ns = $parser->getNamespaceFromFilePath($path);
    }
}
```

---

### 3. Pedreiro (ricasolucoes/pedreiro)

**Descrição:** Biblioteca de gerenciamento e customização de exceções.

#### Como o Arquiteto Usa

```php
// src/Contracts/Traits/ManipuleFile.php
use Pedreiro\Exceptions\SetterGetterException;

trait ManipuleFile
{
    protected function getParserClass($nameClass): ParseModelClass
    {
        $parserModelClass = new ParseModelClass($nameClass);
        if (!$parserModelClass->typeIs('model')) {
            throw new SetterGetterException("Class is not a valid model");
        }
        return $parserModelClass;
    }
}
```

#### Exceções Disponíveis

| Exceção | Uso |
|---------|-----|
| `SetterGetterException` | Erros de acesso a propriedades |
| `FileNotFoundException` | Arquivo não encontrado |
| `InvalidClassException` | Classe inválida ou mal formada |

---

## Integração com Muleta

### Traits Disponíveis

#### ConsoleTools

Fornece métodos para registro automático de comandos:

```php
use Muleta\Traits\Providers\ConsoleTools;

class MeuProvider extends ServiceProvider
{
    use ConsoleTools;

    public function register()
    {
        // Registra todos os comandos em um diretório
        $this->registerCommandFolders([
            __DIR__ . '/Console/Commands' => 'App\Console\Commands',
        ]);
    }
}
```

#### PublishableTrait

Helpers para publicar assets:

```php
use Muleta\Traits\Providers\PublishableTrait;

class MeuProvider extends ServiceProvider
{
    use PublishableTrait;

    public function boot()
    {
        // Publish config
        $this->publishConfig(__DIR__ . '/../config/meuconfig.php', 'meuconfig');

        // Publish views
        $this->publishViews(__DIR__ . '/../resources/views', 'meupackage');
    }
}
```

---

## Integração com Support

### ComposerParser

Analisa `composer.json` e resolve namespaces:

```php
use Support\Patterns\Parser\ComposerParser;

$parser = new ComposerParser();

// Obter namespace base da aplicação
$namespace = $parser->getAppNamespace(); // "App\"

// Resolver classe para arquivo
$path = $parser->getFilePathFromClass('App\Models\Product');
// "/app/Models/Product.php"

// Resolver arquivo para namespace
$ns = $parser->getNamespaceFromFilePath('/app/Services/UserService.php');
// "App\Services"
```

### ClassReader

Lê estrutura de classes PHP:

```php
use Support\Patterns\Parser\ClassReader;

$reader = new ClassReader('/app/Models/Product.php');

// Obter namespace
$namespace = $reader->getNamespace(); // "App\Models"

// Obter nome da classe
$className = $reader->getClassName(); // "Product"

// Obter métodos
$methods = $reader->getMethods(); // ['getId', 'getName', ...]

// Obter propriedades
$properties = $reader->getProperties(); // ['$id', '$name', ...]
```

### ParseModelClass

Valida e extrai informações de modelos:

```php
use Support\Patterns\Parser\ParseModelClass;

$parser = new ParseModelClass('App\Models\Product');

// Verificar se é modelo
$isModel = $parser->typeIs('model'); // true

// Obter tabela
$table = $parser->getTable(); // "products"

// Obter fillable
$fillable = $parser->getFillable(); // ['name', 'price', ...]

// Obter relacionamentos
$relations = $parser->getRelations();
```

---

## Integração com Pedreiro

### Uso de Exceções Personalizadas

```php
use Pedreiro\Exceptions\SetterGetterException;
use Pedreiro\Exceptions\FileNotFoundException;

class MeuComando extends Command
{
    public function handle()
    {
        try {
            $model = $this->getModelClass('Product');
        } catch (FileNotFoundException $e) {
            $this->error("Modelo não encontrado: " . $e->getMessage());
            return 1;
        } catch (SetterGetterException $e) {
            $this->error("Erro ao acessar propriedade: " . $e->getMessage());
            return 1;
        }
    }
}
```

---

## Padrões de Desenvolvimento

### Estrutura de Projetos Rica Soluções

```
meu-projeto-laravel/
├── app/
│   ├── Models/              # Modelos Eloquent (gerados pelo Arquiteto)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/         # Controllers de API
│   │   │   └── Admin/       # Controllers administrativos
│   │   ├── Requests/        # Form Requests (gerados pelo Arquiteto)
│   │   └── Resources/       # API Resources
│   ├── Services/            # Lógica de negócio
│   ├── Repositories/        # Acesso a dados
│   ├── ModelFilters/        # Filtros Eloquent (gerados pelo Arquiteto)
│   └── UseCases/            # Casos de uso
├── config/
│   └── arquiteto.php        # Configuração do Arquiteto
└── composer.json
```

### Naming Conventions

#### Models

```php
// ✅ Correto
namespace App\Models;
class Product extends Model {}

// ❌ Evitar
namespace Support; // Gerado pelo Arquiteto, mudar para App\Models
```

#### Requests

```php
// ✅ Correto
namespace App\Http\Requests;
class ProductRequest extends FormRequest {}
```

#### Filters

```php
// ✅ Correto
namespace App\ModelFilters;
class ProductFilter extends ModelFilter {}
```

---

## Microserviços Laravel

### Cenário: Arquitetura de Microserviços

```
┌─────────────────────────────────────────────────────────────┐
│                    MICROSERVIÇOS                             │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌──────────────┐   ┌──────────────┐   ┌──────────────┐   │
│  │   Products   │   │   Orders     │   │   Users      │   │
│  │   Service    │   │   Service    │   │   Service    │   │
│  │              │   │              │   │              │   │
│  │ ┌──────────┐ │   │ ┌──────────┐ │   │ ┌──────────┐ │   │
│  │ │Arquiteto │ │   │ │Arquiteto │ │   │ │Arquiteto │ │   │
│  │ └──────────┘ │   │ └──────────┘ │   │ └──────────┘ │   │
│  └──────────────┘   └──────────────┘   └──────────────┘   │
│         │                   │                   │           │
│         └───────────────────┴───────────────────┘           │
│                             │                                │
│                    ┌────────▼────────┐                      │
│                    │  Shared MySQL   │                      │
│                    │    Database     │                      │
│                    └─────────────────┘                      │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

### Workflow de Geração em Microserviços

#### Service 1: Products

```bash
cd products-service
composer require ricasolucoes/arquiteto

php artisan arquiteto:migrationFromMysql "shared_db.product*"
php artisan arquiteto:request product
php artisan arquiteto:filter ProductFilter
```

#### Service 2: Orders

```bash
cd orders-service
composer require ricasolucoes/arquiteto

php artisan arquiteto:migrationFromMysql "shared_db.order*"
php artisan arquiteto:request order
php artisan arquiteto:filter OrderFilter
```

### Consistência Entre Microserviços

```bash
# Script para padronizar geração
#!/bin/bash
# generate-microservices.sh

SERVICES="products orders users inventory"

for service in $SERVICES; do
    echo "Generating for $service service..."
    cd $service-service

    composer require ricasolucoes/arquiteto

    php artisan arquiteto:migrationFromMysql "shared_db.${service}*"
    php artisan arquiteto:request ${service}

    cd ..
done
```

---

## CI/CD e Versionamento

### Integração com GitLab CI

```yaml
# .gitlab-ci.yml

stages:
  - install
  - generate
  - test
  - deploy

install:
  stage: install
  script:
    - composer install --no-dev --prefer-dist

generate:
  stage: generate
  script:
    - php artisan arquiteto:migrationFromMysql "mydb.*"
    - git diff --exit-code || (echo "Generated code differs" && exit 1)
  only:
    - master

test:
  stage: test
  script:
    - php artisan test

deploy:
  stage: deploy
  script:
    - ./deploy.sh
  only:
    - master
```

### Versionamento Semântico

O Arquiteto segue **Semantic Versioning**:

```
MAJOR.MINOR.PATCH

0.4.4
│ │ │
│ │ └─ Bug fixes
│ └─── New features (backward compatible)
└───── Breaking changes
```

### Changelog

Mantenha changelog atualizado:

```markdown
# Changelog

## [0.4.4] - 2024-01-15
### Added
- Suporte para ENUM types

### Fixed
- Bug na geração de foreign keys

### Changed
- Melhorias na detecção de relacionamentos
```

---

[← Voltar ao Índice](../README.md) | [Próximo: Extensão →](extensao.md)
