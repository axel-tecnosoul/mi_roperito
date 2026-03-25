# Deployment

The application requires Google reCAPTCHA credentials.

## Configure database connection

Set the database connection variables on the server or in a `.env` file located at the project root:

- `DB_HOST` – database host
- `DB_USER` – database user
- `DB_PASS` – database password
- `DB_NAME` – database name

Example using environment variables:

```bash
export DB_HOST="localhost"
export DB_USER="miroperito"
export DB_PASS="secret"
export DB_NAME="mi_roperito"
```

Ensure these variables are available to the web server process.

## Configure reCAPTCHA keys

Set the following environment variables on the production server or provide them in a `.env` file located at the project root:

- `RECAPTCHA_SITE_KEY` – public site key used by the client.
- `RECAPTCHA_SECRET_KEY` – secret key used for server-side verification.

### Using environment variables

```bash
export RECAPTCHA_SITE_KEY="<your site key>"
export RECAPTCHA_SECRET_KEY="<your secret key>"
```
Ensure these are loaded for the web server process (e.g. via the service configuration).

### Using a `.env` file

Create a file named `.env` with the keys:

```env
RECAPTCHA_SITE_KEY=<your site key>
RECAPTCHA_SECRET_KEY=<your secret key>
```

The `.env` file is ignored by Git and should never be committed. Keep it secure.

## Configure CRON Job for Payment Expiration

Set up automated payment expiration processing:

```bash
# Execute weekly on Mondays at 2:00 AM (RECOMMENDED)
0 2 * * 1 /usr/bin/php /path/to/htdocs/MiRoperito/admin/cronVencimientoPagosPendientes.php

# Alternative: Monthly on 1st day at 2:00 AM
0 2 1 * * /usr/bin/php /path/to/htdocs/MiRoperito/admin/cronVencimientoPagosPendientes.php
```

**Note**: 
- Weekly execution provides better responsiveness for "complete weeks" logic
- Logs are automatically generated at `admin/log_pagos_vencidos.txt`
- Requires parameter ID 10 configured in database (see README_vencimiento_pagos.md)

## Sistema de Pagos Vencidos (pagado=2)

### Implementación completada ✅
- **Estado:** `pagado=2` agregado para pagos vencidos
- **Automatización:** CRON configurable (mensual o semanal)  
- **Lógica:** Vencimientos por meses completos
- **Configuración:** Parámetro ID 10 en tabla `parametros` (meses)
- **Interfaces:** Actualizadas para mostrar "Vencido"

### Archivos modificados:
1. `cronVencimientoPagosPendientes.php` - Script de vencimiento automático
2. `listarPagosPendientes.php` - Filtros para estado vencido  
3. `ajaxProductosVendidos.php` - Display estado vencido
4. `cardVerVenta.php` - Display estado vencido
5. Múltiples interfaces - Soporte pagado=2

### Configuración CRON:
```bash
# Mensualmente (recomendado)
0 2 1 * * /usr/bin/php /path/to/admin/cronVencimientoPagosPendientes.php

# O semanalmente
0 2 * * 1 /usr/bin/php /path/to/admin/cronVencimientoPagosPendientes.php
```

### Parámetros:
- ID 10 en tabla `parametros`: Meses para vencimiento (default: 6)
- Los pagos se vencen por meses completos
- Ejemplo: hoy 24/03/2026 con 6 meses vence hasta 31/08/2025

## Local development

To test reCAPTCHA locally, set `APP_ENV=development` and provide development keys:

- `RECAPTCHA_SITE_KEY_DEV` – site key for local testing.
- `RECAPTCHA_SECRET_KEY_DEV` – secret key for local testing.

These can be supplied as environment variables:

```bash
export APP_ENV=development
export RECAPTCHA_SITE_KEY_DEV="<your dev site key>"
export RECAPTCHA_SECRET_KEY_DEV="<your dev secret key>"
```

Or through additional entries in `.env`:

```env
APP_ENV=development
RECAPTCHA_SITE_KEY_DEV=<your dev site key>
RECAPTCHA_SECRET_KEY_DEV=<your dev secret key>
```

When `APP_ENV` is set to `development`, the application will automatically use the development keys; otherwise the production keys are used.
