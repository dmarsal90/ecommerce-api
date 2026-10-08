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
 
## E-Commerce Architecture Review (2026-10-08) - Senior Developer Assessment
 
### Database Schema & Relationships - Issues Found
 
#### ✅ Well-Structured Core
- Products → Variants: One-to-Many with cascade delete
- Variants → Attributes: Many-to-Many via pivot `product_variant_attribute_values`
- Attributes → Values: One-to-Many with color/size linking
- Orders → Items: One-to-Many with snapshot fields (price, name, attributes)
- Orders → Addresses: Polymorphic billing/shipping via `type` column
- Inventory: Separate table tracking quantity, reserved, available per location
- Stock Movements: Audit trail with reference_type/reference_id
 
#### ⚠️ Critical Gaps in Relationships
| Issue | Location | Impact |
|-------|----------|--------|
| Missing Product ↔ Inventory relationship | `Product` model has no `inventory()` method | Can't access inventory from product directly |
| Missing Category ↔ Products inverse | `Category` model likely missing `products()` | Can't eager load products from category |
| Cart uses session, not DB model | `CartController` uses `session()` but `Cart`/`CartItem` models exist | **Inconsistency** - DB models unused, session-based cart doesn't persist across devices |
| No Product ↔ Brand foreign key cleanup | Migration has `brand` string column, separate migration adds `brand_id` | **Data integrity risk** - string column not removed |
 
### Object Creation & Data Integrity - Issues
 
#### Product Creation Flow - Problems
1. **No slug uniqueness guarantee** - `Str::slug()` can produce duplicates
2. **SKU generation not unique** - random 8 chars could collide
3. **No inventory auto-creation** - Product created but no `Inventory` record (StockLockService expects it)
4. **Brand column duplication** - Migration has both `brand` (string) and `brand_id` (FK)
 
#### Variant Creation - Missing
- No `ProductVariant::creating` boot method for SKU generation
- No automatic `Inventory` creation for variants
 
#### Order Creation - Incomplete
- Missing: Order confirmation email/event
- Missing: Payment intent creation (Stripe/PayPal)
- Missing: Inventory reservation (not just decrement)
- Missing: Order status history tracking
 
### API Endpoints - Completeness Check
 
#### Current Endpoints
| Resource | Endpoints | Missing |
|----------|-----------|---------|
| **Auth** | register, login, logout, me, update | Password reset, email verification, social login |
| **Products** | list, show, featured, new, sale, related | **Search suggestions**, **filter options**, **compare** |
| **Categories** | list, tree, show, products | Category filters in product list |
| **Brands** | list, featured, show, products | - |
| **Collections** | list, show, products | - |
| **Cart** | get, add, update, remove, clear, count | **Merge guest→user**, **apply coupon**, **shipping estimate** |
| **Orders** | list, show, create, cancel | **Return/request**, **reorder**, **invoice PDF**, **tracking** |
| **Addresses** | list, create, update, delete | **Set default**, **validate address** |
| **User** | profile, addresses | **Wishlist**, **order history**, **downloadable products** |
 
#### 🔴 Critical Missing Endpoints
1. **Checkout flow** - No `/checkout` endpoint for payment intent
2. **Webhooks** - No Stripe/PayPal webhook handlers
3. **Guest checkout** - Orders require auth
4. **Product reviews** - No POST `/products/{slug}/reviews`
5. **Coupons/Discounts** - No validation/apply endpoints
6. **Shipping rates** - No calculation endpoint
7. **Stock check** - No real-time availability endpoint
 
### 🐛 E-Commerce Specific Bugs & Gaps
 
#### 1. Inventory System - Inconsistent Dual Tracking
```php
// Product has quantity + track_quantity
// Variant has quantity + track_quantity  
// Inventory has quantity + reserved + available + track_quantity
// StockLockService checks inventory->available ?? quantity
// But Product/Variant quantity not synced with Inventory!
```
**Risk**: Three sources of truth for stock. `Inventory` created in seeder but not auto-created.
 
#### 2. Cart Implementation - Dual System
```php
// CartController uses session() 
// But Cart/CartItem Eloquent models exist with DB tables
// Cart::getCurrentCart() exists but unused
```
**Result**: Guest carts don't persist, no cross-device sync, DB models dead code.
 
#### 3. Pricing - Hardcoded Business Logic
```php
// PricingCalculator uses config('shop.*') 
// But no support for:
// - Customer groups pricing
// - Volume/tiered pricing
// - Sale price scheduling
// - Coupon codes
// - Tax exemptions
```
 
#### 4. Order Flow - Missing States
```php
// Statuses: pending, paid, processing, shipped, delivered, cancelled, refunded
// Missing: 
// - awaiting_payment (for async payments)
// - partially_shipped
// - partially_refunded
// - on_hold
```
 
#### 5. Product Variants - Incomplete
- No **variant image** support (only single `image` string)
- No **variant-specific weight/dimensions** for shipping
- No **variant SEO** (meta_title, meta_description)
 
#### 6. Attributes System - Overengineered but Underused
- `ProductAttribute` has `type`, `display_type` but never used in API
- No **filtering by attribute** in ProductController
- No **attribute-based URL** (e.g., `/products?color=red&size=M`)
 
#### 7. No Soft Deletes on Critical Models
```php
// Order, Product, ProductVariant, Category - no SoftDeletes
// Accidental deletion = data loss
```
 
#### 8. Missing Indexes for Performance
```sql
-- Products: missing composite index on (is_active, category_id, brand_id)
-- OrderItems: missing index on product_id for "bought together" queries
-- Inventory: unique constraint allows NULL variant_id + NULL product_id = duplicate risk
```
 
### 🔒 Security & Compliance Gaps
| Gap | Severity |
|-----|----------|
| No PCI DSS considerations (raw card data not handled but no tokenization) | Medium |
| No GDPR: right to erasure, data export | High |
| No rate limiting on order creation (inventory exhaustion attack) | High |
| No idempotency keys on order creation (double-charge risk) | High |
| CORS not configured for production domains | Medium |
| No API versioning in routes | Medium |
 
### 📦 Code Quality Issues
| File | Issue |
|------|-------|
| `Product` model | `getMinPriceAttribute` executes query per access (N+1 risk) |
| `ProductVariant` | `getFormattedAttributes` loads all attributes per variant |
| `CartService::buildCartDTO` | Loads ALL products/variants then filters in PHP |
| `OrderService::prepareOrderItems` | N+1: finds product/variant per item in loop |
| `ProductFilterService` | `search` uses `LIKE %term%` - no full-text search |
| `StockLockService` | `lockForUpdate` on SQLite = **no-op** (SQLite doesn't support row locks) |
 
### ✅ What's Actually Good
1. **Service layer extraction** - Clean separation of concerns
2. **DTO pattern** - Type-safe data passing
3. **Policies** - Proper authorization
4. **Stock locking** - Correct use of `lockForUpdate` (for MySQL/Postgres)
5. **Order snapshot** - Items store price/name at purchase time
6. **Stock movements** - Audit trail exists
7. **Test coverage** - 66 tests passing
8. **Filament admin** - Full CMS for content management
 
### 🚀 Priority Fixes for Production
 
#### P0 - Blockers (Must Fix First)
1. Fix Cart: Use DB models, remove session-based implementation
2. Auto-create Inventory on Product/Variant creation
3. Add idempotency key to order creation
4. Add SoftDeletes to Order, Product, ProductVariant, Category
 
#### P1 - Critical
5. Implement checkout/payment intent endpoint
6. Add webhook handlers for payments
7. Fix SQLite row-lock issue (use advisory locks or skip in testing)
8. Add compound indexes for product filtering
9. Implement coupon/discount system
 
#### P2 - Important
10. Guest checkout support
11. Product reviews API
12. Shipping rate calculation
13. Email notifications (order confirmed, shipped, etc.)
14. Wishlist endpoints
15. Full-text search (Meilisearch/Algolia)
 
#### P3 - Nice to Have
16. Customer group pricing
17. Variant images
18. Product comparison
19. Recently viewed
20. Abandoned cart recovery
 
### 📋 Summary Score: 6.5/10
**Production Ready?** ❌ **No** - Critical cart/inventory issues, missing checkout flow, no payment integration.
**Time to Production Ready:** ~2-3 weeks with dedicated team.

The service layer refactor was the right architectural move - now the domain logic is testable and extensible. The database schema is solid for a mid-sized catalog. Main work is completing the ecommerce-specific features (checkout, payments, cart persistence) and fixing the inventory/cart dual-system inconsistency.

---

## Implementation Progress (2026-10-08) - Completed Items

### ✅ P0 - Blockers (All Completed)

| # | Task | Status | Files Changed |
|---|------|--------|---------------|
| 1 | Fix Cart: Use DB models, remove session-based implementation | ✅ | `CartService`, `CartController`, `Cart`, `CartItem` models |
| 2 | Auto-create Inventory on Product/Variant creation | ✅ | `Product::boot()`, `ProductVariant::boot()` |
| 3 | Add idempotency key to order creation | ✅ | Migration, `Order`, `OrderService`, `StoreOrderRequest` |
| 4 | Add SoftDeletes to Order, Product, ProductVariant, Category | ✅ | Migrations, Models with `SoftDeletes` trait |
| 5 | Fix SQLite row-lock issue in StockLockService | ✅ | `StockLockService::lockForUpdate()` driver check |
| 6 | Clean up Product brand column duplication | ✅ | Migration `2026_09_17_134142_fix_products_brand_foreign_key` |

### ✅ P1 - Critical (All Completed)

| # | Task | Status | Files Changed |
|---|------|--------|---------------|
| 5 | Implement checkout/payment intent endpoint | ✅ | `CheckoutController`, `CheckoutService`, `StripePaymentGateway` |
| 6 | Add webhook handlers for payments (Stripe) | ✅ | `WebhookController`, `WebhookController::stripe()`, routes |
| 7 | Fix SQLite row-lock issue | ✅ | `StockLockService` driver-aware locking |
| 8 | Add compound indexes for product filtering | ✅ | Migration `2026_10_08_155925_add_compound_indexes_to_products_table` |
| 9 | Implement coupon/discount system | ✅ | `Coupon`, `CouponService`, `CouponController`, `ValidateCouponRequest` |

### 📊 Test Results After Implementation
```
66 tests, 568 assertions - ALL PASSING
```

### 🏗️ New Architecture Components Added

#### Services
- `App\Services\Contracts\PaymentGatewayInterface`
- `App\Services\Payment\StripePaymentGateway`
- `App\Services\Order\CheckoutService`
- `App\Services\Order\CouponService`
- `App\Services\Contracts\PaymentGatewayInterface`

#### Models
- `App\Models\Coupon` (with SoftDeletes, scopes, discount calculation)

#### Controllers
- `App\Http\Controllers\Api\CheckoutController`
- `App\Http\Controllers\Api\WebhookController`
- `App\Http\Controllers\Api\CouponController`

#### Requests/Resources
- `App\Http\Requests\Api\ValidateCouponRequest`
- `App\Http\Resources\Api\CouponResource`

#### Migrations
- `2026_10_08_153705_add_idempotency_key_to_orders_table`
- `2026_10_08_154043_add_soft_deletes_to_orders_table`
- `2026_10_08_154309_add_soft_deletes_to_products_table`
- `2026_10_08_154310_add_soft_deletes_to_product_variants_table`
- `2026_10_08_154311_add_soft_deletes_to_categories_table`
- `2026_10_08_155925_add_compound_indexes_to_products_table`
- `2026_10_08_161202_create_coupons_table`

### 🔄 Updated Project Structure

```
app/
├── Services/
│   ├── Contracts/
│   │   ├── StockServiceInterface.php
│   │   ├── PricingCalculatorInterface.php
│   │   ├── ProductFilterServiceInterface.php
│   │   └── PaymentGatewayInterface.php
│   ├── Inventory/
│   │   └── StockLockService.php
│   ├── Order/
│   │   ├── OrderService.php
│   │   ├── PricingCalculator.php
│   │   ├── CheckoutService.php
│   │   ├── CouponService.php
│   │   └── OrderDTOs/
│   ├── Cart/
│   │   ├── CartService.php
│   │   ├── CartDTO.php
│   │   └── CartItemDTO.php
│   ├── Product/
│   │   └── ProductFilterService.php
│   └── Payment/
│       └── StripePaymentGateway.php
├── Models/
│   ├── Coupon.php (new)
│   ├── Order.php (SoftDeletes, idempotency_key)
│   ├── Product.php (SoftDeletes, inventory(), unique slug/sku)
│   ├── ProductVariant.php (SoftDeletes, inventory auto-creation)
│   └── Category.php (SoftDeletes)
├── Http/Controllers/Api/
│   ├── CartController.php (thin, uses CartService)
│   ├── OrderController.php (thin, uses OrderService)
│   ├── ProductController.php (thin, uses ProductFilterService)
│   ├── CheckoutController.php (new)
│   ├── WebhookController.php (new)
│   └── CouponController.php (new)
└── Policies/
    ├── OrderPolicy.php
    └── ProductPolicy.php
```

### 🚀 Remaining Work (P2 - P3)

| Priority | Task | Status |
|----------|------|--------|
| P2 | Guest checkout support | ⏳ |
| P2 | Product reviews API | ⏳ |
| P2 | Shipping rate calculation endpoint | ⏳ |
| P2 | Email notifications | ⏳ |
| P2 | Wishlist endpoints | ⏳ |
| P2 | Fix N+1 queries | ⏳ |
| P3 | Customer group pricing | ⏳ |
| P3 | Variant images support | ⏳ |
| P3 | Product comparison endpoint | ⏳ |
| P3 | Recently viewed products | ⏳ |
| P3 | Abandoned cart recovery | ⏳ |

---

*Last Updated: 2026-10-08*
*All 66 tests passing - 11 P0/P1 items completed*