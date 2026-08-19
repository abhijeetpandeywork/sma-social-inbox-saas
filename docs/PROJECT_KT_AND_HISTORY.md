# Social Inbox Automation SaaS — Project Knowledge Transfer (KT) & Architecture Guide

## 1. Executive Summary & Hostinger Shared Hosting Context
This application is an enterprise-grade multi-tenant Social Inbox Automation SaaS tailored specifically for **Hostinger Shared Hosting** (PHP 8.2 / 8.3, MySQL 8.0, Shared CPU/RAM, Database Queue Driver, Cron-based 1-minute queue processing with DB locks, No Redis/Daemon processes).

- **Production Live URL**: [https://sma.digitalrubix.site](https://sma.digitalrubix.site)
- **API Health Endpoint**: [https://sma.digitalrubix.site/health](https://sma.digitalrubix.site/health)
- **API Setup & Accounts Guide**: [https://sma.digitalrubix.site/connections](https://sma.digitalrubix.site/connections)
- **GitHub Repository**: [https://github.com/abhijeetpandeywork/sma-social-inbox-saas](https://github.com/abhijeetpandeywork/sma-social-inbox-saas)

---

## 2. Platform Connections & Webhook Setup Guide

### Meta (Instagram Business + Facebook Pages) Webhooks
- **Callback URL**: `https://sma.digitalrubix.site/api/webhooks/meta`
- **Verify Token**: `social_inbox_secret_token`
- **Signature Algorithm**: HMAC-SHA256 (`X-Hub-Signature-256` header)
- **Supported Webhook Subscriptions**:
  - `comments`: Triggers auto-reply, intent scoring, phone extraction, comment hiding.
  - `messages`: Processes DMs.
  - `feed`: Handles post-level engagement.

### Twitter / X Pay-Per-Use Tracking
- API costs tracked per client: `$0.005` per read, `$0.015` per DM write, `$0.20` per link post.

### YouTube Data API v3 & GMB Reviews
- Cron-driven polling service checks YouTube video comments and Google Business Profile reviews every 15 minutes.

---

## 3. Seeded RBAC Access Accounts (Password: `password`)

| Role | Email | 2FA Secret Code | Scope |
|---|---|---|---|
| **Agency Admin** | `admin@digitalrubix.com` | `123456` | Cross-Tenant Global Access |
| **Client Manager** | `manager@digitalrubix.com` | N/A | Assigned Clients (`Sai Business Solutions`, `Bagnomy`) |
| **Team Executive** | `exec@digitalrubix.com` | N/A | Single Client (`Sai Business Solutions`) |

---

## 4. Automated Deployment & Maintenance Commands

### Hostinger Automated Deployment
Run from workspace root:
```bash
node deploy_sma_digitalrubix.cjs
```

### Database Backup Command
```bash
php artisan app:backup-database
```
