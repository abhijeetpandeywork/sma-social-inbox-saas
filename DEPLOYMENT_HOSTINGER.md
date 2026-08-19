# Hostinger Shared Hosting Deployment Guide — Social Inbox SaaS

This guide provides step-by-step instructions for deploying the **Social Inbox Automation SaaS** to Hostinger Shared Web Hosting (Business or Premium plans).

---

## 1. Hosting Requirements Checklist

- [x] Hostinger Business or Premium Shared Hosting Plan (with SSH & Cron job access confirmed).
- [x] PHP 8.2 or 8.3 selected in Hostinger hPanel.
- [x] Enabled PHP extensions: `zip`, `pdo_mysql`, `curl`, `mbstring`, `openssl`.
- [x] MySQL 8.0 database created in Hostinger hPanel.

---

## 2. Directory Structure on Hostinger

To ensure security on shared hosting, place the Laravel project files outside the web root (`public_html`) or configure `.htaccess` to point to `public`:

```text
/home/u123456789/
├── domain.com/               <-- Web root directory
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/               <-- Point hPanel document root here
│   ├── .env
│   └── artisan
```

### Alternative: `.htaccess` Redirection inside `public_html`
If your shared plan document root is fixed to `public_html`, copy the contents of `public/` into `public_html/` and update `public_html/index.php`:

```php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
```

---

## 3. Hostinger Cron Job Setup (CRITICAL)

Since shared hosting cannot run persistent background daemons (Supervisor/Redis), **all queue processing, SLA escalations, token health checks, and database backups are driven by Hostinger's Cron Manager**.

In Hostinger hPanel &rarr; **Advanced** &rarr; **Cron Jobs**, add the following 1-minute cron entry:

```bash
* * * * * cd /home/u123456789/domains/yourdomain.com && php artisan schedule:run >> /dev/null 2>&1
```

> **How it works:**
> Every minute, `schedule:run` triggers `php artisan queue:work --stop-when-empty --max-time=50`.
> Cache-backed database locking (`withoutOverlapping(10)`) prevents concurrent runs from overlapping if an execution takes longer than 60 seconds.

---

## 4. Environment (.env) Configuration

Create or update `.env` on your Hostinger server with your production database credentials:

```env
APP_NAME="Social Inbox SaaS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u123456789_socialinbox
DB_USERNAME=u123456789_dbuser
DB_PASSWORD=your_secure_password

QUEUE_CONNECTION=database
CACHE_STORE=database

META_VERIFY_TOKEN=your_custom_webhook_secret
META_APP_SECRET=your_meta_app_secret
ANTHROPIC_API_KEY=sk-ant-api03-...
```

---

## 5. Deployment Commands via SSH

Run these commands in your SSH terminal on Hostinger:

```bash
# 1. Install Production Dependencies
composer install --no-dev --optimize-autoloader

# 2. Generate Key & Run Database Migrations
php artisan key:generate
php artisan migrate --force

# 3. Seed Production Initial Admin User
php artisan db:seed --force

# 4. Cache Config, Routes & Views for Speed
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 6. Uptime & Cron Monitoring Setup

To ensure you are instantly notified if Hostinger's cron stops firing or DB connectivity drops:

1. Sign up for a free account on [UptimeRobot](https://uptimerobot.com).
2. Add a new HTTP(s) Monitor pointing to: `https://yourdomain.com/health`
3. Set Monitoring Interval to **5 minutes**.
4. Configure alerts via Email or WhatsApp. The `/health` API will automatically return HTTP 503 if the last cron queue run is older than 5 minutes.
