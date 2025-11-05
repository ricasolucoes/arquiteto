# 🛠️ Comandos Disponíveis

[← Voltar ao Índice](../README.md)

---

## Índice

- [Visão Geral](#visão-geral)
- [arquiteto:migrationFromMysql](#arquitetomigrationfrommysql)
- [arquiteto:migrationFromMysql (Migration)](#arquitetomigrationfrommysql-migration)
- [arquiteto:request](#arquitetorequest)
- [arquiteto:filter](#arquitetofilter)
- [arquiteto:generate](#arquitetogenerate)
- [arquiteto:migrationFromModel](#arquitetomigrationfrommodel)
- [Comparação de Comandos](#comparação-de-comandos)

---

## Visão Geral

O Arquiteto fornece **6 comandos Artisan** poderosos para geração automática de código:

| Comando | Descrição | Fonte |
|---------|-----------|-------|
| `arquiteto:migrationFromMysql` | Gera modelo Eloquent de tabela MySQL | MySQL table |
| `arquiteto:migrationFromMysql` | Gera migration de tabela MySQL | MySQL table |
| `arquiteto:request` | Gera Form Request com validações | MySQL table |
| `arquiteto:filter` | Cria filtro Eloquent para queries | Nome do filtro |
| `arquiteto:generate` | Gera controller + model + view | Nome do modelo |
| `arquiteto:migrationFromModel` | Gera migration de modelo Eloquent | Eloquent model |

---

## arquiteto:migrationFromMysql

### Descrição

Gera **modelos Eloquent** completos a partir de tabelas MySQL existentes, incluindo:

- ✅ Fillable attributes
- ✅ Relacionamentos (hasOne, hasMany)
- ✅ Soft Deletes (se `deleted_at` existir)
- ✅ Timestamps automáticos
- ✅ Comentários inline com tipos de dados

### Sintaxe

```bash
php artisan arquiteto:migrationFromMysql [database_table]
```

**Argumentos:**

| Argumento | Tipo | Descrição | Exemplo |
|-----------|------|-----------|---------|
| `database_table` | string | Nome qualificado da tabela (database.table) ou apenas table | `meudb.products` ou `products` |

**Suporte a Wildcards:**

```bash
# Gerar para múltiplas tabelas
php artisan arquiteto:migrationFromMysql "meudb.product*"

# Resultado: gera Product, ProductCategory, ProductImage, etc.
```

### Exemplo de Uso

#### Cenário: Gerar modelo a partir da tabela `products`

```bash
php artisan arquiteto:migrationFromMysql products
```

**Output:**

```
Table: mydb.products

==== Please ensure you follow the rules to avoid any problems ====
1) Table names should be singular.  If you think differently, you are wrong.
2) Tables have an auto-increment field named 'id'.
3) The models in RED will be overwritten! (as app/Models/<model>.php)

Are you happy to proceed? [yes|no]:
> yes

** The models have been created **
```

**Arquivo Gerado:** `app/Models/Product.php`

```php
<?php

namespace Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model {

    use SoftDeletes;

    protected $table = 'products';
    public $timestamps = false;

    public $fillable = [
        'name',        // (varchar(255))
        'slug',        // (varchar(255))
        'description', // (text)
        'price',       // (decimal(10,2))
        'stock',       // (int)
        'category_id', // (int)
        'created_at',  // (timestamp)
        'updated_at',  // (timestamp)
        //'deleted_at', // (timestamp)
    ];

    /**  One-to-Many Relations  **/

    public function Category()
    {
        return $this->hasOne('Arquiteto\Category', 'id', 'category_id');
    }

    /**  Many-to-One Relations  **/

    public function OrderItems()
    {
        return $this->hasMany('Arquiteto\OrderItem', 'product_id', 'id');
    }
}
```

### Detecção Automática de Recursos

#### Soft Deletes

Se a tabela contém coluna `deleted_at`, o Arquiteto automaticamente:

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model {
    use SoftDeletes;
    // ...
}
```

#### Relacionamentos One-to-Many (hasOne)

Detecta foreign keys na tabela atual:

```sql
-- Tabela products tem: category_id → categories.id
```

Gera:

```php
public function Category()
{
    return $this->hasOne('Arquiteto\Category', 'id', 'category_id');
}
```

#### Relacionamentos Many-to-One (hasMany)

Detecta foreign keys em outras tabelas apontando para a atual:

```sql
-- Tabela order_items tem: product_id → products.id
```

Gera:

```php
public function OrderItems()
{
    return $this->hasMany('Arquiteto\OrderItem', 'product_id', 'id');
}
```

### Tratamento Especial: Tabela `users`

Quando a tabela é `users` e já existe `app/Models/User.php`, o comando:

1. ✅ Preserva a estrutura existente
2. ✅ Atualiza apenas `$fillable`
3. ✅ Adiciona/atualiza relacionamentos
4. ✅ Remove métodos antigos de relacionamento

### Regras e Convenções

| Regra | Descrição |
|-------|-----------|
| **Nomes singulares** | Tabelas devem ter nomes no singular (ex: `product`, não `products`) |
| **Campo `id`** | Tabelas devem ter campo auto-increment `id` |
| **Sobrescrita** | Modelos existentes serão sobrescritos (backup recomendado) |

### Opções Avançadas

#### Múltiplas Tabelas

```bash
# Gerar modelos para todas as tabelas que começam com "shop_"
php artisan arquiteto:migrationFromMysql "mydb.shop_*"
```

#### Usando Database Padrão

Se omitir o nome do database, usa o configurado em `.env`:

```bash
# Usa DB_DATABASE do .env
php artisan arquiteto:migrationFromMysql products
```

---

## arquiteto:migrationFromMysql (Migration)

### Descrição

Gera **migrations Laravel** a partir de tabelas MySQL existentes, incluindo:

- ✅ Todos os tipos de colunas suportados
- ✅ Constraints (nullable, unique, default)
- ✅ Foreign keys
- ✅ Indexes

### Sintaxe

```bash
php artisan arquiteto:migrationFromMysql [database.table]
```

### Mapeamento de Tipos MySQL → Laravel

| MySQL Type | Laravel Schema | Exemplo |
|------------|----------------|---------|
| `BIGINT` | `bigInteger()` | `$table->bigInteger('user_id')` |
| `INT`, `INTEGER` | `integer()` | `$table->integer('quantity')` |
| `SMALLINT` | `smallInteger()` | `$table->smallInteger('status')` |
| `TINYINT` | `tinyInteger()` | `$table->tinyInteger('active')` |
| `MEDIUMINT` | `mediumInteger()` | `$table->mediumInteger('views')` |
| `VARCHAR(n)` | `string(n)` | `$table->string('name', 255)` |
| `CHAR(n)` | `char(n)` | `$table->char('code', 10)` |
| `TEXT` | `text()` | `$table->text('description')` |
| `MEDIUMTEXT` | `mediumText()` | `$table->mediumText('content')` |
| `LONGTEXT` | `longText()` | `$table->longText('body')` |
| `DECIMAL(p,s)` | `decimal(p,s)` | `$table->decimal('price', 10, 2)` |
| `DOUBLE` | `double(p,s)` | `$table->double('amount', 15, 8)` |
| `FLOAT` | `float()` | `$table->float('rate')` |
| `BOOLEAN` | `boolean()` | `$table->boolean('is_active')` |
| `DATE` | `date()` | `$table->date('birth_date')` |
| `DATETIME` | `dateTime()` | `$table->dateTime('created_at')` |
| `TIMESTAMP` | `timestamp()` | `$table->timestamp('updated_at')` |
| `TIME` | `time()` | `$table->time('start_time')` |
| `ENUM(...)` | `enum([...])` | `$table->enum('status', ['active', 'inactive'])` |
| `JSON` | `json()` | `$table->json('metadata')` |
| `JSONB` | `jsonb()` | `$table->jsonb('data')` |
| `BLOB` | `binary()` | `$table->binary('file')` |
| `LONGBLOB` | (via raw SQL) | `DB::statement('ALTER TABLE...')` |
| `MEDIUMBLOB` | (via raw SQL) | `DB::statement('ALTER TABLE...')` |

### Exemplo de Uso

```bash
php artisan arquiteto:migrationFromMysql mydb.products
```

**Output:**

```
Migration: database/migrations/<date>_create_Product_table.php

==== Please ensure you follow the rules to avoid any problems ====
1) Table names should be singular.  If you think differently, you are wrong.
2) Tables have an auto-increment field named 'id'.
3) Foreign key relations might fail depending on the order you generate the migrations.
4) The models in RED will be overwritten!

Are you happy to proceed? [yes|no]:
> yes

** The migrations have been created **
```

**Arquivo Gerado:** `database/migrations/2024_01_15_120000_create_Product_table.php`

```php
<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            $table->integer('category_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });

        /**  Foreign Key Relations  **/
        $table->index('category_id');
        $table->foreign('category_id')
              ->references('id')
              ->on('categories');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('products');
    }
}
```

### Tratamento de Constraints

#### Nullable

```php
// Column IS_NULLABLE = 'YES'
$table->string('description')->nullable();
```

#### Unique

```php
// Column COLUMN_KEY = 'UNI'
$table->string('email')->unique();
```

#### Default Values

```php
// Column COLUMN_DEFAULT = '0'
$table->integer('stock')->default(0);
```

### Foreign Keys

O comando detecta foreign keys via `information_schema.KEY_COLUMN_USAGE`:

```php
$table->index('category_id');
$table->foreign('category_id')
      ->references('id')
      ->on('categories');
```

### Limitações

⚠️ **LONGBLOB e MEDIUMBLOB** são tratados via `DB::statement()` porque Laravel Schema não suporta nativamente:

```php
DB::statement('ALTER TABLE products ADD thumbnail LONGBLOB');
```

---

## arquiteto:request

### Descrição

Gera **Form Request** com **validações automáticas** baseadas na estrutura da tabela MySQL.

### Sintaxe

```bash
php artisan arquiteto:request [database_table]
```

### Exemplo de Uso

```bash
php artisan arquiteto:request products
```

**Arquivo Gerado:** `app/Http/Requests/ProductRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'category_id' => 'required|integer|exists:categories,id',
        ];
    }
}
```

### Regras de Validação Automáticas

| Estrutura do Banco | Regra de Validação |
|--------------------|--------------------|
| `IS_NULLABLE = 'NO'` | `required` |
| `IS_NULLABLE = 'YES'` | `nullable` |
| `DATA_TYPE = 'varchar'` | `string` |
| `CHARACTER_MAXIMUM_LENGTH = 255` | `max:255` |
| `DATA_TYPE = 'int'` | `integer` |
| `DATA_TYPE = 'decimal'` | `numeric` |
| `COLUMN_KEY = 'UNI'` | `unique:table,column` |
| `COLUMN_NAME LIKE '%email%'` | `email` |
| Foreign Key existe | `exists:referenced_table,column` |

### Uso no Controller

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;

class ProductController extends Controller
{
    public function store(ProductRequest $request)
    {
        // Dados já validados automaticamente
        $product = Product::create($request->validated());

        return response()->json($product, 201);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return response()->json($product);
    }
}
```

---

## arquiteto:filter

### Descrição

Cria **Eloquent Model Filters** para queries complexas e reutilizáveis.

### Sintaxe

```bash
php artisan arquiteto:filter [name]
```

**Argumentos:**

| Argumento | Tipo | Descrição | Exemplo |
|-----------|------|-----------|---------|
| `name` | string | Nome do filtro (sufixo "Filter" é opcional) | `Product` ou `ProductFilter` |

### Exemplo de Uso

```bash
php artisan arquiteto:filter ProductFilter
```

**Arquivo Gerado:** `app/ModelFilters/ProductFilter.php`

```php
<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class ProductFilter extends ModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    /**
     * Filter by name
     *
     * @param string $name
     * @return ProductFilter
     */
    public function name($name)
    {
        return $this->where('name', 'LIKE', "%$name%");
    }

    /**
     * Filter by category
     *
     * @param int $categoryId
     * @return ProductFilter
     */
    public function category($categoryId)
    {
        return $this->where('category_id', $categoryId);
    }

    /**
     * Filter by price range
     *
     * @param array $priceRange ['min' => 10, 'max' => 100]
     * @return ProductFilter
     */
    public function priceRange($priceRange)
    {
        if (isset($priceRange['min'])) {
            $this->where('price', '>=', $priceRange['min']);
        }

        if (isset($priceRange['max'])) {
            $this->where('price', '<=', $priceRange['max']);
        }

        return $this;
    }
}
```

### Configuração

O comando usa configurações de `config/eloquentfilter.php`:

```php
return [
    'namespace' => 'App\\ModelFilters\\',
    // ...
];
```

### Uso no Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use EloquentFilter\Filterable;

class Product extends Model
{
    use Filterable;

    // ...
}
```

### Uso em Queries

```php
// Controller
public function index(Request $request)
{
    $products = Product::filter($request->all())->paginate(20);

    return response()->json($products);
}

// URL: /products?name=laptop&category=1&priceRange[min]=500&priceRange[max]=2000
```

---

## arquiteto:generate

### Descrição

Gera **controller, model e view** de forma integrada para CRUD completo.

### Sintaxe

```bash
php artisan arquiteto:generate [model]
```

### Exemplo de Uso

```bash
php artisan arquiteto:generate Product
```

**Arquivos Gerados:**

1. `app/Models/Product.php`
2. `app/Http/Controllers/Admin/Products.php`
3. `resources/views/admin/products/edit.haml`

### Estrutura do Controller

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class Products extends Controller
{
    public function index()
    {
        $products = Product::paginate(15);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function store(Request $request)
    {
        $product = Product::create($request->all());
        return redirect()->route('admin.products.index');
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->all());
        return redirect()->route('admin.products.index');
    }

    public function destroy($id)
    {
        Product::destroy($id);
        return redirect()->route('admin.products.index');
    }
}
```

### Stubs Utilizados

O comando usa stubs localizados em:

```
stubs/generate/
├── controller.stub
├── model.stub
└── view.stub
```

---

## arquiteto:migrationFromModel

### Descrição

Gera **migrations** a partir de **modelos Eloquent** existentes.

### Sintaxe

```bash
php artisan arquiteto:migrationFromModel [name]
```

### Status

⚠️ **Em Desenvolvimento** - Este comando possui implementação parcial.

---

## Comparação de Comandos

### Quando Usar Cada Comando

| Cenário | Comando Recomendado |
|---------|---------------------|
| Já tenho banco de dados MySQL | `arquiteto:migrationFromMysql` (model) |
| Preciso gerar migrations do MySQL | `arquiteto:migrationFromMysql` (migration) |
| Preciso de validações automáticas | `arquiteto:request` |
| Quero filtros reutilizáveis | `arquiteto:filter` |
| Preciso de CRUD completo rapidamente | `arquiteto:generate` |
| Tenho modelo e quero migration | `arquiteto:migrationFromModel` |

### Workflow Recomendado

**Para Novos Projetos:**

```bash
# 1. Gerar modelos de todo o banco
php artisan arquiteto:migrationFromMysql "mydb.*"

# 2. Gerar requests para validação
php artisan arquiteto:request products
php artisan arquiteto:request categories

# 3. Gerar filtros para queries complexas
php artisan arquiteto:filter ProductFilter
php artisan arquiteto:filter CategoryFilter
```

**Para Migração de Sistemas Legados:**

```bash
# 1. Gerar migrations do banco existente
php artisan arquiteto:migrationFromMysql mydb.users
php artisan arquiteto:migrationFromMysql mydb.orders

# 2. Gerar modelos
php artisan arquiteto:migrationFromMysql users
php artisan arquiteto:migrationFromMysql orders

# 3. Adicionar validações
php artisan arquiteto:request users
php artisan arquiteto:request orders
```

---

[← Voltar ao Índice](../README.md) | [Próximo: Uso Prático →](uso-pratico.md)
