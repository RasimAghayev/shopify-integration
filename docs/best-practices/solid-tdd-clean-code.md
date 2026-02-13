# Best Practices Guide: SOLID, TDD, Clean Code, YAGNI, KISS

## SOLID Principles

### S - Single Responsibility Principle (SRP)

**Definition:** A class should have only one reason to change.

**Bad Example:**
```php
class ProductService
{
    public function syncProduct($shopifyId)
    {
        // Fetches from API
        $data = Http::get("shopify.com/products/{$shopifyId}");

        // Validates data
        if (!isset($data['sku'])) {
            throw new Exception('Invalid SKU');
        }

        // Saves to database
        Product::create($data);

        // Sends notification
        Mail::to('admin@example.com')->send(new ProductSynced());

        // Logs the action
        Log::info('Product synced', ['id' => $shopifyId]);
    }
}
```

**Good Example:**
```php
final class SyncProductFromShopifyUseCase
{
    public function __construct(
        private readonly ShopifyClientInterface $shopifyClient,
        private readonly ProductRepositoryInterface $repository,
        private readonly EventDispatcherInterface $dispatcher
    ) {}

    public function execute(SyncProductDTO $dto): ProductEntity
    {
        $shopifyProduct = $this->shopifyClient->getProduct($dto->shopifyId);
        $product = ProductEntity::fromShopifyData($shopifyProduct);
        $this->repository->save($product);
        $this->dispatcher->dispatch(new ProductSynced($product));

        return $product;
    }
}
```

**Checklist:**
- [ ] Each class handles exactly one concern
- [ ] Class name clearly describes its purpose
- [ ] Methods are cohesive (related to the class purpose)
- [ ] No "and" in class names (UserServiceAndValidator)

---

### O - Open/Closed Principle (OCP)

**Definition:** Software entities should be open for extension but closed for modification.

**Bad Example:**
```php
class ProductSyncer
{
    public function sync(string $type, array $data)
    {
        if ($type === 'full') {
            // Full sync logic
        } elseif ($type === 'incremental') {
            // Incremental sync logic
        } elseif ($type === 'webhook') {
            // Webhook sync logic - added later, required modifying class
        }
    }
}
```

**Good Example:**
```php
interface SyncStrategyInterface
{
    public function sync(array $data): void;
}

final class FullSyncStrategy implements SyncStrategyInterface
{
    public function sync(array $data): void
    {
        // Full sync logic
    }
}

final class IncrementalSyncStrategy implements SyncStrategyInterface
{
    public function sync(array $data): void
    {
        // Incremental sync logic
    }
}

// New strategy added without modifying existing code
final class WebhookSyncStrategy implements SyncStrategyInterface
{
    public function sync(array $data): void
    {
        // Webhook sync logic
    }
}

final class ProductSyncer
{
    public function __construct(
        private readonly SyncStrategyInterface $strategy
    ) {}

    public function sync(array $data): void
    {
        $this->strategy->sync($data);
    }
}
```

**Checklist:**
- [ ] New functionality added through new classes, not modifications
- [ ] Interfaces/abstractions used for variation points
- [ ] Strategy, Template Method, or Decorator patterns where applicable

---

### L - Liskov Substitution Principle (LSP)

**Definition:** Objects of a superclass should be replaceable with objects of its subclasses without affecting program correctness.

**Bad Example:**
```php
interface ProductRepositoryInterface
{
    public function save(Product $product): void;
}

class CachedProductRepository implements ProductRepositoryInterface
{
    public function save(Product $product): void
    {
        // VIOLATES LSP - throws unexpected exception
        if (!$this->cache->isAvailable()) {
            throw new CacheUnavailableException();
        }
        // ...
    }
}
```

**Good Example:**
```php
interface ProductRepositoryInterface
{
    public function save(ProductEntity $product): void;
    public function findBySku(Sku $sku): ?ProductEntity;
}

final class EloquentProductRepository implements ProductRepositoryInterface
{
    public function save(ProductEntity $product): void
    {
        ProductModel::updateOrCreate(
            ['sku' => $product->sku->value],
            $product->toArray()
        );
    }

    public function findBySku(Sku $sku): ?ProductEntity
    {
        $model = ProductModel::where('sku', $sku->value)->first();
        return $model ? ProductEntity::fromModel($model) : null;
    }
}

final class CachedProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
        private readonly CacheInterface $cache
    ) {}

    public function save(ProductEntity $product): void
    {
        // Gracefully handles cache failure - no surprise exceptions
        try {
            $this->cache->forget("product.{$product->sku->value}");
        } catch (CacheException $e) {
            Log::warning('Cache clear failed', ['exception' => $e]);
        }

        $this->repository->save($product);
    }

    public function findBySku(Sku $sku): ?ProductEntity
    {
        return $this->cache->remember(
            "product.{$sku->value}",
            3600,
            fn() => $this->repository->findBySku($sku)
        );
    }
}
```

**Checklist:**
- [ ] Subclasses don't throw unexpected exceptions
- [ ] Subclasses don't have stricter preconditions
- [ ] Subclasses don't have weaker postconditions
- [ ] Subclasses maintain parent invariants

---

### I - Interface Segregation Principle (ISP)

**Definition:** Clients should not be forced to depend on interfaces they don't use.

**Bad Example:**
```php
interface ProductServiceInterface
{
    public function create(array $data): Product;
    public function update(int $id, array $data): Product;
    public function delete(int $id): void;
    public function sync(int $shopifyId): Product;
    public function bulkImport(array $products): void;
    public function export(): array;
    public function sendToShopify(int $id): void;
    public function updateInventory(int $id, int $quantity): void;
    public function generateReport(): Report;
    public function sendNotification(int $id): void;
}
```

**Good Example:**
```php
interface ProductCreatorInterface
{
    public function create(CreateProductDTO $dto): ProductEntity;
}

interface ProductUpdaterInterface
{
    public function update(UpdateProductDTO $dto): ProductEntity;
}

interface ProductSyncerInterface
{
    public function syncFromShopify(string $shopifyId): ProductEntity;
    public function syncToShopify(Sku $sku): void;
}

interface ProductBulkImporterInterface
{
    public function import(array $products): BulkImportResult;
}

interface InventoryManagerInterface
{
    public function updateQuantity(Sku $sku, int $quantity): void;
}
```

**Checklist:**
- [ ] Interfaces are small and focused
- [ ] Clients only implement methods they need
- [ ] No empty method implementations
- [ ] Role-based interface naming (Reader, Writer, Syncer)

---

### D - Dependency Inversion Principle (DIP)

**Definition:** High-level modules should not depend on low-level modules. Both should depend on abstractions.

**Bad Example:**
```php
class SyncProductUseCase
{
    private ShopifyApiClient $client;      // Concrete class
    private EloquentProductRepository $repository;  // Concrete class
    private LaravelEventDispatcher $dispatcher;     // Concrete class

    public function __construct()
    {
        $this->client = new ShopifyApiClient();
        $this->repository = new EloquentProductRepository();
        $this->dispatcher = new LaravelEventDispatcher();
    }
}
```

**Good Example:**
```php
final class SyncProductFromShopifyUseCase
{
    public function __construct(
        private readonly ShopifyClientInterface $shopifyClient,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger
    ) {}

    public function execute(SyncProductDTO $dto): ProductEntity
    {
        $shopifyProduct = $this->shopifyClient->getProduct($dto->shopifyId);
        $product = ProductEntity::fromShopifyData($shopifyProduct);
        $this->productRepository->save($product);
        $this->eventDispatcher->dispatch(new ProductSynced($product));

        return $product;
    }
}

// Service Provider binds interfaces to implementations
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ShopifyClientInterface::class,
            ShopifyClient::class
        );

        $this->app->bind(
            ProductRepositoryInterface::class,
            fn($app) => new CachedProductRepository(
                new EloquentProductRepository(),
                $app->make(CacheInterface::class)
            )
        );
    }
}
```

**Checklist:**
- [ ] Constructor injection used for dependencies
- [ ] Interfaces defined for external services
- [ ] No "new" keyword for dependencies in business logic
- [ ] Service container/DI container configured

---

## Test-Driven Development (TDD)

### TDD Cycle: Red-Green-Refactor

```
1. RED: Write a failing test
2. GREEN: Write minimum code to pass
3. REFACTOR: Improve code while keeping tests green
```

### TDD Example

**Step 1: Write Failing Test**
```php
/** @test */
public function it_creates_valid_sku(): void
{
    $sku = new Sku('TEST-001');

    $this->assertEquals('TEST-001', $sku->value);
}
```

**Step 2: Minimum Implementation**
```php
final class Sku
{
    public function __construct(
        public readonly string $value
    ) {}
}
```

**Step 3: Add More Tests**
```php
/** @test */
public function it_throws_for_empty_sku(): void
{
    $this->expectException(InvalidSkuException::class);

    new Sku('');
}
```

**Step 4: Implement Validation**
```php
final class Sku
{
    public function __construct(
        public readonly string $value
    ) {
        if (empty($value)) {
            throw new InvalidSkuException('SKU cannot be empty');
        }
    }
}
```

### Test Structure: AAA Pattern

```php
/** @test */
public function it_syncs_product_successfully(): void
{
    // Arrange - Set up test data and mocks
    $dto = new SyncProductDTO(shopifyId: '123456');

    $this->shopifyClient
        ->shouldReceive('getProduct')
        ->once()
        ->with('123456')
        ->andReturn(['id' => '123456', 'title' => 'Test']);

    $this->productRepository
        ->shouldReceive('save')
        ->once();

    // Act - Execute the code under test
    $product = $this->useCase->execute($dto);

    // Assert - Verify the results
    $this->assertInstanceOf(ProductEntity::class, $product);
    $this->assertEquals('123456', $product->shopifyId);
}
```

### TDD Best Practices

- [ ] Write test before implementation
- [ ] One assertion per test (ideally)
- [ ] Test behavior, not implementation
- [ ] Use descriptive test names
- [ ] Keep tests fast and isolated
- [ ] Mock external dependencies
- [ ] Aim for 80%+ coverage

---

## Clean Code Principles

### Meaningful Names

**Bad:**
```php
$d; // elapsed time in days
$p; // product
$list1; // products
```

**Good:**
```php
$elapsedDays;
$product;
$activeProducts;
```

### Functions Should Be Small

**Bad:**
```php
public function processOrder($orderId)
{
    // 200 lines of code doing multiple things
}
```

**Good:**
```php
public function processOrder(OrderId $orderId): void
{
    $order = $this->findOrder($orderId);
    $this->validateOrder($order);
    $this->reserveInventory($order);
    $this->processPayment($order);
    $this->dispatchOrderEvent($order);
}
```

### Single Level of Abstraction

**Bad:**
```php
public function syncProducts(): void
{
    $response = Http::get('api.shopify.com/products');
    $data = json_decode($response->body(), true);

    foreach ($data['products'] as $product) {
        DB::table('products')->insert([
            'sku' => $product['sku'],
            'title' => $product['title']
        ]);
    }
}
```

**Good:**
```php
public function syncProducts(): void
{
    $products = $this->shopifyClient->fetchAllProducts();

    foreach ($products as $product) {
        $this->productRepository->save($product);
    }
}
```

### Comments

**Bad Comments:**
```php
// Increment i
$i++;

// Check if user is admin
if ($user->role === 'admin')
```

**Good Comments:**
```php
// Shopify API rate limits to 2 requests per second
sleep(1);

// TODO: Implement retry logic for transient failures (SHOP-123)
```

### Error Handling

**Bad:**
```php
try {
    $product = $this->sync($id);
} catch (Exception $e) {
    // Ignore
}
```

**Good:**
```php
try {
    $product = $this->sync($id);
} catch (ShopifyApiException $e) {
    $this->logger->error('Shopify sync failed', [
        'product_id' => $id,
        'error' => $e->getMessage()
    ]);
    throw new ProductSyncFailedException($id, $e);
}
```

---

## YAGNI (You Aren't Gonna Need It)

**Definition:** Don't add functionality until it's necessary.

**Bad Example:**
```php
class Product
{
    private array $translations = [];
    private array $customFields = [];
    private ?Product $parentProduct = null;
    private array $bundledProducts = [];
    private array $subscriptionOptions = [];
    // ... features that might never be used
}
```

**Good Example:**
```php
final class Product
{
    public function __construct(
        public readonly Sku $sku,
        public readonly string $title,
        public readonly Price $price
    ) {}
    // Only what's needed now
}
```

**YAGNI Checklist:**
- [ ] Feature is actually required for current task
- [ ] Not building for hypothetical future needs
- [ ] No "just in case" code
- [ ] Removing unused code regularly

---

## KISS (Keep It Simple, Stupid)

**Definition:** Prefer simple solutions over complex ones.

**Bad Example:**
```php
class ProductFactory
{
    public function create(
        ProductBuilderInterface $builder,
        ProductValidatorChain $validators,
        ProductTransformerPipeline $transformers,
        ProductDecoratorFactory $decoratorFactory
    ): ProductInterface {
        $product = $builder->build();
        $product = $validators->validate($product);
        $product = $transformers->transform($product);
        $product = $decoratorFactory->decorate($product);
        return $product;
    }
}
```

**Good Example:**
```php
final class ProductFactory
{
    public function create(array $data): ProductEntity
    {
        return new ProductEntity(
            sku: new Sku($data['sku']),
            title: $data['title'],
            price: new Price($data['price'])
        );
    }
}
```

**KISS Checklist:**
- [ ] Solution is straightforward
- [ ] No unnecessary abstraction layers
- [ ] Code is readable by junior developers
- [ ] Not over-engineered for current needs

---

## Summary Checklist

### Before Writing Code

- [ ] Understand the requirement fully
- [ ] Write a failing test first (TDD)
- [ ] Design with SOLID principles in mind

### While Writing Code

- [ ] Keep functions small and focused (SRP)
- [ ] Use meaningful names
- [ ] Handle errors properly
- [ ] No magic numbers or strings

### After Writing Code

- [ ] All tests pass
- [ ] Code coverage >= 80%
- [ ] Remove unused code (YAGNI)
- [ ] Simplify if possible (KISS)
- [ ] Refactor for clarity
