# API de la tienda

Base: `{API_URL}/api`. Todas las peticiones y respuestas son JSON; envía siempre `Accept: application/json`.

## Convenciones

**Autenticación.** Token Bearer (Sanctum): `Authorization: Bearer <token>`. El token se obtiene en `POST /login` o `POST /register` y se revoca en `POST /logout`. No se usan cookies, así que el front puede estar en otro origen (ver CORS).

**Carrito de invitado.** Cada respuesta de las rutas `/cart`, `/login` y `/register` incluye la cabecera `X-Cart-Token` (UUID). Guárdala y reenvíala en las siguientes peticiones; si falta o no es un UUID, el servidor genera una nueva. Con un Bearer válido el carrito pertenece al usuario, y al hacer `login` o `register` enviando el `X-Cart-Token` del invitado, sus artículos se fusionan con el carrito del usuario.

**CORS.** Se permite el origen definido en `CORS_ALLOWED_ORIGINS` (por defecto `FRONTEND_URL`, `http://localhost:3000`). `X-Cart-Token` está expuesta a JavaScript.

**Paginación.** Los listados paginados devuelven `{ data: [...], meta: { current_page, last_page, per_page, total }, links: {...} }`. Parámetro `page` en todos; `per_page` (máx. 100, por defecto 20) solo en `/products` y en los `/{slug}/products` de categorías, marcas y colecciones. Las reseñas se paginan de 10 en 10 y los pedidos con el tamaño por defecto del servidor.

**Errores.**

| Código | Cuándo | Cuerpo |
|---|---|---|
| 401 | Falta o es inválido el token | `{ "message": "Unauthenticated." }` |
| 404 | Recurso inexistente o inactivo | `{ "message": "..." }` |
| 422 | Validación o regla de negocio | `{ "message": "...", "errors": { "campo": ["..."] } }` (las reglas de negocio, como cupón inválido o sin stock, devuelven solo `message`) |
| 429 | Rate limit | `{ "message": "..." }` |

**Importes.** Decimales con dos cifras (`"119.98"` en pedidos, número en carrito); la moneda viene en `GET /config` y en cada pedido (`currency`).

## Configuración y contenido (públicos)

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/config` | `{ name, currency, tax_rate, free_shipping_threshold, shipping_cost }` |
| GET | `/banners` | Banners activos y vigentes. Filtro `?position=hero\|sidebar\|footer` |
| GET | `/homepage` | Bloques de la home ordenados: `{ id, type, title, settings }` (`settings` es un objeto) |
| GET | `/menus` | Menús activos con `items` anidados (`children`): `{ name, slug, location, items: [{ id, label, url, open_in_new_tab, children }] }` |
| GET | `/pages` | Lista de páginas publicadas: `{ title, slug }` |
| GET | `/pages/{slug}` | Página completa (`content`, `meta_title`, `meta_description`, `template`, `published_at`) |
| GET | `/tags` | `{ id, name, slug, color }` |
| GET | `/colors` | `{ id, name, slug, hex, image }` |
| GET | `/sizes` | `{ id, size_group_id, name, slug, value }` |

Las respuestas de esta sección van envueltas en `{ "data": ... }`.

## Catálogo (público)

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/products` | Listado paginado con filtros (abajo) |
| GET | `/products/featured` · `/new-arrivals` · `/on-sale` | Listados rápidos |
| GET | `/products/{slug}` | Detalle: añade `collections`, `tags` y `reviews` aprobadas |
| GET | `/products/{slug}/related` | Productos relacionados |
| GET | `/products/{slug}/reviews` | Reseñas aprobadas, paginadas (10) |
| GET | `/categories` · `/categories/tree` | Plano o en árbol (`children`) |
| GET | `/categories/{slug}` · `/categories/{slug}/products` | Detalle y productos |
| GET | `/brands` · `/brands/featured` · `/brands/{slug}` · `/brands/{slug}/products` | Marcas |
| GET | `/collections` · `/collections/{slug}` · `/collections/{slug}/products` | Colecciones |

**Filtros de `GET /products`** (todos opcionales): `search` (nombre y descripciones), `category` y `brand` (slug), `gender` (`men|women|kids|unisex`), `season`, `featured`, `on_sale`, `min_price`, `max_price`, `sort` (`price_asc|price_desc|name_asc|name_desc|oldest|newest`), `per_page`.

**Producto:** `{ id, name, slug, sku, description, short_description, price, compare_at_price, is_active, is_featured, gender, season, quantity, track_quantity, category, brand, images, variants, created_at, updated_at }`.

**Reseña:** `{ id, product_id, rating, title, comment, is_verified_purchase, author, created_at, updated_at }`. `author` es solo el nombre; nunca se expone el email.

## Autenticación y cuenta

| Método | Ruta | Auth | Cuerpo / notas |
|---|---|---|---|
| POST | `/register` | – | `name`, `email`, `password` (≥ 8), `password_confirmation`. 201 → `{ user, token }` |
| POST | `/login` | – | `email`, `password`. → `{ user, token }`. 422 si las credenciales no son correctas |
| POST | `/forgot-password` | – | `email`. Siempre 200 (no revela si el email existe). Envía un correo con enlace a `FRONTEND_URL/reset-password?token=…&email=…` |
| POST | `/reset-password` | – | `token`, `email`, `password` (≥ 8), `password_confirmation`. Revoca todos los tokens del usuario |
| POST | `/logout` | ✔ | Revoca el token actual |
| GET | `/me` | ✔ | `{ user: { id, name, email, avatar_url, email_verified_at, addresses, ... } }` |
| PUT | `/me` | ✔ | `name`, `email` (requiere `current_password`), `password` + `password_confirmation` |
| GET · POST | `/addresses` | ✔ | Direcciones del usuario. POST: `type` (`billing|shipping`), `first_name`, `last_name`, `company`, `address_line_1`, `address_line_2`, `city`, `state`, `postal_code`, `country` (ISO-2), `phone`, `is_default` |
| PUT · DELETE | `/addresses/{id}` | ✔ | Mismos campos, todos opcionales en PUT |

## Carrito

Funciona para invitados (con `X-Cart-Token`) y para usuarios (con Bearer).

| Método | Ruta | Cuerpo | Respuesta |
|---|---|---|---|
| GET | `/cart` | – | `{ data: { items, subtotal, total_quantity, item_count } }` |
| POST | `/cart` | `product_id`, `variant_id?`, `quantity` (1–99) | `{ message, cart_count }`. 422 si el producto está inactivo o no hay stock |
| PUT | `/cart/{key}` | `quantity` (1–99) | Carrito actualizado |
| DELETE | `/cart/{key}` | – | Quita el artículo |
| DELETE | `/cart` | – | Vacía el carrito |
| GET | `/cart/count` | – | `{ count }` |

`{key}` es el campo `key` de cada artículo (`{ key, product_id, variant_id, quantity, price, total, product, variant }`).

## Cupones y checkout (requieren Bearer)

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/coupons` | Cupones aplicables al carrito actual |
| POST | `/coupons/validate` | Cuerpo `{ code }`. 200 → `{ valid, coupon, discount, new_subtotal }`; 422 → `{ valid: false, message }` |
| POST | `/orders` | Crea un pedido (pagos que no son con tarjeta, p. ej. `bank_transfer`) |
| GET | `/checkout/requirements` | Tarjetas y direcciones guardadas + `next_step` (`payment_method` → `billing_address` → `shipping_address` → `ready`) |
| POST | `/checkout/payment-intent` | Crea el pedido y, si `payment_method` es `credit_card` o `stripe`, un PaymentIntent de Stripe |
| GET | `/orders` | Pedidos del usuario, paginados |
| GET | `/orders/{orderNumber}` | Detalle (404 si no es tuyo) |
| POST | `/orders/{orderNumber}/cancel` | Solo en estado `pending` o `processing`; restaura el stock |

**Cuerpo de `POST /orders` y `POST /checkout/payment-intent`:**

```json
{
  "items": [{ "product_id": 5, "variant_id": null, "quantity": 2 }],
  "shipping_address": {
    "first_name": "Ana", "last_name": "Pérez", "address_line_1": "Calle 1",
    "address_line_2": null, "city": "Madrid", "state": "M", "postal_code": "28001",
    "country": "ES", "phone": null
  },
  "billing_address": null,
  "payment_method": "credit_card",
  "coupon_code": "WELCOME10",
  "idempotency_key": "d2c3…",
  "notes": null
}
```

- Direcciones: en lugar de `shipping_address` / `billing_address` en línea se pueden enviar `shipping_address_id` / `billing_address_id` (direcciones guardadas del usuario). Con tarjeta, la de facturación es obligatoria. Con `bank_transfer`, `billing_address` es opcional (por defecto, la de envío).
- Tarjeta: `payment_method` `credit_card` o `stripe` exige `payment_method_id` (tarjeta guardada, no caducada) y solo se acepta en `/checkout/payment-intent`; `/orders` devuelve 422. Los datos de tarjeta en crudo se rechazan: solo Stripe.js los maneja.
- `billing_address` es opcional sin tarjeta (por defecto, la de envío). `coupon_code` es opcional; si no es válido, expirado o agotado, la petición devuelve 422 y no se crea el pedido.
- `idempotency_key` evita pedidos duplicados al reintentar: repetir la misma clave devuelve el pedido ya creado.
- El precio, el impuesto, el envío y el descuento los calcula siempre el servidor; el front solo los muestra. Envío gratis por encima de `free_shipping_threshold` o con un cupón `free_shipping`.
- El stock se descuenta al crear el pedido.
- 201 en `/orders` → `{ message, data: pedido }`. 201 en `/checkout/payment-intent` → `{ message, order, payment_intent }`, donde `payment_intent` es `{ id, client_secret, ... }` (o `null` si el método no es con tarjeta). El front confirma el pago con `client_secret` usando Stripe.js.

**Pedido:** `{ id, order_number, email, phone, status, payment_status, fulfillment_status, subtotal, tax, shipping, discount, total, currency, payment_method, notes, cancelled_at, shipped_at, delivered_at, items, addresses, created_at, updated_at }`. Cada `item`: `{ id, order_id, product_id, variant_id, quantity, price, compare_at_price, total, product_name, variant_name, sku, product, variant }`.

**Estados de pago.** Tras confirmar el pago con Stripe, el estado del pedido pasa a `paid` mediante el webhook (`POST /webhooks/stripe`, solo para Stripe; el front no lo usa). Hay que consultar `GET /orders/{orderNumber}` para ver el estado final.

**3-D Secure.** El PaymentIntent se crea con la tarjeta guardada pero sin confirmar, y se pide 3DS siempre que la tarjeta lo soporte. El front debe confirmarlo en el navegador:

```js
const { error, paymentIntent } = await stripe.confirmCardPayment(client_secret);
// Stripe.js abre el reto 3DS si hace falta. Sin error y status 'succeeded' → pago hecho.
```

- Si hay `error` (tarjeta rechazada, reto fallido o cancelado), mostrar el mensaje y permitir reintentar con `stripe.confirmCardPayment` sobre el mismo `client_secret`, sin crear otro pedido.
- El resultado de Stripe.js es solo informativo: el estado real lo marca el webhook. Tras confirmar, consultar `GET /orders/{orderNumber}` hasta ver `payment_status: paid` (puede tardar unos segundos).
- Un pedido con tarjeta que no se paga en 60 min se cancela solo y se libera el stock.

## Reseñas (requieren Bearer)

`POST /products/{slug}/reviews` con `{ rating (1–5), title?, comment? }`. 201 → la reseña, que queda `pending` hasta que se modere en el panel. Una reseña por usuario y producto (422 si ya existe). `is_verified_purchase` se calcula en el servidor.
