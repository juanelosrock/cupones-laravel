# CuponesHub — Plataforma de Gestión de Cupones de Descuento

## Contexto del Proyecto
CuponesHub es una plataforma Laravel 12 independiente del proyecto Sr WOK, diseñada para:
- Gestionar usuarios/roles/permisos del panel admin
- Crear y administrar cupones de descuento (porcentaje o monto fijo) con tope máximo de descuento
- Rastrear redenciones con log completo (quién, cuándo, dónde, canal)
- Exponer una API REST para que sistemas POS externos validen/rediman cupones
- Gestionar clientes con aceptación de T&C / datos personales (Ley 1581 Colombia)
- Enviar SMS masivos con cupones segmentados por ciudad, zona, punto de venta
- Enviar campañas de Email con cupones
- Enviar campañas de WhatsApp masivas con plantillas Zenvia o texto libre

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
| SMS drivers | `log` (dev) · `infobip` · `labsmobile` · `zenvia` |
| Email drivers | `log` (dev) · `infobip` · `zenvia` |
| WhatsApp drivers | `log` (dev) · `zenvia` (API v2) |

---

## Arquitectura de Base de Datos (~36 tablas)

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
| `coupon_batches` | Reglas: tipo descuento, valor, límites, fechas, applicable_to, max_discount_amount |
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

### Email
| Tabla | Descripción |
|-------|-------------|
| `email_campaigns` | Campañas masivas de email |
| `email_recipients` | Envíos individuales: estado, mensaje, error |

### WhatsApp
| Tabla | Descripción |
|-------|-------------|
| `whatsapp_campaigns` | Campañas masivas (text/template, fuente: campaign o csv) |
| `whatsapp_recipients` | Destinatarios: phone, name, assigned_coupon_code, status, message_sent |
| `whatsapp_opt_outs` | Lista negra de teléfonos WhatsApp |

### Configuración
| Tabla | Descripción |
|-------|-------------|
| `settings` | Key/value para configuración dinámica (drivers, credenciales encriptadas) |

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
| Lotes de Cupones | `/admin/coupon-batches` | Crear, activar/pausar, exportar, tope de descuento |
| Redenciones | `/admin/redemptions` | Log con filtros + reversión |
| Clientes | `/admin/customers` | CRUD + bloquear/desbloquear + filtro por fecha |
| Documentos Legales | `/admin/legal-documents` | Crear versiones + publicar |
| Campañas SMS | `/admin/sms-campaigns` | Crear, filtrar, enviar/programar |
| Campañas Email | `/admin/email-campaigns` | Crear, filtrar, enviar/programar |
| Campañas WhatsApp | `/admin/whatsapp-campaigns` | Crear (desde campaña o CSV), enviar plantillas Zenvia |
| API Clients | `/admin/api-clients` | Generar credenciales + logs + docs + tester |
| Proveedores | `/admin/providers` | Configurar SMS/Email/WhatsApp drivers y credenciales |
| Auditoría | `/admin/audit` | Log de todas las acciones |
| Manual | `/admin/manual` | Manual de usuario del sistema |

---

## API REST v1

**Base:** `/api/v1/` — requiere `X-Client-Id` + `X-Client-Secret`

| Método | Endpoint | Rate Limit | Descripción |
|--------|----------|-----------|-------------|
| POST | `/api/v1/coupons/validate` | 30/min | Valida código + calcula descuento (con tope) |
| POST | `/api/v1/coupons/redeem` | 10/min | Redime cupón (marca como usado) |
| GET | `/api/v1/coupons/{code}` | 60/min | Info del cupón incluyendo max_discount_amount |
| POST | `/api/v1/customers/register` | 60/min | Registra cliente |
| GET | `/api/v1/customers/{document}` | 60/min | Consulta por doc o teléfono |
| POST | `/api/v1/customers/accept-terms` | 60/min | Acepta documentos legales |
| GET | `/api/v1/legal/{type}` | público | Documento legal vigente |
| GET | `/api/v1/legal/{type}/versions` | público | Historial de versiones del documento |
| GET | `/api/v1/legal/{type}/versions/{v}` | público | Versión específica del documento |
| GET | `/api/health` | público | Health check |

Tipos de documento legal válidos: `terms`, `privacy`, `sms_consent`, `commercial`

### Respuesta validate/redeem (con tope de descuento)
```json
{
  "valid": true,
  "code": "PROMO50",
  "discount_type": "percentage",
  "discount_value": 50.00,
  "effective_discount_value": 40.00,
  "discount_capped": true,
  "max_discount_amount": 40000.00,
  "discount_amount": 40000.00,
  "original_amount": 100000.00,
  "final_amount": 60000.00,
  "message": "Cupón aplicado. Descuento máximo aplicado: $40.000",
  "coupon": {
    "starts_at": "2026-01-01",
    "expires_at": "2026-12-31",
    "min_purchase": 20000,
    "max_purchase": null,
    "max_discount_amount": 40000.00,
    "uses_remaining": 48,
    "applicable_to": "all"
  },
  "meta": { "request_id": "uuid", "processed_at": "2026-05-26T10:00:00-05:00" }
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
- `calculateDiscount(batch, amount)` — retorna `[float $discountAmount, bool $wasCapped]`
  - Si hay `max_discount_amount` y el descuento calculado lo supera → retorna el tope y `wasCapped = true`
  - La respuesta API incluye `effective_discount_value` (% real aplicado) cuando hay tope

### SmsService (`app/Services/SmsService.php`)
- `send(phone, message)` — despacha por driver (log / infobip / labsmobile / zenvia)
- `renderTemplate(template, vars)` — reemplaza `{code}`, `{name}`, `{discount}`
- Verifica `SmsOptOut` antes de enviar

### EmailService (`app/Services/EmailService.php`)
- `send(to, subject, body)` — despacha por driver (log / infobip / zenvia)
- Sender name por defecto: `Promocion` (configurable en panel Proveedores)

### WhatsAppService (`app/Services/WhatsAppService.php`)
- `send(phone, contentType, text, templateId, fields, externalId)` — despacha por driver
- `renderTemplate(template, vars)` — igual que SmsService
- `getTemplates()` — consulta `GET /v2/templates?channel=whatsapp` de Zenvia y retorna las plantillas
- Driver `zenvia`: usa API v2 `POST /v2/channels/whatsapp/messages`
  - `contentType='template'`: payload `{type:'template', templateId, fields:(object)}`
  - `contentType='text'`: payload `{type:'text', text}`

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
| `ProcessSmsCampaign` | 3600s | Envía SMS a todos los recipientes, 100ms entre envíos |
| `ProcessEmailCampaign` | 3600s | Envía Emails a todos los recipientes, 100ms entre envíos |
| `ProcessWhatsAppCampaign` | 3600s | Envía WhatsApp (text o template) a todos los recipientes, 100ms entre envíos |

---

## Lógica de Cupones

### Tipos de lote
- **unique:** N códigos individuales generados por job. Formato: `{PREFIX}{8_chars_random}`
- **general:** Un solo código compartido, límite por `max_uses_total`

### Cálculo de descuento (con tope)
- `percentage`: `discount = amount × (value / 100)`; si `discount > max_discount_amount` → `discount = max_discount_amount`
- `fixed`: `discount = min(value, amount)`; si `discount > max_discount_amount` → `discount = max_discount_amount`
- `effective_discount_value` en respuesta API = porcentaje real que produce el descuento aplicado

### Validaciones aplicadas
- `min_purchase_amount` — monto mínimo de compra
- `max_purchase_amount` — monto máximo de compra (rechaza la transacción si supera)
- `max_discount_amount` — tope máximo del descuento (aplica cap, no rechaza)
- `max_uses_total` — usos totales del lote
- `max_uses_per_user` — usos por cliente
- `max_uses_per_day` — usos diarios
- `start_date` / `end_date` — ventana de validez
- `status` — batch debe estar en `active`

---

## WhatsApp Campaigns — Flujo Completo

### Crear campaña
1. **Destinatarios**: elegir entre "Clientes de campaña" o "Subir CSV"
   - CSV: auto-detecta separador (`,` o `;`), normaliza teléfonos colombianos 10 dígitos → `57xx`, deduplica, límite 10.000 filas
2. **Cupón**: opcional — vincula un lote (general o único). Las variables `{code}` y `{discount}` se resuelven en el job
3. **Tipo de mensaje**:
   - `template`: plantilla pre-aprobada en Zenvia. Botón "Cargar desde Zenvia" consulta la API y muestra combobox con búsqueda. Al seleccionar, auto-rellena variables detectadas en el BODY de la plantilla
   - `text`: texto libre con variables `{name}`, `{code}`, `{discount}`, `{phone}` (solo para sesiones activas 24h)
4. **Programar**: opcional, estado `scheduled`; sin fecha → estado `draft`

### Estado de campañas
`draft` → `sending` → `sent` | `failed` | `cancelled`

### Acciones disponibles en show
- **Enviar ahora** (draft/scheduled/failed)
- **Cancelar** (draft/scheduled)
- **Reintentar fallidos** (sent/failed)
- **Reintentar destinatario individual**
- **Sincronizar destinatarios** (si hay clientes nuevos en la campaña origen)
- **Vincular/desvincular lote de cupones**

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
| Email duplicado | Verificación previa antes de `updateOrCreate` + catch `UniqueConstraintViolationException` |

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

# Procesar jobs en cola (generación cupones + envío SMS/Email/WhatsApp)
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
│   │   ├── Admin/          ← 15+ controladores del panel
│   │   │   ├── WhatsAppCampaignController.php  ← templatesList(), syncRecipients(), linkBatch()
│   │   │   ├── SmsCampaignController.php
│   │   │   ├── EmailCampaignController.php
│   │   │   ├── CouponBatchController.php       ← max_discount_amount
│   │   │   └── CustomerController.php          ← filtros date_from / date_to
│   │   ├── Api/V1/         ← CouponController, CustomerController, LegalController
│   │   ├── Auth/           ← LoginController
│   │   └── Public/         ← CouponLandingController, LegalPageController
│   └── Middleware/
│       ├── ApiAuthenticate.php     ← auth de la API
│       └── TrackLoginHistory.php   ← rastrea logins web
├── Models/                 ← 28+ modelos
├── Services/               ← CouponService, SmsService, EmailService, WhatsAppService, AuditService
└── Jobs/                   ← GenerateUniqueCoupons, ProcessSmsCampaign, ProcessEmailCampaign, ProcessWhatsAppCampaign

resources/views/
├── layouts/admin.blade.php ← sidebar con todos los módulos
├── admin/
│   ├── whatsapp-campaigns/ ← create (CSV + combobox Zenvia), show, index
│   ├── sms-campaigns/
│   ├── email-campaigns/
│   ├── coupon-batches/     ← create/edit con max_discount_amount
│   ├── customers/          ← index con filtros de fecha
│   ├── api-clients/        ← docs.blade.php + tester.blade.php
│   └── manual/             ← index.blade.php (actualizar con cada cambio)
└── public/                 ← landing cupón, páginas legales

database/
├── migrations/             ← 36+ migraciones
└── seeders/                ← 5 seeders

routes/
├── web.php                 ← auth + admin panel + páginas públicas
└── api.php                 ← API v1 + health check
```

---

## Notas Importantes

- **Timezone:** `America/Bogota` (UTC-5) — configurado en `config/app.php`; `now()` retorna hora colombiana
- **SMS/Email/WhatsApp en desarrollo:** Driver configurado como `log` — los mensajes aparecen en `storage/logs/laravel.log`
- **Puerto:** 8001 porque 8000 está ocupado por el proyecto **Sr WOK** (`e:/pedidos-laravel`)
- **Ley 1581:** Toda recolección de datos de clientes requiere consentimiento explícito registrado en `document_acceptances`
- **Race conditions:** `CouponService::redeem()` usa `DB::transaction` + `lockForUpdate()` para evitar doble redención bajo carga concurrente
- **Jobs grandes:** Para lotes de miles de cupones o campañas masivas, el job se despacha a la cola — requiere `php artisan queue:work` corriendo
- **Sender name:** Todos los envíos (SMS, Email, WhatsApp) usan `Promocion` como nombre remitente por defecto; configurable en panel Proveedores
- **Setting model:** `Setting::get('key')` usa `rescue()` — retorna null si el valor fue encriptado con distinto APP_KEY. En producción, mantener fallbacks en `.env`
- **WhatsApp BIM:** Los envíos masivos SIEMPRE requieren tipo `template`; el tipo `text` solo funciona dentro de ventana de sesión activa de 24h
- **Modelos con nombres compuestos:** Declarar siempre `protected $table` explícito en modelos como `WhatsAppCampaign` para evitar que Laravel infiera nombres de tabla incorrectos

---

## Instrucciones para Claude

### Memoria del proyecto
- El historial completo de cambios vive en `C:\Users\QimeraServer\.claude\projects\e--cupones-laravel\memory\`
- El índice principal es `MEMORY.md` en esa misma carpeta
- **Cada vez que el usuario corrija código, lógica o forma de trabajo:** guardar inmediatamente en memoria antes de continuar
  - Bug arreglado o fix → añadir en `project_changelog.md` bajo "Correcciones y fixes"
  - Nueva funcionalidad → añadir en `project_changelog.md` bajo "Módulos y funcionalidades añadidas"
  - Corrección a forma de trabajo → crear o actualizar `feedback_*.md` y puntero en `MEMORY.md`
  - Decisión arquitectónica → añadir en `project_architecture.md`

### Flujo obligatorio al terminar cada tarea
1. Hacer `git add` de los archivos modificados
2. Hacer `git commit` con mensaje descriptivo
3. Hacer `git push origin main`
4. Dar al usuario el comando `git pull` para el servidor
5. Si hay migraciones nuevas, indicar `php artisan migrate`
6. Si hay cambios en config, indicar `php artisan config:clear`
7. Actualizar `project_changelog.md` en memoria con el cambio realizado

### Manual de usuario
- Actualizar `resources/views/admin/manual/index.blade.php` con cada cambio funcional visible al usuario — sin esperar que lo pida
