# 🎯 Exemplos Reais

[← Voltar ao Índice](../README.md)

---

## Índice

- [E-commerce Completo](#e-commerce-completo)
- [Sistema de Blog](#sistema-de-blog)
- [API de Gerenciamento de Tarefas](#api-de-gerenciamento-de-tarefas)
- [Sistema ERP](#sistema-erp)
- [Marketplace Multi-vendor](#marketplace-multi-vendor)

---

## E-commerce Completo

### Contexto

Criar uma loja virtual completa com produtos, categorias, carrinhos, pedidos e pagamentos.

### Estrutura do Banco de Dados

```sql
-- Categorias de produtos
CREATE TABLE category (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (parent_id) REFERENCES category(id) ON DELETE SET NULL
);

-- Produtos
CREATE TABLE product (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description LONGTEXT,
    short_description VARCHAR(500),
    sku VARCHAR(100) UNIQUE NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) NULL,
    cost DECIMAL(10,2),
    stock INT DEFAULT 0,
    min_stock INT DEFAULT 5,
    weight DECIMAL(8,2),
    length DECIMAL(8,2),
    width DECIMAL(8,2),
    height DECIMAL(8,2),
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (category_id) REFERENCES category(id) ON DELETE RESTRICT
);

-- Imagens de produtos
CREATE TABLE product_image (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    path VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE
);

-- Carrinhos
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    session_id VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
);

-- Itens do carrinho
CREATE TABLE cart_item (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cart_id) REFERENCES cart(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE
);

-- Pedidos
CREATE TABLE `order` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    subtotal DECIMAL(10,2) NOT NULL,
    shipping_cost DECIMAL(10,2) DEFAULT 0.00,
    tax DECIMAL(10,2) DEFAULT 0.00,
    discount DECIMAL(10,2) DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    shipping_address TEXT,
    billing_address TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES user(id)
);

-- Itens do pedido
CREATE TABLE order_item (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    sku VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES `order`(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES product(id)
);
```

### Gerando a Estrutura

```bash
#!/bin/bash
# generate-ecommerce.sh

echo "🚀 Gerando estrutura do e-commerce..."

# 1. Gerar modelos
echo "📦 Gerando modelos Eloquent..."
php artisan arquiteto:migrationFromMysql "ecommerce.category"
php artisan arquiteto:migrationFromMysql "ecommerce.product"
php artisan arquiteto:migrationFromMysql "ecommerce.product_image"
php artisan arquiteto:migrationFromMysql "ecommerce.cart"
php artisan arquiteto:migrationFromMysql "ecommerce.cart_item"
php artisan arquiteto:migrationFromMysql "ecommerce.order"
php artisan arquiteto:migrationFromMysql "ecommerce.order_item"

# 2. Gerar form requests
echo "📝 Gerando form requests..."
php artisan arquiteto:request category
php artisan arquiteto:request product
php artisan arquiteto:request cart
php artisan arquiteto:request order

# 3. Gerar filtros
echo "🔍 Gerando filtros..."
php artisan arquiteto:filter CategoryFilter
php artisan arquiteto:filter ProductFilter
php artisan arquiteto:filter OrderFilter

echo "✅ Estrutura gerada com sucesso!"
```

### Personalizar Modelos

```php
<?php
// app/Models/Product.php (após ajustes)

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use EloquentFilter\Filterable;

class Product extends Model
{
    use SoftDeletes, Filterable;

    protected $table = 'product';

    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'short_description',
        'sku', 'price', 'sale_price', 'cost', 'stock', 'min_stock',
        'weight', 'length', 'width', 'height', 'is_active', 'is_featured',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost' => 'decimal:2',
        'weight' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    // Relacionamentos (gerados + ajustados)
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Scopes personalizados
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'min_stock');
    }

    // Accessors
    public function getEffectivePriceAttribute()
    {
        return $this->sale_price ?? $this->price;
    }

    public function getDiscountPercentageAttribute()
    {
        if (!$this->sale_price) {
            return 0;
        }

        return round((($this->price - $this->sale_price) / $this->price) * 100);
    }
}
```

### Controller de Produtos

```php
<?php
// app/Http/Controllers/Api/ProductController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::filter($request->all())
            ->with(['category', 'images', 'primaryImage'])
            ->active()
            ->paginate(20);

        return ProductResource::collection($products);
    }

    public function featured()
    {
        $products = Product::featured()
            ->active()
            ->inStock()
            ->with(['category', 'primaryImage'])
            ->limit(10)
            ->get();

        return ProductResource::collection($products);
    }

    public function show(Product $product)
    {
        $product->load(['category', 'images']);

        return new ProductResource($product);
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        $products = Product::where('name', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->orWhere('sku', 'LIKE', "%{$query}%")
            ->active()
            ->with(['category', 'primaryImage'])
            ->paginate(20);

        return ProductResource::collection($products);
    }
}
```

### Filtros Customizados

```php
<?php
// app/ModelFilters/ProductFilter.php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class ProductFilter extends ModelFilter
{
    public function category($categoryId)
    {
        return $this->where('category_id', $categoryId);
    }

    public function priceRange($range)
    {
        if (isset($range['min'])) {
            $this->where('price', '>=', $range['min']);
        }

        if (isset($range['max'])) {
            $this->where('price', '<=', $range['max']);
        }

        return $this;
    }

    public function inStock()
    {
        return $this->where('stock', '>', 0);
    }

    public function featured()
    {
        return $this->where('is_featured', true);
    }

    public function sale()
    {
        return $this->whereNotNull('sale_price');
    }

    public function sortBy($field)
    {
        $allowed = ['price', 'name', 'created_at', 'stock'];

        if (in_array($field, $allowed)) {
            return $this->orderBy($field);
        }

        return $this;
    }
}
```

### Rotas

```php
<?php
// routes/api.php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;

Route::prefix('v1')->group(function () {
    // Produtos
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/featured', [ProductController::class, 'featured']);
    Route::get('products/search', [ProductController::class, 'search']);
    Route::get('products/{product}', [ProductController::class, 'show']);

    // Categorias
    Route::apiResource('categories', CategoryController::class);

    // Carrinho (autenticado)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('cart', [CartController::class, 'show']);
        Route::post('cart/items', [CartController::class, 'addItem']);
        Route::put('cart/items/{item}', [CartController::class, 'updateItem']);
        Route::delete('cart/items/{item}', [CartController::class, 'removeItem']);
        Route::delete('cart', [CartController::class, 'clear']);

        // Pedidos
        Route::post('orders', [OrderController::class, 'store']);
        Route::get('orders', [OrderController::class, 'index']);
        Route::get('orders/{order}', [OrderController::class, 'show']);
    });
});
```

### Resultado

**Tempo economizado:** ~12 horas de desenvolvimento

**Código gerado:**
- 7 modelos Eloquent completos
- 4 Form Requests com validações
- 3 Filtros Eloquent
- Relacionamentos detectados automaticamente

---

## Sistema de Blog

### Estrutura Simplificada

```sql
CREATE TABLE post (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    excerpt VARCHAR(500),
    content LONGTEXT NOT NULL,
    featured_image VARCHAR(255),
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    views INT DEFAULT 0,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES user(id),
    FOREIGN KEY (category_id) REFERENCES category(id)
);

CREATE TABLE comment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NULL,
    parent_id INT NULL,
    author_name VARCHAR(100),
    author_email VARCHAR(150),
    content TEXT NOT NULL,
    is_approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES post(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_id) REFERENCES comment(id) ON DELETE CASCADE
);

CREATE TABLE tag (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE post_tag (
    post_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (post_id, tag_id),
    FOREIGN KEY (post_id) REFERENCES post(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tag(id) ON DELETE CASCADE
);
```

### Geração

```bash
php artisan arquiteto:migrationFromMysql "blog.*"
php artisan arquiteto:request post
php artisan arquiteto:request comment
php artisan arquiteto:filter PostFilter
```

### Modelo Post Customizado

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use EloquentFilter\Filterable;

class Post extends Model
{
    use SoftDeletes, Filterable;

    protected $table = 'post';

    protected $fillable = [
        'user_id', 'category_id', 'title', 'slug', 'excerpt',
        'content', 'featured_image', 'status', 'views', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'views' => 'integer',
    ];

    // Relacionamentos
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->orderByDesc('created_at');
    }

    public function approvedComments()
    {
        return $this->comments()->where('is_approved', true);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }

    // Scopes
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

    public function scopePopular($query, $limit = 10)
    {
        return $query->orderByDesc('views')->limit($limit);
    }

    // Métodos
    public function incrementViews()
    {
        $this->increment('views');
    }

    public function isPublished()
    {
        return $this->status === 'published'
            && $this->published_at !== null
            && $this->published_at->isPast();
    }
}
```

---

## API de Gerenciamento de Tarefas

### Banco de Dados

```sql
CREATE TABLE project (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE task (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    status ENUM('todo', 'in_progress', 'review', 'done') DEFAULT 'todo',
    due_date DATE,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES project(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id)
);
```

### Geração

```bash
php artisan arquiteto:migrationFromMysql "tasks.*"
php artisan arquiteto:request project
php artisan arquiteto:request task
php artisan arquiteto:filter TaskFilter
```

### Filter Customizado

```php
<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class TaskFilter extends ModelFilter
{
    public function project($projectId)
    {
        return $this->where('project_id', $projectId);
    }

    public function assignee($userId)
    {
        return $this->where('user_id', $userId);
    }

    public function status($status)
    {
        return $this->where('status', $status);
    }

    public function priority($priority)
    {
        return $this->where('priority', $priority);
    }

    public function overdue()
    {
        return $this->whereNotNull('due_date')
                    ->where('due_date', '<', now())
                    ->where('status', '!=', 'done');
    }

    public function dueToday()
    {
        return $this->whereDate('due_date', today());
    }
}
```

---

## Sistema ERP

### Módulos Gerados

- ✅ Gestão de Clientes
- ✅ Gestão de Fornecedores
- ✅ Controle de Estoque
- ✅ Vendas e Pedidos
- ✅ Compras
- ✅ Financeiro

**Tabelas:** 40+
**Tempo economizado:** ~60 horas
**Modelos gerados:** 40+
**Requests gerados:** 25+
**Filtros gerados:** 15+

---

## Marketplace Multi-vendor

### Complexidade

- Múltiplos vendedores
- Comissões e splits
- Gestão de lojas
- Avaliações e reviews

**Tabelas:** 25+
**Tempo economizado:** ~40 horas

---

[← Voltar ao Índice](../README.md) | [Próximo: Contribuição →](contribuicao.md)
