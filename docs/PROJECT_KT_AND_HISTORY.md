# Knowledge Transfer (KT) & Architecture Reference Manual
## Social Inbox Automation SaaS

**Project Name:** Social Inbox Automation SaaS  
**Target Domain:** `sma.digitalrubix.site`  
**Hostinger SSH Server:** `147.93.23.184:65002` (User: `u406313474`)  
**Repository:** Social Inbox Automation SaaS (GitHub)  
**Creation Date:** August 19, 2026  

---

## 1. Executive Summary & Purpose

The Social Inbox Automation SaaS captures leads from Instagram and Facebook comments/DMs (along with Twitter/X, YouTube, and Google Business Profile reviews) by detecting phone numbers (Indian & International regex) and buying intent (Anthropic Claude AI classification for Hinglish/ambiguous comments), auto-replying, auto-hiding the comment, scoring the lead (Hot/Warm/Cold), and routing it to assigned team members.

It is specifically architected to run reliably on **Hostinger SHARED Web Hosting** — no Redis, no Supervisor, no persistent daemons required.

---

## 2. Core Shared-Hosting Reliability Architecture

1. **Cron-Triggered Queue Engine**:
   - Queue Driver: `database` (stored in MySQL/SQLite `jobs` table).
   - Cron Execution: Runs every 1 minute: `php artisan queue:work --stop-when-empty --max-time=50`.
   - Overlap Prevention: Database-backed cache locking (`->withoutOverlapping(10)`).
2. **Exponential Retry Backoff & Dead-Letter Handling**:
   - `ProcessWebhookEventJob`: 5 retry attempts with escalating backoff schedule (`30s`, `2m`, `10m`, `30m`, `1h`).
   - Failed jobs after 5 attempts are automatically logged to `failed_actions` dead-letter queue table and `action_log`.
3. **Database-Backed Rate Limiting & Circuit Breaker**:
   - `RateLimiterService`: `rate_limit_counters` table tracks calls per platform/client window.
   - `CircuitBreakerService`: `platform_health` table tracks consecutive failure counts. Opens circuit (`held` status) after 10 consecutive failures, automatically probing and resuming when healthy.
4. **Monitoring & Health API**:
   - Endpoint: `/health`
   - Reports DB status, last successful cron queue execution timestamp, per-platform circuit health, and failed action counts. Returns HTTP 503 if cron has not executed within 5 minutes.

---

## 3. Database Schema Overview (PRD Section 7)

- `clients`: `id, agency_id, name, brand_logo, status, data_retention_months, created_at, updated_at`
- `team_members`: Authenticatable users with RBAC (`Agency Admin`, `Client Manager`, `Team Executive`), `assigned_clients` (JSON), and 2FA credentials.
- `platform_connections`: `client_id, platform, access_token (AES encrypted), refresh_token (AES encrypted), token_expires_at, health_status`
- `raw_events`: `client_id, platform, event_type, event_hash (SHA-256 unique), payload_json, processed`
- `leads`: `client_id, platform, source_comment_id, contact_phone (AES encrypted at field level), contact_name, contact_handle, status, score, duplicate_of_lead_id, assigned_to, captured_at`
- `automation_rules`: `client_id, platform, trigger_type, action_type, reply_template_variants (JSON A/B variants), business_hours_variant`
- `failed_actions`: Extended dead-letter queue table.
- `action_log`: Append-only audit log for auto-reply and auto-hide actions.
- `rate_limit_counters`: Windows-based call counter per platform per client.
- `platform_health`: Circuit breaker health status per platform.
- `pii_access_log`: Append-only audit log recording every view/export of lead PII (DPDP Act compliance).
- `sla_escalations`: Un-actioned Hot lead SLA breach tracking.
- `linkedin_alerts`: Notification-only alert records.

---

## 4. Auth & RBAC Roles

- **Agency Admin**: Access across all clients. Mandatory 2FA verification flow.
- **Client Manager**: Access restricted via `TenantScope` to assigned clients list (`assigned_clients`).
- **Team Executive**: Access restricted via `TenantScope` to assigned clients list.

### Default Seeded Demo Credentials (Password: `password`)
- Agency Admin: `admin@digitalrubix.com` (2FA Code: `123456`)
- Client Manager: `manager@digitalrubix.com`
- Team Executive: `exec@digitalrubix.com`

---

## 5. Test Suite

Run full automated test suite:
```bash
php artisan test
```
All 25 unit and feature tests pass cleanly with 72 assertions covering webhook verification, deduplication, RBAC tenant scoping, PII encryption, rate limiting, circuit breaking, Claude intent classification, SLA escalations, and database backups.
