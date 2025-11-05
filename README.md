# Arquiteto - Biblioteca de Geração de Código para Laravel

**Rica Soluções Arquiteto** - Geração automática de código Laravel com base em banco de dados MySQL.

[![Packagist](https://img.shields.io/packagist/v/ricasolucoes/arquiteto.svg?label=Packagist&style=flat-square)](https://packagist.org/packages/ricasolucoes/arquiteto)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/ricasolucoes/arquiteto.svg?label=Scrutinizer&style=flat-square)](https://scrutinizer-ci.com/g/ricasolucoes/arquiteto/)
[![Travis](https://img.shields.io/travis/ricasolucoes/arquiteto.svg?label=TravisCI&style=flat-square)](https://travis-ci.org/ricasolucoes/arquiteto)
[![StyleCI](https://styleci.io/repos/arquiteto/shield)](https://styleci.io/repos/arquiteto)
[![License](https://img.shields.io/packagist/l/ricasolucoes/arquiteto.svg?label=License&style=flat-square)](https://github.com/ricasolucoes/arquiteto/blob/master/LICENSE)

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-8.x%20%7C%209.x%20%7C%2010.x-red.svg?style=flat-square" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-7.2%2B%20%7C%208.x-blue.svg?style=flat-square" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-5.7%2B%20%7C%208.x-blue.svg?style=flat-square" alt="MySQL">
</p>

---

## 📚 Índice

- [Introdução](#-introdução)
- [Instalação](docs/instalacao.md)
- [Arquitetura](docs/arquitetura.md)
- [Comandos Disponíveis](docs/comandos.md)
- [Uso Prático](docs/uso-pratico.md)
- [Integração Rica Soluções](docs/integracao.md)
- [Extensão e Customização](docs/extensao.md)
- [Exemplos Reais](docs/exemplos.md)
- [Contribuição](docs/contribuicao.md)

---

## 🎯 Introdução

### O que é a biblioteca Arquiteto?

**Arquiteto** é uma biblioteca Laravel desenvolvida pela **Rica Soluções** que acelera drasticamente o desenvolvimento de aplicações através da **geração automática de código** a partir de bases de dados MySQL existentes ou modelos Eloquent.

A biblioteca implementa um conjunto robusto de comandos Artisan que analisam a estrutura do banco de dados, detectam relacionamentos, e geram automaticamente:

- ✅ **Modelos Eloquent** com fillables, relationships e soft deletes
- ✅ **Migrations** Laravel a partir de tabelas MySQL
- ✅ **Form Requests** com validações baseadas na estrutura da tabela
- ✅ **Filtros Eloquent** para queries complexas
- ✅ **Controllers, Views e Models** de forma integrada

### Objetivo e Filosofia do Projeto

O Arquiteto foi criado com base nos seguintes princípios:

#### 🚀 **Produtividade Acelerada**
Reduz significativamente o tempo gasto escrevendo código repetitivo (boilerplate), permitindo que desenvolvedores foquem na lógica de negócio.

#### 📐 **Padronização de Código**
Garante que toda a equipe siga os mesmos padrões de nomenclatura, estrutura e organização, essenciais em ambientes corporativos.

#### 🔄 **Integração com Legado**
Facilita a modernização de sistemas legados, gerando automaticamente modelos Laravel a partir de estruturas de banco de dados existentes.

#### 🧩 **Arquitetura Limpa**
Promove boas práticas de desenvolvimento com separação clara de responsabilidades e código organizado.

### Benefícios de Uso

| Benefício | Descrição |
|-----------|-----------|
| **⚡ Velocidade** | Gera em segundos o que levaria horas para escrever manualmente |
| **🎯 Precisão** | Detecta automaticamente tipos de dados, constraints e relacionamentos |
| **🔒 Segurança** | Gera validações baseadas na estrutura real do banco de dados |
| **📦 Escalabilidade** | Facilita a expansão de projetos com novos módulos padronizados |
| **👥 Colaboração** | Equipes trabalham com código consistente e previsível |
| **🔧 Manutenibilidade** | Código gerado segue convenções Laravel, facilitando manutenção |

### Contexto no Ecossistema Rica Soluções

O **Arquiteto** é peça fundamental na **stack de desenvolvimento** da Rica Soluções, integrando-se perfeitamente com outras bibliotecas internas:

```
┌─────────────────────────────────────────────────────────────┐
│                   Ecossistema Rica Soluções                  │
├─────────────────────────────────────────────────────────────┤
│  📦 Arquiteto (Geração de Código)                            │
│  📦 Muleta (Ferramentas e Traits Reutilizáveis)             │
│  📦 Support (Parsers e Utilitários)                          │
│  📦 Pedreiro (Gestão de Exceções)                            │
├─────────────────────────────────────────────────────────────┤
│  🌐 APIs REST / GraphQL                                       │
│  🔐 Autenticação e Autorização                               │
│  📊 Dashboards e Relatórios                                  │
│  🛠️  Microserviços Laravel                                    │
└─────────────────────────────────────────────────────────────┘
```

### Casos de Uso Ideais

✅ **Migração de sistemas legados** - Gere modelos Laravel de bases existentes
✅ **Prototipagem rápida** - Crie MVPs e provas de conceito rapidamente
✅ **APIs REST/GraphQL** - Scaffolding de recursos com models e requests
✅ **Microserviços** - Padronize a estrutura de múltiplos serviços
✅ **Refatoração** - Modernize projetos antigos com estrutura Laravel atual

---

## 🚀 Início Rápido

### Instalação

```bash
composer require ricasolucoes/arquiteto
```

### Uso Básico

```bash
# Gerar modelo a partir da tabela 'users'
php artisan arquiteto:migrationFromMysql users

# Gerar migration a partir da tabela
php artisan arquiteto:migrationFromMysql database.users

# Gerar form request com validações
php artisan arquiteto:request users

# Gerar filtro Eloquent
php artisan arquiteto:filter UserFilter
```

---

## 📖 Documentação Completa

### 📚 Guias Principais

- **[Instalação](docs/instalacao.md)** - Requisitos, instalação e configuração inicial
- **[Arquitetura](docs/arquitetura.md)** - Estrutura interna, namespaces e padrões
- **[Comandos](docs/comandos.md)** - Referência completa de todos os comandos
- **[Uso Prático](docs/uso-pratico.md)** - Como usar no dia a dia com exemplos

### 🔧 Guias Avançados

- **[Integração](docs/integracao.md)** - Integração com ecossistema Rica Soluções
- **[Extensão](docs/extensao.md)** - Como customizar e estender funcionalidades
- **[Exemplos](docs/exemplos.md)** - Exemplos reais de projetos da Rica Soluções

### 👥 Colaboração

- **[Contribuição](docs/contribuicao.md)** - Como contribuir com o projeto

---

## 🏗️ Arquitetura Resumida

```
Arquiteto/
├── Console/Commands/          # Comandos de geração de código
│   ├── Generate.php           # Gera controller + model + view
│   ├── GenerateModelFromMySQL.php      # Model a partir do MySQL
│   ├── GenerateMigrationFromMySQL.php  # Migration do MySQL
│   ├── GenerateRequestFromMySQL.php    # Form Request do MySQL
│   └── MakeEloquentFilter.php          # Cria filtros Eloquent
├── Contracts/
│   ├── AbstractGeneratorCommand.php    # Base para geradores
│   └── Traits/
│       └── ManipuleFile.php           # Manipulação de arquivos
├── Facades/
│   └── Arquiteto.php          # Facade Laravel
└── Services/
    └── ArquitetoService.php   # Serviço principal
```

---

## 🎓 Exemplo Completo

### Cenário: Criar estrutura completa para entidade "Product"

```bash
# 1. Gerar modelo Eloquent a partir da tabela products
php artisan arquiteto:migrationFromMysql products

# 2. Gerar form request com validações
php artisan arquiteto:request products

# 3. Gerar filtro para queries avançadas
php artisan arquiteto:filter ProductFilter

# 4. Resultado: estrutura completa gerada
app/
├── Models/
│   └── Product.php            # Com fillables e relationships
├── Http/
│   ├── Requests/
│   │   └── ProductRequest.php # Validações automáticas
│   └── Controllers/
└── ModelFilters/
    └── ProductFilter.php      # Filtros reutilizáveis
```

O modelo gerado automaticamente inclui:

```php
// app/Models/Product.php
namespace Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model {
    use SoftDeletes;

    protected $table = 'products';
    public $timestamps = false;

    public $fillable = [
        'name',        // (varchar(255))
        'description', // (text)
        'price',       // (decimal(10,2))
        'category_id', // (int)
        'created_at',  // (timestamp)
    ];

    /**  One-to-Many Relations  **/
    public function Category() {
        return $this->hasOne('Arquiteto\Category', 'id', 'category_id');
    }

    /**  Many-to-One Relations  **/
    public function Orders() {
        return $this->hasMany('Arquiteto\Order', 'product_id', 'id');
    }
}
```

---

## 🤝 Suporte e Comunidade

- 💬 [Chat no Slack](https://bit.ly/ricasolucoes-slack)
- 📧 [Suporte por Email](mailto:help@ricasolucoes.com.br)
- 🐛 [Reportar Issues](https://github.com/ricasolucoes/arquiteto/issues)
- 📖 [Documentação Completa](https://ricasolucoes.com/packages/arquiteto/)

---

## 📝 Licença

Este software é disponibilizado sob a licença [MIT License](LICENSE).

**© 2008-2025 Rica Soluções** - Alguns direitos reservados.

---

## 🌟 Créditos

Desenvolvido com ❤️ pela equipe **Rica Soluções**.

**Autor Principal:** [Rica Soluções](https://ricasolucoes.com.br)
**Email:** help@ricasolucoes.com.br

---

## 🔗 Links Úteis

- 🏠 [Homepage](https://ricasolucoes.com/packages/arquiteto/)
- 📦 [Packagist](https://packagist.org/packages/ricasolucoes/arquiteto)
- 🐙 [GitHub](https://github.com/ricasolucoes/arquiteto)
- 📚 [Documentação Oficial](https://github.com/ricasolucoes/arquiteto/tree/master/docs)

---

<p align="center">
  <strong>Feito com 🚀 pela Rica Soluções</strong><br>
  Inovação na velocidade da vida.
</p>
