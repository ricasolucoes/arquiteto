# 👥 Guia de Contribuição

[← Voltar ao Índice](../README.md)

---

## Índice

- [Como Contribuir](#como-contribuir)
- [Código de Conduta](#código-de-conduta)
- [Reportando Bugs](#reportando-bugs)
- [Sugerindo Melhorias](#sugerindo-melhorias)
- [Padrões de Desenvolvimento](#padrões-de-desenvolvimento)
- [Processo de Pull Request](#processo-de-pull-request)
- [Testes](#testes)
- [Documentação](#documentação)

---

## Como Contribuir

Agradecemos seu interesse em contribuir com o **Arquiteto**! Existem várias formas de contribuir:

### 🐛 Reportar Bugs

Encontrou um problema? [Abra uma issue](https://github.com/ricasolucoes/arquiteto/issues/new)

### 💡 Sugerir Funcionalidades

Tem uma ideia? [Crie uma feature request](https://github.com/ricasolucoes/arquiteto/issues/new)

### 📝 Melhorar Documentação

Documentação clara é essencial. PRs para melhorias na documentação são muito bem-vindos!

### 🔧 Corrigir Bugs ou Implementar Features

Fork o repositório e envie um Pull Request.

### ⭐ Dar Estrela

Se o Arquiteto foi útil, deixe uma estrela no GitHub!

---

## Código de Conduta

### Nossos Compromissos

No interesse de promover um ambiente aberto e acolhedor, nós, como contribuidores e mantenedores, comprometemo-nos a:

- ✅ Usar linguagem acolhedora e inclusiva
- ✅ Respeitar pontos de vista e experiências diferentes
- ✅ Aceitar críticas construtivas com elegância
- ✅ Focar no que é melhor para a comunidade
- ✅ Mostrar empatia com outros membros da comunidade

### Comportamentos Inaceitáveis

- ❌ Uso de linguagem ou imagens sexualizadas
- ❌ Trolling, comentários insultuosos ou depreciativos
- ❌ Assédio público ou privado
- ❌ Publicar informações privadas de terceiros sem permissão
- ❌ Outras condutas que possam ser consideradas inadequadas

### Aplicação

Instâncias de comportamento abusivo, de assédio ou inaceitável podem ser reportadas para [help@ricasolucoes.com.br](mailto:help@ricasolucoes.com.br).

---

## Reportando Bugs

### Antes de Reportar

1. ✅ Verifique se já não existe uma issue sobre o problema
2. ✅ Teste com a versão mais recente do Arquiteto
3. ✅ Tente reproduzir o problema em um ambiente limpo

### Template de Bug Report

```markdown
## Descrição do Bug
Descrição clara e concisa do problema.

## Passos para Reproduzir
1. Execute '...'
2. Com o argumento '...'
3. Observe o erro '...'

## Comportamento Esperado
O que deveria acontecer.

## Comportamento Atual
O que está acontecendo.

## Ambiente
- OS: [ex: Ubuntu 22.04]
- PHP: [ex: 8.1]
- Laravel: [ex: 10.x]
- Arquiteto: [ex: 0.4.4]

## Logs/Screenshots
Cole logs relevantes ou screenshots.

## Contexto Adicional
Qualquer outra informação relevante.
```

### Exemplo Real

```markdown
## Descrição do Bug
Comando `arquiteto:migrationFromMysql` falha ao gerar modelo para tabela com coluna ENUM.

## Passos para Reproduzir
1. Criar tabela com coluna ENUM: `status ENUM('active', 'inactive')`
2. Executar: `php artisan arquiteto:migrationFromMysql users`
3. Erro: "Undefined index: ENUM"

## Comportamento Esperado
Deveria gerar o modelo com cast apropriado para ENUM.

## Comportamento Atual
Comando falha com erro PHP.

## Ambiente
- OS: Ubuntu 22.04
- PHP: 8.1.10
- Laravel: 10.12
- Arquiteto: 0.4.4

## Logs
```
[2024-01-15 10:30:45] ERROR: Undefined index: ENUM
in GenerateModelFromMySQL.php line 182
```
```

---

## Sugerindo Melhorias

### Template de Feature Request

```markdown
## Descrição da Funcionalidade
Descrição clara da funcionalidade proposta.

## Problema que Resolve
Que problema esta funcionalidade resolve?

## Solução Proposta
Como você imagina que isso funcionaria?

## Alternativas Consideradas
Existem outras formas de resolver o problema?

## Contexto Adicional
Exemplos, mockups, casos de uso, etc.
```

### Exemplo Real

```markdown
## Descrição da Funcionalidade
Adicionar comando para gerar DTOs (Data Transfer Objects).

## Problema que Resolve
Atualmente não há forma automática de gerar DTOs a partir de modelos.
Em projetos com Clean Architecture, DTOs são essenciais.

## Solução Proposta
Novo comando: `php artisan arquiteto:dto Product`

Geraria:
```php
class ProductDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public float $price,
    ) {}

    public static function fromModel(Product $product): self
    {
        return new self(
            id: $product->id,
            name: $product->name,
            price: $product->price,
        );
    }
}
```

## Alternativas Consideradas
- Gerar DTOs manualmente (trabalhoso)
- Usar array associativos (sem type safety)

## Contexto Adicional
Referência: [spatie/laravel-data](https://github.com/spatie/laravel-data)
```

---

## Padrões de Desenvolvimento

### Setup do Ambiente

```bash
# Clone o repositório
git clone https://github.com/ricasolucoes/arquiteto.git
cd arquiteto

# Instale dependências
composer install

# Execute testes
composer test
```

### Estrutura de Branches

```
main/master          # Produção estável
├── develop          # Desenvolvimento
│   ├── feature/*    # Novas funcionalidades
│   ├── bugfix/*     # Correções de bugs
│   └── hotfix/*     # Correções urgentes
└── release/*        # Preparação de releases
```

### Naming Conventions

#### Branches

```bash
# Features
feature/generate-dto-command
feature/support-postgresql

# Bugfixes
bugfix/enum-column-generation
bugfix/foreign-key-detection

# Hotfixes
hotfix/critical-migration-error
```

#### Commits

Seguimos o padrão [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>(<scope>): <description>

[optional body]

[optional footer]
```

**Tipos permitidos:**

| Tipo | Descrição | Exemplo |
|------|-----------|---------|
| `feat` | Nova funcionalidade | `feat(commands): add DTO generator` |
| `fix` | Correção de bug | `fix(mysql): handle ENUM columns` |
| `docs` | Apenas documentação | `docs(readme): update installation steps` |
| `style` | Formatação, espaçamento | `style: fix indentation` |
| `refactor` | Refatoração de código | `refactor(traits): simplify ManipuleFile` |
| `test` | Adicionar/modificar testes | `test(commands): add Generate tests` |
| `chore` | Tarefas de build, etc | `chore: update dependencies` |

**Exemplos:**

```bash
# ✅ Bom
git commit -m "feat(mysql): add support for JSON columns"
git commit -m "fix(migrations): handle nullable foreign keys"
git commit -m "docs(commands): add examples for filter command"

# ❌ Ruim
git commit -m "updates"
git commit -m "fix stuff"
git commit -m "WIP"
```

### Código Style Guide

#### PHP

Seguimos **PSR-12** e padrões Laravel:

```php
<?php

declare(strict_types=1);

namespace Arquiteto\Console\Commands;

use Illuminate\Console\Command;

/**
 * Generate DTO from Eloquent model
 */
class GenerateDto extends Command
{
    /**
     * Command signature
     *
     * @var string
     */
    protected $signature = 'arquiteto:dto {model}';

    /**
     * Command description
     *
     * @var string
     */
    protected $description = 'Generate Data Transfer Object from model';

    /**
     * Execute the command
     *
     * @return int
     */
    public function handle(): int
    {
        $model = $this->argument('model');

        if (!$this->validateModel($model)) {
            $this->error("Invalid model: {$model}");
            return 1;
        }

        $this->generateDto($model);

        $this->info('DTO generated successfully!');
        return 0;
    }

    /**
     * Validate model name
     *
     * @param string $model
     * @return bool
     */
    protected function validateModel(string $model): bool
    {
        return preg_match('/^[A-Z][a-zA-Z0-9]*$/', $model) === 1;
    }
}
```

#### Formatação Automática

```bash
# PHP CS Fixer
composer require --dev friendsofphp/php-cs-fixer

# Configurar .php-cs-fixer.php
<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder);

# Executar
vendor/bin/php-cs-fixer fix
```

---

## Processo de Pull Request

### Checklist Antes de Enviar

- [ ] Código segue PSR-12
- [ ] Testes foram adicionados/atualizados
- [ ] Documentação foi atualizada
- [ ] Commits seguem padrão Conventional Commits
- [ ] Branch está atualizada com `develop`
- [ ] Sem conflitos de merge

### Passo a Passo

#### 1. Fork e Clone

```bash
# Fork no GitHub
# Clone seu fork
git clone https://github.com/SEU_USUARIO/arquiteto.git
cd arquiteto

# Adicione upstream
git remote add upstream https://github.com/ricasolucoes/arquiteto.git
```

#### 2. Criar Branch

```bash
# Atualize develop
git checkout develop
git pull upstream develop

# Crie branch
git checkout -b feature/minha-feature
```

#### 3. Desenvolver

```bash
# Faça suas mudanças
# ...

# Teste
composer test

# Commit
git add .
git commit -m "feat(commands): add my awesome feature"
```

#### 4. Atualizar com Upstream

```bash
# Antes de criar PR, sincronize
git fetch upstream
git rebase upstream/develop
```

#### 5. Push e PR

```bash
# Push para seu fork
git push origin feature/minha-feature

# No GitHub, crie Pull Request de:
# SEU_USUARIO:feature/minha-feature → ricasolucoes:develop
```

### Template de Pull Request

```markdown
## Descrição
Descrição clara das mudanças.

## Tipo de Mudança
- [ ] Bug fix (non-breaking change)
- [ ] New feature (non-breaking change)
- [ ] Breaking change (fix ou feature que quebra compatibilidade)
- [ ] Documentation update

## Como Testar
1. Execute '...'
2. Verifique '...'

## Checklist
- [ ] Código segue style guide
- [ ] Testes passam
- [ ] Documentação atualizada
- [ ] Sem warnings ou erros
```

### Code Review

Seu PR será revisado por mantenedores. Expectativas:

- **Feedback construtivo** será dado
- **Mudanças podem ser solicitadas**
- **Seja receptivo** ao feedback
- **Discussões técnicas** são bem-vindas

---

## Testes

### Executando Testes

```bash
# Todos os testes
composer test

# Testes específicos
vendor/bin/phpunit tests/Unit/GenerateModelTest.php

# Com coverage
vendor/bin/phpunit --coverage-html coverage
```

### Escrevendo Testes

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use Arquiteto\Console\Commands\GenerateDto;

class GenerateDtoTest extends TestCase
{
    /** @test */
    public function it_generates_dto_from_model()
    {
        $this->artisan('arquiteto:dto', ['model' => 'Product'])
             ->assertExitCode(0);

        $this->assertFileExists(app_path('DTOs/ProductDTO.php'));
    }

    /** @test */
    public function it_fails_with_invalid_model_name()
    {
        $this->artisan('arquiteto:dto', ['model' => 'invalid-name'])
             ->assertExitCode(1);
    }
}
```

### Cobertura de Testes

Objetivo: **80%+ de cobertura**

```bash
vendor/bin/phpunit --coverage-text
```

---

## Documentação

### Atualizando Docs

```bash
# Documentação está em /docs
docs/
├── instalacao.md
├── arquitetura.md
├── comandos.md
├── uso-pratico.md
├── integracao.md
├── extensao.md
├── exemplos.md
└── contribuicao.md
```

### Padrão de Documentação

- ✅ Português do Brasil
- ✅ Exemplos de código funcionais
- ✅ Screenshots quando aplicável
- ✅ Links internos relativos
- ✅ Formatação Markdown consistente

---

## Contato

### Equipe

- **Email:** help@ricasolucoes.com.br
- **Slack:** [bit.ly/ricasolucoes-slack](https://bit.ly/ricasolucoes-slack)
- **GitHub Issues:** [github.com/ricasolucoes/arquiteto/issues](https://github.com/ricasolucoes/arquiteto/issues)

### Mantenedores

| Nome | GitHub | Email |
|------|--------|-------|
| Rica Soluções | [@ricasolucoes](https://github.com/ricasolucoes) | help@ricasolucoes.com.br |

---

## Licença

Ao contribuir, você concorda que suas contribuições serão licenciadas sob a **MIT License**.

---

## Agradecimentos

Obrigado por considerar contribuir com o Arquiteto! Sua ajuda torna este projeto melhor para todos. 🙌

---

[← Voltar ao Índice](../README.md)
