# CuponesHub — Plataforma de Gestión de Cupones de Descuento

## Contexto del Proyecto
CuponesHub es una plataforma Laravel 12 independiente del proyecto Sr WOK, diseñada para:
- Gestionar usuarios/roles/permisos del panel admin
- Crear y administrar cupones de descuento (porcentaje o monto fijo)
- Rastrear redenciones con log completo (quién, cuándo, dónde, canal)
- Exponer una API REST para que sistemas POS externos validen/rediman cupones
- Gestionar clientes con aceptación de T&C / datos personales (Ley 1581 Colombia)
- Enviar SMS masivos con cupones segmentados por ciudad, zona, punto de venta

**Proyecto:** `e:/cupones-laravel`
**App URL:** `http://localhost:8001` (puerto 8000 lo ocupa Sr WOK)
**Base de datos:** MySQL — `cuponeshub` (root / sin contraseña)

---

## Credenciales de Acceso

### Panel Admin
| Campo | Valor |
|-------|-------|
| URL | `http://localhost:8001/login` |
| Email | `admin@cuponeshub.com` |
| Password | `Admin@2026!` |
| Rol | `super-admin` |

### API Demo Client
| Campo | Valor |
|-------|-------|
| client_id | `ch_demo_client` |
| client_secret | `demo_secret_J0d3o77ZuFjxuF4RkXHIeNo95kNNfFaj` |
| Uso | Headers `X-Client-Id` + `X-Client-Secret` en cada request |

---

## Stack Tecnológico

| Capa | Tecnología |
|------|-----------|
| Backend | Laravel 12, PHP 8.2 |
| Auth admin | Session-based (login/logout propio, sin Breeze) |
| Auth API | Custom middleware `ApiAuthenticate` (X-Client-Id/Secret) |
| Roles | spatie/laravel-permission — roles: super-admin, admin, operador, analista |
| Frontend | Tailwind CSS v4 + Alpine.js + Vite |
| Cola | Database driver (`php artisan queue:work`) |
| Base de datos | MySQL |
| SMS | Driver interface: `log` (dev), `infobip`, `twilio` |

---

## Arquitectura de Base de Datos (32 tablas)

### Usuarios & Auth Admin
| Tabla | Descripción |
|-------|-------------|
| `users` | Usuarios del panel admin con roles |
| `user_profiles` | Avatar, departamento, cargo |
| `login_history` | Registro de accesos con IP, user_agent, éxito/fallo |
| `roles`, `permissions`, `model_has_roles`, `model_has_permissions` | Spatie |

### Geografía (seeded con Colombia)
| Tabla | Descripción |
|-------|-------------|
| `countries` | Colombia seeded |
| `departments` | 8 departamentos colombianos |
| `cities` | 15+ ciudades colombianas |
| `zones` | 9 zonas para Bogotá |
| `points_of_sale` | Puntos de venta por zona/ciudad |

### Catálogo
| Tabla | Descripción |
|-------|-------------|
| `categories` | Categorías de productos |
| `products` | Productos con SKU y precio |

### Campañas & Cupones (núcleo del sistema)
| Tabla | Descripción |
|-------|-------------|
| `campaigns` | Agrupador de lotes. type: discount/loyalty/referral/promo |
| `coupon_batches` | Reglas: tipo descuento, valor, límites, fechas, applicable_to |
| `coupon_restrictions` | Polimórfico: restricciones por ciudad/zona/PDV/producto/categoría |
| `coupons` | Códigos individuales (tipo unique) o vacío (tipo general) |
| `coupon_redemptions` | Log completo: montos, canal, IP, reversed_at |

### Clientes
| Tabla | Descripción |
|-------|-------------|
| `customers` | Clientes finales: doc, teléfono, ciudad, status |
| `customer_meta` | key/value flexible para datos adicionales |

### Términos & Condiciones (Ley 1581)
| Tabla | Descripción |
|-------|-------------|
| `legal_documents` | Versiones de T&C, privacidad, consentimiento SMS (seeded v1.0) |
| `document_acceptances` | Aceptación por cliente con IP + timestamp |

### SMS
| Tabla | Descripción |
|-------|-------------|
| `sms_providers` | Configuración de proveedores (JSON encrypted) |
| `sms_campaigns` | Campañas masivas con filtros JSON |
| `sms_recipients` | Envíos individuales: estado, mensaje enviado, error |
| `sms_opt_outs` | Lista negra de teléfonos |

### API & Seguridad
| Tabla | Descripción |
|-------|-------------|
| `api_clients` | Clientes API: credenciales, IPs permitidas, rate limit |
| `api_request_logs` | Cada request con SHA256 hash (anti-replay) |
| `audit_logs` | Auditoría de CUD en entidades críticas |
| `security_alerts` | Alertas de seguridad: brute force, anomalías |

---

## Módulos del Panel Admin

| Módulo | Ruta | Descripción |
|--------|------|-------------|
| Dashboard | `/admin` | 8 métricas + redenciones recientes + top lotes |
| Usuarios | `/admin/users` | CRUD + asignar roles |
| Roles | `/admin/roles` | Crear roles con permisos |
| Geografía | `/admin/geography` | Ver países/depts/ciudades/zonas/PDVs |
| Campañas | `/admin/campaigns` | CRUD completo |
| Lotes de Cupones | `/admin/coupon-batches` | Crear, activar/pausar, exportar |
| Redenciones | `/admin/redemptions` | Log con filtros + reversión |
| Clientes | `/admin/customers` | CRUD + bloquear/desbloquear |
| Documentos Legales | `/admin/legal-documents` | Crear versiones + publicar |
| Campañas SMS | `/admin/sms-campaigns` | Crear, filtrar, enviar/programar |
| API Clients | `/admin/api-clients` | Generar credenciales + logs |
| Auditoría | `/admin/audit` | Log de todas las acciones |

---

## API REST v1

**Base:** `/api/v1/` — requiere `X-Client-Id` + `X-Client-Secret`

| Método | Endpoint | Rate Limit | Descripción |
|--------|----------|-----------|-------------|
| POST | `/api/v1/coupons/validate` | 30/min | Valida código + calcula descuento |
| POST | `/api/v1/coupons/redeem` | 10/min | Redime cupón (marca como usado) |
| GET | `/api/v1/coupons/{code}` | 60/min | Info del cupón |
| POST | `/api/v1/customers/register` | 60/min | Registra cliente |
| GET | `/api/v1/customers/{document}` | 60/min | Consulta por doc o teléfono |
| POST | `/api/v1/customers/accept-terms` | 60/min | Acepta documentos legales |
| GET | `/api/v1/legal/{type}` | público | Documento legal vigente |
| GET | `/api/health` | público | Health check |

### Ejemplo validate
```bash
curl -X POST http://localhost:8001/api/v1/coupons/validate \
  -H "X-Client-Id: ch_demo_client" \
  -H "X-Client-Secret: demo_secret_J0d3o77ZuFjxuF4RkXHIeNo95kNNfFaj" \
  -H "Content-Type: application/json" \
  -d '{"code": "PROMO25", "amount": 50000, "phone": "3001234567"}'
```

### Respuesta validate/redeem
```json
{
  "valid": true,
  "code": "PROMO25",
  "discount_type": "percentage",
  "discount_value": 25.00,
  "discount_amount": 12500.00,
  "original_amount": 50000.00,
  "final_amount": 37500.00,
  "message": "Cupón aplicado exitosamente",
  "coupon": {
    "starts_at": "2026-01-01",
    "expires_at": "2026-12-31",
    "min_purchase": 20000,
    "max_purchase": null,
    "uses_remaining": 48,
    "applicable_to": "all"
  },
  "meta": { "request_id": "uuid", "processed_at": "2026-04-11T10:00:00Z" }
}
```

---

## Páginas Públicas

| URL | Descripción |
|-----|-------------|
| `/terminos-y-condiciones` | T&C vigente |
| `/politica-de-privacidad` | Política de datos (Ley 1581) |
| `/consentimiento-sms` | Consentimiento SMS |
| `/aceptar/{type}` | Formulario de aceptación (nombre, doc, tel, email, checkbox) |
| `/cupon/{code}` | Landing page del cupón con calculadora de descuento en vivo |

---

## Servicios Clave

### CouponService (`app/Services/CouponService.php`)
- `validate(code, amount, customerId, context)` — retorna valid/invalid con descuento calculado
- `redeem(code, amount, customerId, context)` — usa `DB::transaction` + `lockForUpdate()` para evitar race conditions
- `reverse(redemptionId, userId)` — reversa una redención
- `generateCodes(batch, quantity)` — genera códigos con prefijo, 3 intentos anti-colisión

### SmsService (`app/Services/SmsService.php`)
- `send(phone, message)` — despacha por driver (log/infobip/twilio)
- `renderTemplate(template, vars)` — reemplaza `{code}`, `{name}`, `{discount}`
- Verifica `SmsOptOut` antes de enviar

### AuditService (`app/Services/AuditService.php`)
- `AuditService::log(event, entityType, entityId, old, new)` — escribe en audit_logs
- `AuditService::alert(type, severity, description, context, ip)` — escribe en security_alerts

### ApiAuthenticate Middleware (`app/Http/Middleware/ApiAuthenticate.php`)
- Valida `X-Client-Id` + `X-Client-Secret`
- Verifica IP en whitelist del cliente
- Verifica expiración del cliente
- Loguea cada request con SHA256 hash (anti-replay)
- Actualiza `last_used_at`

---

## Jobs en Cola

| Job | Timeout | Descripción |
|-----|---------|-------------|
| `GenerateUniqueCoupons` | 600s | Genera N códigos únicos para un lote |
| `ProcessSmsCampaign` | 3600s | Envía SMS a todos los recipientes en chunks, 100ms entre envíos |

---

## Lógica de Cupones

### Tipos de lote
- **unique:** N códigos individuales generados por job. Formato: `{PREFIX}{8_chars_random}`
- **general:** Un solo código compartido, límite por `max_uses_total`

### Cálculo de descuento
- `percentage`: `discount = amount × (value / 100)`
- `fixed`: `discount = min(value, amount)`

### Validaciones aplicadas
- `min_purchase_amount` — monto mínimo de compra
- `max_purchase_amount` — monto máximo (nullable)
- `max_uses_total` — usos totales del lote
- `max_uses_per_user` — usos por cliente
- `max_uses_per_day` — usos diarios
- `start_date` / `end_date` — ventana de validez
- `status` — batch debe estar en `active`

---

## Seguridad

| Mecanismo | Implementación |
|-----------|---------------|
| API auth | X-Client-Id + X-Client-Secret hasheado con bcrypt |
| IP whitelist | `allowed_ips` JSON en api_clients |
| Anti-replay | SHA256 hash por request en api_request_logs |
| Rate limiting | throttle:30,1 (validate) / throttle:10,1 (redeem) / throttle:5,1 (login) |
| Brute force | Después de 5 logins fallidos en 10 min → security_alert |
| Audit log | Cada CUD en entidades críticas |
| Soft deletes | Clientes, cupones, lotes, campañas — nada se elimina definitivamente |
| CSRF | Habilitado en todas las rutas web |
| Datos personales | `document_acceptances` con IP + timestamp en cada aceptación |

---

## Seeders Ejecutados

| Seeder | Datos creados |
|--------|--------------|
| `RolesAndPermissionsSeeder` | Roles: super-admin, admin, operador, analista |
| `AdminUserSeeder` | `admin@cuponeshub.com` / `Admin@2026!` con rol super-admin |
| `GeographySeeder` | Colombia, 8 departamentos, 15+ ciudades, 9 zonas Bogotá |
| `LegalDocumentsSeeder` | T&C v1.0, Política de Privacidad v1.0, Consentimiento SMS v1.0 |
| `ApiClientSeeder` | Cliente demo: ch_demo_client |

---

## Comandos Frecuentes

```bash
# Iniciar servidor
cd e:/cupones-laravel
php artisan serve --port=8001

# Procesar jobs en cola (generación cupones + envío SMS)
php artisan queue:work

# Compilar assets frontend
npm run build
npm run dev        # modo watch

# Migraciones
php artisan migrate
php artisan migrate:fresh --seed   # reset completo

# Caché
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## Estructura de Archivos Clave

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          ← 14 controladores del panel
│   │   ├── Api/V1/         ← CouponController, CustomerController, LegalController
│   │   ├── Auth/           ← LoginController
│   │   └── Public/         ← CouponLandingController, LegalPageController
│   └── Middleware/
│       ├── ApiAuthenticate.php     ← auth de la API
│       └── TrackLoginHistory.php   ← rastrea logins web
├── Models/                 ← 26 modelos
├── Services/               ← CouponService, SmsService, AuditService
└── Jobs/                   ← GenerateUniqueCoupons, ProcessSmsCampaign

resources/views/
├── layouts/admin.blade.php ← sidebar con 14 módulos
├── admin/                  ← vistas CRUD de todos los módulos
└── public/                 ← landing cupón, páginas legales

database/
├── migrations/             ← 32 migraciones
└── seeders/                ← 5 seeders

routes/
├── web.php                 ← auth + admin panel + páginas públicas
└── api.php                 ← API v1 + health check
```

---

## Notas Importantes

- **SMS en desarrollo:** Driver configurado como `log` — los mensajes aparecen en `storage/logs/laravel.log`
- **Puerto:** 8001 porque 8000 está ocupado por el proyecto **Sr WOK** (`e:/pedidos-laravel`)
- **Ley 1581:** Toda recolección de datos de clientes requiere consentimiento explícito registrado en `document_acceptances`
- **Race conditions:** `CouponService::redeem()` usa `DB::transaction` + `lockForUpdate()` para evitar doble redención bajo carga concurrente
- **Jobs grandes:** Para lotes de miles de cupones o campañas SMS masivas, el job se despacha a la cola — requiere `php artisan queue:work` corriendo
