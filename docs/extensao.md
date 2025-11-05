# 🔧 Extensão e Customização

[← Voltar ao Índice](../README.md)

---

## Índice

- [Criando Novos Comandos](#criando-novos-comandos)
- [Estendendo Classes Base](#estendendo-classes-base)
- [Criando Traits Customizados](#criando-traits-customizados)
- [Customizando Templates](#customizando-templates)
- [Hooks e Eventos](#hooks-e-eventos)
- [Integrando com Outros Pacotes](#integrando-com-outros-pacotes)

---

## Criando Novos Comandos

### Estrutura Básica

Para criar um novo gerador que se integra ao Arquiteto:

```php
<?php
// app/Console/Commands/GenerateServiceFromModel.php

namespace App\Console\Commands;

use Arquiteto\Contracts\AbstractGeneratorCommand;
use Illuminate\Filesystem\Filesystem;

class GenerateServiceFromModel extends AbstractGeneratorCommand
{
    /**
     * Assinatura do comando
     */
    protected $signature = 'arquiteto:service {model}';

    /**
     * Descrição do comando
     */
    protected $description = 'Generate service class from Eloquent model';

    /**
     * Construtor
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct($files);
    }

    /**
     * Executa o comando
     */
    public function handle()
    {
        $modelName = $this->argument('model');

        // Usar trait ManipuleFile para operações de arquivo
        [$className, $namespace] = $this->getClassAndNamespace($modelName);

        // Validar se é um modelo
        $parser = $this->getParserClass($modelName);

        // Gerar service
        $this->generateService($className, $namespace);

        $this->info("Service {$className}Service created successfully!");
    }

    /**
     * Gera o service
     */
    protected function generateService($className, $namespace)
    {
        $template = $this->getTemplate();

        // Substituições
        $template = str_replace('#NAMESPACE#', $namespace, $template);
        $template = str_replace('#CLASS_NAME#', $className, $template);
        $template = str_replace('#MODEL_CLASS#', $className, $template);

        // Caminho de destino
        $path = app_path("Services/{$className}Service.php");

        // Criar diretório se não existir
        if (!$this->files->exists(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0755, true);
        }

        // Escrever arquivo
        $this->files->put($path, $template);
    }

    /**
     * Template do service
     */
    protected function getTemplate()
    {
        return <<<'PHP'
<?php

namespace #NAMESPACE#\Services;

use #NAMESPACE#\Models\#MODEL_CLASS#;
use #NAMESPACE#\Repositories\#MODEL_CLASS#Repository;

class #CLASS_NAME#Service
{
    protected $repository;

    public function __construct(#MODEL_CLASS#Repository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll($filters = [])
    {
        return $this->repository->getAll($filters);
    }

    public function getById($id)
    {
        return $this->repository->getById($id);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function update($id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }
}
PHP;
    }
}
```

### Registrar o Comando

#### Opção 1: Auto-discovery (Laravel 5.5+)

Basta colocar em `app/Console/Commands/` que o Laravel registra automaticamente.

#### Opção 2: Registro Manual

```php
<?php
// app/Console/Kernel.php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\GenerateServiceFromModel::class,
    ];

    // ...
}
```

### Uso

```bash
php artisan arquiteto:service Product
```

---

## Estendendo Classes Base

### Estender AbstractGeneratorCommand

Crie uma base personalizada para seus geradores:

```php
<?php
// app/Console/Commands/BaseRicaGenerator.php

namespace App\Console\Commands;

use Arquiteto\Contracts\AbstractGeneratorCommand;

abstract class BaseRicaGenerator extends AbstractGeneratorCommand
{
    /**
     * Namespace padrão da aplicação
     */
    protected function getAppNamespace()
    {
        return 'App';
    }

    /**
     * Validar nome de classe
     */
    protected function validateClassName($name)
    {
        if (!preg_match('/^[A-Z][a-zA-Z0-9]*$/', $name)) {
            $this->error('Nome de classe inválido. Use PascalCase.');
            return false;
        }
        return true;
    }

    /**
     * Confirmar sobrescrita
     */
    protected function confirmOverwrite($path)
    {
        if ($this->files->exists($path)) {
            return $this->confirm("O arquivo {$path} já existe. Sobrescrever?");
        }
        return true;
    }

    /**
     * Adicionar header padrão
     */
    protected function addHeader($content)
    {
        $header = <<<'PHP'
/**
 * Generated by Rica Soluções Arquiteto
 * @generated
 */

PHP;
        return str_replace('<?php', "<?php\n\n{$header}", $content);
    }

    /**
     * Log de geração
     */
    protected function logGeneration($type, $name, $path)
    {
        \Log::info("Arquiteto: Generated {$type}", [
            'name' => $name,
            'path' => $path,
            'user' => auth()->user()->email ?? 'console',
        ]);
    }
}
```

### Usar Base Personalizada

```php
<?php

namespace App\Console\Commands;

class GenerateRepository extends BaseRicaGenerator
{
    protected $signature = 'rica:repository {model}';

    public function handle()
    {
        $modelName = $this->argument('model');

        if (!$this->validateClassName($modelName)) {
            return 1;
        }

        $path = app_path("Repositories/{$modelName}Repository.php");

        if (!$this->confirmOverwrite($path)) {
            return 0;
        }

        // Gerar código
        $content = $this->generateRepository($modelName);
        $content = $this->addHeader($content);

        $this->files->put($path, $content);

        $this->logGeneration('Repository', $modelName, $path);
        $this->info("Repository criado com sucesso!");
    }

    // ...
}
```

---

## Criando Traits Customizados

### Trait para Validações

```php
<?php
// app/Console/Traits/ValidatesInput.php

namespace App\Console\Traits;

trait ValidatesInput
{
    /**
     * Valida nome de tabela
     */
    protected function validateTableName($table)
    {
        if (!preg_match('/^[a-z_]+$/', $table)) {
            $this->error('Nome de tabela deve ser snake_case: ' . $table);
            return false;
        }
        return true;
    }

    /**
     * Valida se tabela existe
     */
    protected function tableExists($table)
    {
        try {
            \DB::table($table)->limit(1)->get();
            return true;
        } catch (\Exception $e) {
            $this->error("Tabela {$table} não existe.");
            return false;
        }
    }

    /**
     * Valida formato database.table
     */
    protected function parseDatabaseTable($input)
    {
        if (str_contains($input, '.')) {
            [$database, $table] = explode('.', $input, 2);
        } else {
            $database = config('database.connections.mysql.database');
            $table = $input;
        }

        return compact('database', 'table');
    }
}
```

### Trait para Formatação

```php
<?php
// app/Console/Traits/FormatsCode.php

namespace App\Console\Traits;

trait FormatsCode
{
    /**
     * Formatar código com PHP-CS-Fixer
     */
    protected function formatCode($path)
    {
        if (!file_exists($path)) {
            return false;
        }

        // Usar php-cs-fixer se disponível
        if ($this->commandExists('php-cs-fixer')) {
            exec("php-cs-fixer fix {$path} --rules=@PSR12");
            $this->info("Código formatado: {$path}");
        }

        return true;
    }

    /**
     * Verificar se comando existe
     */
    protected function commandExists($command)
    {
        $return = shell_exec(sprintf("which %s", escapeshellarg($command)));
        return !empty($return);
    }

    /**
     * Adicionar imports automaticamente
     */
    protected function addImport($content, $import)
    {
        // Encontrar linha após namespace
        $lines = explode("\n", $content);
        $insertAt = 0;

        foreach ($lines as $i => $line) {
            if (str_starts_with(trim($line), 'namespace ')) {
                $insertAt = $i + 1;
                break;
            }
        }

        // Inserir use statement
        array_splice($lines, $insertAt, 0, "\nuse {$import};");

        return implode("\n", $lines);
    }
}
```

### Usar Traits Customizados

```php
<?php

namespace App\Console\Commands;

use App\Console\Traits\ValidatesInput;
use App\Console\Traits\FormatsCode;
use Arquiteto\Contracts\AbstractGeneratorCommand;

class MeuComando extends AbstractGeneratorCommand
{
    use ValidatesInput, FormatsCode;

    public function handle()
    {
        $table = $this->argument('table');

        // Validar input
        if (!$this->validateTableName($table)) {
            return 1;
        }

        if (!$this->tableExists($table)) {
            return 1;
        }

        // Parse database.table
        extract($this->parseDatabaseTable($table));

        // Gerar código
        $path = $this->generate($database, $table);

        // Formatar código
        $this->formatCode($path);
    }
}
```

---

## Customizando Templates

### Publicar Stubs

```bash
php artisan vendor:publish --provider="Arquiteto\ArquitetoProvider" --tag="stubs"
```

### Localização dos Stubs

Após publicação:

```
resources/stubs/arquiteto/
├── model.stub
├── migration.stub
├── controller.stub
├── request.stub
└── filter.stub
```

### Personalizar Template de Model

```php
// resources/stubs/arquiteto/model.stub

<?php

namespace #NAMESPACE#;

use Illuminate\Database\Eloquent\Model;
#IMPORTS#

/**
 * #CLASS_NAME# Model
 *
 * @property int $id
 * @generated by Rica Soluções Arquiteto
 */
class #CLASS_NAME# extends Model
{
    #TRAITS#

    /**
     * Table name
     *
     * @var string
     */
    protected $table = '#TABLE_NAME#';

    /**
     * Mass assignable attributes
     *
     * @var array
     */
    protected $fillable = #FILLABLE#;

    /**
     * Attribute casting
     *
     * @var array
     */
    protected $casts = #CASTS#;

    #RELATIONS#

    #SCOPES#
}
```

### Usar Stub Customizado

```php
<?php

class MeuGerador extends AbstractGeneratorCommand
{
    protected function getStubPath($name)
    {
        // Priorizar stub customizado
        $customPath = resource_path("stubs/arquiteto/{$name}.stub");

        if ($this->files->exists($customPath)) {
            return $customPath;
        }

        // Fallback para stub padrão
        return __DIR__ . "/../../stubs/{$name}.stub";
    }

    protected function loadStub($name)
    {
        return $this->files->get($this->getStubPath($name));
    }
}
```

---

## Hooks e Eventos

### Criar Sistema de Hooks

```php
<?php
// app/Services/ArquitetoHooks.php

namespace App\Services;

class ArquitetoHooks
{
    protected $hooks = [];

    /**
     * Registrar hook
     */
    public function register($event, callable $callback)
    {
        if (!isset($this->hooks[$event])) {
            $this->hooks[$event] = [];
        }

        $this->hooks[$event][] = $callback;
    }

    /**
     * Executar hooks
     */
    public function fire($event, $data = [])
    {
        if (!isset($this->hooks[$event])) {
            return $data;
        }

        foreach ($this->hooks[$event] as $callback) {
            $data = $callback($data);
        }

        return $data;
    }
}
```

### Integrar Hooks nos Geradores

```php
<?php

class GenerateModelWithHooks extends AbstractGeneratorCommand
{
    protected $hooks;

    public function __construct(Filesystem $files, ArquitetoHooks $hooks)
    {
        parent::__construct($files);
        $this->hooks = $hooks;
    }

    public function handle()
    {
        $modelName = $this->argument('model');

        // Hook: antes de gerar
        $modelName = $this->hooks->fire('before_generate_model', $modelName);

        // Gerar modelo
        $content = $this->generateModel($modelName);

        // Hook: modificar conteúdo gerado
        $content = $this->hooks->fire('model_generated', [
            'name' => $modelName,
            'content' => $content,
        ])['content'];

        // Salvar arquivo
        $path = $this->saveModel($modelName, $content);

        // Hook: após salvar
        $this->hooks->fire('model_saved', [
            'name' => $modelName,
            'path' => $path,
        ]);
    }
}
```

### Registrar Hooks Customizados

```php
<?php
// app/Providers/ArquitetoServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ArquitetoHooks;

class ArquitetoServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $hooks = $this->app->make(ArquitetoHooks::class);

        // Adicionar timestamp header em modelos gerados
        $hooks->register('model_generated', function ($data) {
            $data['content'] = str_replace(
                '<?php',
                "<?php\n\n// Generated at: " . now()->toDateTimeString(),
                $data['content']
            );
            return $data;
        });

        // Log quando modelo é salvo
        $hooks->register('model_saved', function ($data) {
            \Log::info("Modelo {$data['name']} gerado em {$data['path']}");
            return $data;
        });

        // Adicionar trait HasUuid automaticamente
        $hooks->register('model_generated', function ($data) {
            $data['content'] = str_replace(
                'use SoftDeletes;',
                "use SoftDeletes, HasUuid;",
                $data['content']
            );
            return $data;
        });
    }
}
```

---

## Integrando com Outros Pacotes

### Integração com Laravel IDE Helper

```php
<?php

namespace App\Console\Commands;

use Arquiteto\Contracts\AbstractGeneratorCommand;

class GenerateWithIdeHelper extends AbstractGeneratorCommand
{
    public function handle()
    {
        // Gerar modelos
        $this->call('arquiteto:migrationFromMysql', [
            'database_table' => $this->argument('table'),
        ]);

        // Regenerar IDE Helper
        if (class_exists(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class)) {
            $this->info('Regenerating IDE Helper...');
            $this->call('ide-helper:models', [
                '--write' => true,
            ]);
        }
    }
}
```

### Integração com Prettier/PHP-CS-Fixer

```php
<?php

trait FormatsGeneratedCode
{
    protected function formatWithPrettier($path)
    {
        if (file_exists('node_modules/.bin/prettier')) {
            exec("./node_modules/.bin/prettier --write {$path}");
        }
    }

    protected function formatWithPhpCsFixer($path)
    {
        if (file_exists('vendor/bin/php-cs-fixer')) {
            exec("./vendor/bin/php-cs-fixer fix {$path}");
        }
    }

    public function handle()
    {
        // Gerar código
        $path = $this->generate();

        // Formatar
        $this->formatWithPhpCsFixer($path);
        $this->formatWithPrettier($path);
    }
}
```

---

[← Voltar ao Índice](../README.md) | [Próximo: Exemplos →](exemplos.md)
