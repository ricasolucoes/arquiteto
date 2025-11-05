# 💻 Uso Prático

[← Voltar ao Índice](../README.md)

---

## Índice

- [Workflow Completo](#workflow-completo)
- [Criando um CRUD Completo](#criando-um-crud-completo)
- [Migrando Sistema Legado](#migrando-sistema-legado)
- [Desenvolvendo API REST](#desenvolvendo-api-rest)
- [Trabalhando com Relacionamentos](#trabalhando-com-relacionamentos)
- [Customizando Código Gerado](#customizando-código-gerado)
- [Boas Práticas](#boas-práticas)
- [Dicas e Truques](#dicas-e-truques)

---

## Workflow Completo

### Cenário: E-commerce Product Management

Vamos criar um módulo completo de gerenciamento de produtos com categorias e imagens.

#### Passo 1: Preparar Banco de Dados

```sql
-- Criação das tabelas
CREATE TABLE category (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

CREATE TABLE product (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (category_id) REFERENCES category(id) ON DELETE CASCADE
);

CREATE TABLE product_image (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    path VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE
);
```

#### Passo 2: Gerar Modelos Eloquent

```bash
# Gerar todos os modelos de uma vez
php artisan arquiteto:migrationFromMysql "ecommerce.*"
```

**Output:**

```
Table: ecommerce.category
Table: ecommerce.product
Table: ecommerce.product_image

==== Please ensure you follow the rules to avoid any problems ====
1) Table names should be singular...
2) Tables have an auto-increment field named 'id'...

Are you happy to proceed? [yes|no]:
> yes

** The models have been created **
```

#### Passo 3: Gerar Form Requests

```bash
php artisan arquiteto:request category
php artisan arquiteto:request product
php artisan arquiteto:request product_image
```

**Resultado:** Requests criados em `app/Http/Requests/`

#### Passo 4: Gerar Filtros

```bash
php artisan arquiteto:filter CategoryFilter
php artisan arquiteto:filter ProductFilter
```

#### Passo 5: Criar Controllers Manualmente

```php
<?php
// app/Http/Controllers/Api/ProductController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::filter($request->all())
            ->with(['category', 'productImages'])
            ->paginate(20);

        return response()->json($products);
    }

    public function store(ProductRequest $request)
    {
        $product = Product::create($request->validated());

        return response()->json($product, 201);
    }

    public function show(Product $product)
    {
        $product->load(['category', 'productImages']);

        return response()->json($product);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return response()->json($product);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(null, 204);
    }
}
```

#### Passo 6: Definir Rotas

```php
<?php
// routes/api.php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;

Route::prefix('v1')->group(function () {
    Route::apiResource('products', ProductController::class);
    Route::apiResource('categories', CategoryController::class);
});
```

#### Passo 7: Testar API

```bash
# Listar produtos com filtros
curl -X GET "http://localhost/api/v1/products?name=laptop&category=1&priceRange[min]=500"

# Criar produto
curl -X POST "http://localhost/api/v1/products" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Notebook Dell",
    "slug": "notebook-dell",
    "description": "Notebook de alta performance",
    "price": 3500.00,
    "stock": 10,
    "category_id": 1
  }'
```

---

## Criando um CRUD Completo

### Exemplo: Gerenciamento de Blog

#### Estrutura do Banco

```sql
CREATE TABLE post (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content LONGTEXT NOT NULL,
    excerpt TEXT,
    featured_image VARCHAR(255),
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES user(id)
);
```

#### Gerar Estrutura

```bash
# 1. Modelo
php artisan arquiteto:migrationFromMysql post

# 2. Request
php artisan arquiteto:request post

# 3. Filter
php artisan arquiteto:filter PostFilter
```

#### Personalizar Modelo Gerado

```php
<?php
// app/Models/Post.php (gerado automaticamente)

namespace Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use EloquentFilter\Filterable;

class Post extends Model {
    use SoftDeletes, Filterable;

    protected $table = 'post';
    public $timestamps = true; // Modificar manualmente se necessário

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'status',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    // Relacionamentos gerados automaticamente
    public function User()
    {
        return $this->hasOne('Arquiteto\User', 'id', 'user_id');
    }

    // Adicionar manualmente após geração
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }
}
```

#### Personalizar Filter

```php
<?php
// app/ModelFilters/PostFilter.php (adicionar após geração)

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class PostFilter extends ModelFilter
{
    public $relations = [];

    public function title($title)
    {
        return $this->where('title', 'LIKE', "%$title%");
    }

    public function status($status)
    {
        return $this->where('status', $status);
    }

    public function author($userId)
    {
        return $this->where('user_id', $userId);
    }

    public function published()
    {
        return $this->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    public function dateRange($dates)
    {
        if (isset($dates['from'])) {
            $this->where('created_at', '>=', $dates['from']);
        }

        if (isset($dates['to'])) {
            $this->where('created_at', '<=', $dates['to']);
        }

        return $this;
    }
}
```

---

## Migrando Sistema Legado

### Cenário: Sistema PHP Antigo com MySQL

#### Situação Inicial

- Sistema legado em PHP puro
- Banco de dados MySQL com 50+ tabelas
- Sem migrations nem ORM
- Documentação inexistente

#### Estratégia de Migração

##### Fase 1: Análise e Inventário

```bash
# Listar todas as tabelas
mysql -u root -p -e "SHOW TABLES FROM legacy_db"

# Exportar estrutura para análise
mysqldump -u root -p --no-data legacy_db > legacy_structure.sql
```

##### Fase 2: Gerar Models e Migrations

```bash
# Criar projeto Laravel novo
composer create-project laravel/laravel new-system
cd new-system

# Instalar Arquiteto
composer require ricasolucoes/arquiteto

# Configurar .env para apontar ao banco legado
DB_DATABASE=legacy_db

# Gerar todos os modelos
php artisan arquiteto:migrationFromMysql "legacy_db.*"

# Gerar todas as migrations
php artisan arquiteto:migrationFromMysql "legacy_db.*"
```

##### Fase 3: Ajustar Código Gerado

```php
<?php
// Exemplo: Ajustar relacionamento gerado

// ANTES (gerado automaticamente)
class Order extends Model {
    public function Customer() {
        return $this->hasOne('Arquiteto\Customer', 'id', 'customer_id');
    }
}

// DEPOIS (ajustado manualmente)
class Order extends Model {
    public function customer() { // lowercase para seguir convenção Laravel
        return $this->belongsTo(Customer::class); // belongsTo ao invés de hasOne
    }

    // Adicionar relacionamento inverso em Customer
}
```

##### Fase 4: Criar Seeds a Partir dos Dados

```php
<?php
// database/seeders/LegacyDataSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegacyDataSeeder extends Seeder
{
    public function run()
    {
        // Desabilitar foreign keys temporariamente
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Copiar dados de tabelas sem dependências
        $this->seedUsers();
        $this->seedCategories();

        // Copiar dados com dependências
        $this->seedOrders();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function seedUsers()
    {
        DB::connection('legacy')->table('users')->orderBy('id')->chunk(100, function ($users) {
            foreach ($users as $user) {
                \App\Models\User::create([
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    // ... mapeamento de campos
                ]);
            }
        });
    }
}
```

##### Fase 5: Validação e Testes

```bash
# Comparar contagens de registros
mysql -u root -p -e "
SELECT
    'users' as table_name, COUNT(*) as legacy_count
FROM legacy_db.users
UNION ALL
SELECT 'orders', COUNT(*) FROM legacy_db.orders;
"

# vs

php artisan tinker
>>> DB::table('users')->count();
>>> DB::table('orders')->count();
```

---

## Desenvolvendo API REST

### Cenário: API de Gerenciamento de Tarefas

#### Estrutura do Banco

```sql
CREATE TABLE task (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    due_date DATE,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES user(id)
);
```

#### Gerar Estrutura Base

```bash
php artisan arquiteto:migrationFromMysql task
php artisan arquiteto:request task
php artisan arquiteto:filter TaskFilter
```

#### Personalizar Request

```php
<?php
// app/Http/Requests/TaskRequest.php (adicionar após geração)

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'due_date' => 'nullable|date|after:today',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'O título da tarefa é obrigatório.',
            'priority.in' => 'A prioridade deve ser: low, medium ou high.',
            'due_date.after' => 'A data de vencimento deve ser futura.',
        ];
    }
}
```

#### Criar API Resource

```php
<?php
// app/Http/Resources/TaskResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => $this->status,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'completed_at' => $this->completed_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'user' => [
                'id' => $this->user_id,
                'name' => $this->user->name ?? null,
            ],
        ];
    }
}
```

#### Controller da API

```php
<?php
// app/Http/Controllers/Api/TaskController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $tasks = Task::filter($request->all())
            ->where('user_id', $request->user()->id)
            ->with('user')
            ->paginate(20);

        return TaskResource::collection($tasks);
    }

    public function store(TaskRequest $request)
    {
        $task = Task::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return new TaskResource($task);
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);

        return new TaskResource($task);
    }

    public function update(TaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $task->update($request->validated());

        return new TaskResource($task);
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(null, 204);
    }

    public function complete(Task $task)
    {
        $this->authorize('update', $task);

        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return new TaskResource($task);
    }
}
```

---

## Trabalhando com Relacionamentos

### Ajustando Relacionamentos Gerados

O Arquiteto gera relacionamentos automaticamente, mas você pode precisar ajustá-los:

#### Exemplo: Relacionamento Many-to-Many

```php
<?php
// Gerado automaticamente (não funciona para many-to-many)
class Product extends Model {
    public function Tags() {
        // ...
    }
}

// Ajustar manualmente
class Product extends Model {
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'product_tag');
    }
}

class Tag extends Model {
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_tag');
    }
}
```

### Eager Loading

```php
// Controller
public function index()
{
    $products = Product::with([
        'category',
        'tags',
        'images',
    ])->paginate(20);

    return response()->json($products);
}
```

---

## Customizando Código Gerado

### Modificar Modelos

```php
<?php
// Adicionar após geração automática

class Product extends Model {
    // ... código gerado

    // Adicionar casts
    protected $casts = [
        'price' => 'decimal:2',
        'featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    // Adicionar accessors
    public function getFormattedPriceAttribute()
    {
        return 'R$ ' . number_format($this->price, 2, ',', '.');
    }

    // Adicionar scopes
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }
}
```

---

## Boas Práticas

### 1. Sempre Fazer Backup

```bash
# Antes de gerar código sobre modelos existentes
cp -r app/Models app/Models.backup
```

### 2. Usar Controle de Versão

```bash
# Commitar antes de gerar código
git add .
git commit -m "Before Arquiteto generation"

# Gerar código
php artisan arquiteto:migrationFromMysql products

# Revisar mudanças
git diff
```

### 3. Revisar Código Gerado

Sempre revise e ajuste:
- ✅ Relacionamentos
- ✅ Casts de atributos
- ✅ Timestamps
- ✅ Namespace (Support vs App\Models)

### 4. Separar Lógica de Negócio

```php
// ❌ Evitar lógica no modelo
class Product extends Model {
    public function calculateDiscount() {
        // lógica complexa
    }
}

// ✅ Usar Services
class ProductService {
    public function calculateDiscount(Product $product) {
        // lógica complexa
    }
}
```

---

## Dicas e Truques

### Gerar Para Múltiplas Tabelas

```bash
# Usando wildcard
php artisan arquiteto:migrationFromMysql "mydb.product*"

# Ou script bash
for table in products categories orders; do
    php artisan arquiteto:migrationFromMysql $table
done
```

### Usar Stubs Personalizados

```bash
# Publicar stubs
php artisan vendor:publish --provider="Arquiteto\ArquitetoProvider" --tag="stubs"

# Editar stubs em resources/stubs/arquiteto/
```

### Automatizar com Scripts

```bash
#!/bin/bash
# generate-all.sh

tables="users categories products orders"

for table in $tables; do
    echo "Generating for $table..."
    php artisan arquiteto:migrationFromMysql $table
    php artisan arquiteto:request $table
    php artisan arquiteto:filter "${table^}Filter"
done

echo "Done!"
```

---

[← Voltar ao Índice](../README.md) | [Próximo: Integração →](integracao.md)
