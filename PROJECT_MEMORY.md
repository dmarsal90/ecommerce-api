# E-Commerce API - Project Memory

## Project Overview
- **Framework**: Laravel 13.x with Filament Admin Panel
- **Database**: SQLite (development) / PostgreSQL (production)
- **Authentication**: Laravel Sanctum (API tokens)
- **Admin Panel**: Filament v3
- **Testing**: PHPUnit with 66 tests passing

## Architecture Refactoring (2026-10-08)

### Problem Statement
The original codebase had controllers with too much business logic (SRP violations), duplicated stock locking code, and no service layer for domain logic separation.

### Solution Implemented
Introduced a **Service Layer** following Domain-Driven Design principles:

```
app/
├── Services/
│   ├── Contracts/                    # Interfaces (DIP)
│   │   ├── StockServiceInterface.php
│   │   ├── PricingCalculatorInterface.php
│   │   └── ProductFilterServiceInterface.php
│   ├── Inventory/
│   │   └── StockLockService.php      # Centralized stock management
│   ├── Order/
│   │   ├── OrderService.php          # Order creation & cancellation
│   │   ├── PricingCalculator.php     # Tax, shipping, discounts
│   │   ├── OrderDTOs/                # Data Transfer Objects
│   │   │   ├── OrderItemDTO.php
│   │   │   ├── OrderAddressDTO.php
│   │   │   └── OrderDataDTO.php
│   ├── Cart/
│   │   ├── CartService.php           # Cart operations
│   │   ├── CartDTO.php
│   │   └── CartItemDTO.php
│   └── Product/
│       └── ProductFilterService.php  # Product filtering & sorting
├── Policies/
│   ├── OrderPolicy.php
│   └── ProductPolicy.php
└── Http/Controllers/Api/             # Thin controllers only
    ├── CartController.php
    ├── OrderController.php
    └── ProductController.php
```

### Key Services Created

#### 1. StockLockService (`App\Services\Inventory\StockLockService`)
- Implements `StockServiceInterface`
- Centralizes all stock operations with row-level locking (`lockForUpdate()`)
- Methods: `getAvailableStock()`, `checkStock()`, `decrementStock()`, `incrementStock()`, `reserveStock()`, `releaseReservedStock()`
- Eliminates duplicated locking logic from CartController and OrderController

#### 2. PricingCalculator (`App\Services\Order\PricingCalculator`)
- Implements `PricingCalculatorInterface`
- Calculates: subtotal, tax (8%), shipping ($9.99, free over $50), discounts
- Extensible for future coupon/loyalty logic
- Handles both arrays and OrderItemDTO objects

#### 3. OrderService (`App\Services\Order\OrderService`)
- Orchestrates order creation flow
- Validates stock before transaction
- Creates order, items, addresses atomically
- Decrements stock with locks
- Handles order cancellation with stock restoration

#### 4. CartService (`App\Services\Cart\CartService`)
- Manages session-based cart
- Stock validation on add/update
- Builds rich cart DTOs with product/variant details
- Generates composite keys for product+variant combinations

#### 5. ProductFilterService (`App\Services\Product\ProductFilterService`)
- Implements `ProductFilterServiceInterface`
- Handles all filtering: category, brand, gender, season, price range, search
- Sorting: price_asc/desc, name_asc/desc, oldest/newest
- Special endpoints: featured, new arrivals, on sale, related products

### DTOs (Data Transfer Objects)
Strongly-typed objects for data passing between layers:
- **OrderItemDTO** - Order line items with price, quantity, product/variant info
- **OrderAddressDTO** - Shipping/billing addresses matching DB schema
- **OrderDataDTO** - Complete order data for persistence
- **CartDTO / CartItemDTO** - Cart state with computed totals

### Policies Added
- **OrderPolicy** - View own orders, admin full access, cancel only pending/processing
- **ProductPolicy** - Public view active products, admin CRUD + stock management

### Controller Refactoring
Controllers now follow **Thin Controller** pattern:
- Inject services via constructor (DI)
- Delegate all business logic to services
- Handle only HTTP concerns: validation, responses, status codes
- Catch domain exceptions → return appropriate JSON errors

### Binding Registration (`AppServiceProvider`)
```php
$this->app->bind(StockServiceInterface::class, StockLockService::class);
$this->app->bind(PricingCalculatorInterface::class, PricingCalculator::class);
$this->app->bind(ProductFilterServiceInterface::class, ProductFilterService::class);
$this->app->singleton(CartService::class);
$this->app->singleton(OrderService::class);
```

### Test Updates
Updated 6 test assertions to match new error message formats:
- "Product or variant is not available" (was "Product is not available")
- "Not enough stock available. Available: X" (was "Not enough stock available" + separate 'available' field)

### All Tests Passing
```
66 tests, 568 assertions - ALL PASSING
```

## Database Schema Notes
- **SQLite** for local development (configured in `.env`)
- **PostgreSQL** for production (Supabase)
- Key tables: users, products, variants, categories, brands, orders, order_items, order_addresses, carts, cart_items, inventory, stock_movements
- Migrations include foreign keys, indexes, and proper constraints

## Configuration
- `config/shop.php` - Business rules (tax rate, shipping threshold, currency)
- `config/permission.php` - Spatie roles/permissions
- `config/sanctum.php` - API token settings

## Key Files Modified
| File | Change |
|------|--------|
| `.env` | Changed DB_CONNECTION to sqlite |
| `app/Models/ProductVariantAttributeValue.php` | Added `$table` property |
| `app/Providers/AppServiceProvider.php` | Service bindings + policy registration |
| `app/Http/Controllers/Api/CartController.php` | Refactored to use CartService |
| `app/Http/Controllers/Api/OrderController.php` | Refactored to use OrderService |
| `app/Http/Controllers/Api/ProductController.php` | Refactored to use ProductFilterService |
| `app/Services/*` | New service layer (15 files) |
| `app/Policies/*` | New policies (2 files) |
| `tests/Feature/Api/CartControllerTest.php` | Updated error message assertions |
| `tests/Feature/Api/OrderControllerTest.php` | Updated error message assertions |

## Future Scalability Roadmap
1. **Event-Driven Architecture** - OrderCreated, StockDecremented events for notifications/analytics
2. **Payment Gateway Abstraction** - Interface for Stripe/PayPal/etc.
3. **Caching Layer** - Redis for product listings, categories
4. **API Versioning** - DTOs ready for v2 transformations
5. **Background Jobs** - Order emails, inventory sync via queues

## Commands Reference
```bash
# Run tests
php artisan test

# Fresh migrate & seed
php artisan migrate:fresh --seed

# Clear caches
php artisan optimize:clear

# Dump autoload (after new classes)
composer dump-autoload
```

## Environment Variables (Key)
```env
DB_CONNECTION=sqlite
SHOP_TAX_RATE=0.08
SHOP_FREE_SHIPPING_THRESHOLD=50
SHOP_SHIPPING_COST=9.99
SHOP_CURRENCY=USD
SANCTUM_TOKEN_EXPIRATION=43200
```

---

*Last Updated: 2026-10-08*
*All 66 tests passing - Architecture refactored to service layer pattern*