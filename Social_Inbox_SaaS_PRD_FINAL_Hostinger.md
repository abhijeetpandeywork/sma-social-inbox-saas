# Social Inbox Automation SaaS — FINAL PRD (v3, Hostinger Shared Hosting)
### Multi-Client, Multi-Channel Lead Capture — Built for Shared Hosting Constraints
**Prepared for:** Digital Rubix
**Date:** August 19, 2026
**Hosting:** Hostinger Shared Web Hosting (confirmed)
**Build target:** Antigravity AI (PHP/Laravel)

---

## 1. Important Honesty Note Before We Start

The v2 design assumed a VPS (persistent queue workers, Redis, Supervisor, circuit breakers held in memory). **Shared hosting cannot run any of that** — no persistent background processes, no Redis, no Supervisor/systemd access. This version redesigns the reliability layer to be fully shared-hosting-native:

- **Instead of instant processing** → cron-triggered processing every 1 minute (Hostinger supports cron jobs on Business/Premium shared plans). Practical effect: a comment gets hidden within ~1–2 minutes of the phone number appearing, not instantly. This is a fine trade for this use case.
- **Instead of Redis** → all queue, rate-limit, and circuit-breaker state lives in MySQL. Slightly more DB load, fully workable at this scale.
- **Instead of Supervisor-managed worker** → cron calls `php artisan queue:work --stop-when-empty` every minute, so if one run fails/hangs, the next cron tick recovers automatically — this is actually a reasonable self-healing pattern even without Supervisor.

**One real ceiling to flag honestly:** if webhook volume grows very high (many hundreds of comments/minute across 50 clients), shared hosting's PHP execution time limits, memory caps, and concurrent-process limits will eventually become the bottleneck. Recommendation: build this exactly as below to run cleanly on shared hosting now, but monitor server load from month 1, and be ready to move just the queue-processing piece to a small VPS later if volume outgrows it. Nothing in this design has to change to make that move later — it's the same Laravel queue system either way, just where the cron/worker runs.

---

## 2. Platform Feasibility — Final Locked Matrix

| Platform | Comment automation | DM automation | v1 Scope |
|---|---|---|---|
| **Instagram** | ✅ Reply, hide | ✅ Reply | **Full autopilot** |
| **Facebook** | ✅ Reply, hide | ✅ Reply | **Full autopilot** |
| **Twitter/X** | ✅ Reply | ✅ Reply | **Full autopilot** (pay-per-use, billed separately) |
| **YouTube** | ✅ Reply, hide/moderate | ❌ No DM API on this platform | **Comments only** |
| **GMB** | ⚠️ Reviews only | ❌ Messaging API discontinued by Google (2024), no replacement | **Reviews only** |
| **LinkedIn** | ⚠️ Own-post replies only | ❌ Requires enterprise partner deal | **Alert-only** |
| **Pinterest** | ❌ No public comments API | ❌ No DM API for third parties | **Excluded entirely** |

---

## 3. Tech Stack (Hostinger Shared Hosting Compatible)

- **Backend:** PHP 8.2, Laravel (Hostinger shared hosting supports Laravel — confirm the plan includes SSH/Composer access, needed for setup; Hostinger Business/Premium plans do)
- **Database:** MySQL 8 (included in shared hosting)
- **Queue driver:** `database` (not Redis — Redis isn't available on shared plans)
- **Cron:** Hostinger's cron job manager, one entry running every 1 minute
- **Frontend:** Tailwind CSS + Alpine.js, Chart.js
- **PDF:** TCPDF
- **AI classification:** Anthropic Claude API (external HTTPS call, works fine from shared hosting — no special infra needed)
- **File storage:** Hostinger's included storage for backups/exports; for offsite backup, use an external destination (e.g., a low-cost cloud storage bucket) since shared hosting's own backups shouldn't be your only copy

---

## 4. Reliability Architecture (Redesigned for Cron, Not Persistent Workers)

### 4.1 The Processing Cycle
1. Webhook hits a PHP endpoint (works normally on shared hosting — it's just an HTTP request, no persistent process needed) → verifies signature → inserts into `raw_events` with a unique `event_hash` → returns 200 OK immediately. This part is unaffected by hosting type.
2. A **cron job every 1 minute** runs `php artisan queue:work --stop-when-empty --max-time=50` — processes whatever's queued, then exits cleanly before the next cron tick starts (the `--max-time=50` cap prevents overlapping runs if something's slow).
3. A **locking mechanism** (Laravel's cache lock, backed by the database driver) ensures if a run is still finishing when the next cron tick fires, the new one skips instead of running two instances in parallel against the same events.

### 4.2 Idempotency (unchanged from v2)
- `event_hash` (platform + object_id + timestamp) checked before insert — duplicate webhook deliveries are dropped, never double-processed.

### 4.3 Retry Logic (DB-based, no Redis needed)
- Laravel's database queue driver natively supports retry attempts and backoff — configure 5 attempts with escalating delay (30s, 2min, 10min, 30min, 1hr checked via `available_at` timestamps).
- After max attempts, move to `failed_actions` table (Laravel's built-in `failed_jobs` table extended with client/action context) — never silently dropped, always alerts the assigned team member.

### 4.4 Rate-Limiting (DB counters instead of Redis)
- A `rate_limit_counters` table tracks calls per platform per client per time window. Each queue job checks and increments this before calling the platform API, backing off if the client's budget for that window is used up. Slightly more DB reads than Redis would need, but entirely fine at this scale (dozens of clients, not millions of events).

### 4.5 Circuit Breaker (DB-based)
- A `platform_health` table tracks consecutive failure count per platform. If failures spike past a threshold (e.g., 10 consecutive failures within 5 minutes), new jobs for that platform are marked "held" instead of attempted, and a health-check job (run each cron cycle) probes with a lightweight call before resuming — same effect as an in-memory circuit breaker, just persisted to MySQL instead of Redis.

### 4.6 Token Health Automation
- A separate cron entry (daily) checks token expiry across all `platform_connections`, attempts silent refresh where supported, and emails/dashboard-alerts 7 days ahead of any expiry that needs manual re-auth.

### 4.7 Monitoring & Uptime
- `/health` endpoint reporting: DB connectivity, timestamp of the last successful cron-triggered queue run (so you can detect "cron stopped firing" — a real shared-hosting risk if a plan/cron config breaks), and per-platform `last_successful_call_at`.
- External uptime monitor (UptimeRobot or similar, free tier is enough) pings `/health` every 5 minutes and alerts via email/WhatsApp if the last queue run is older than ~5 minutes (signals cron isn't firing) or if any platform's health is down.

### 4.8 Backup & Disaster Recovery
- Hostinger's included backup feature as a first layer, **plus** a scheduled cron job that dumps the MySQL database (encrypted) and pushes it to external storage daily — do not rely solely on the hosting provider's own backup for a system holding client lead/PII data.
- Documented recovery steps, tested at least once after launch, not just written and forgotten.

---

## 5. Security & Leak-Proofing (unchanged principles, confirmed shared-hosting-workable)

- AES-256 encryption at rest for OAuth tokens and PII fields (phone numbers) — pure application-level encryption via Laravel, no special infra required, works identically on shared hosting.
- TLS enforced everywhere (Hostinger provides free SSL — confirm it's forced/redirected, not just available).
- RBAC (Agency Admin / Client Manager / Team Executive) with `client_id` filtering enforced at the query layer.
- 2FA mandatory for Agency Admin accounts.
- Append-only `action_log` and `pii_access_log` tables — no update/delete permissions even for admins.
- Signature verification on every webhook, parameterized queries throughout, CSRF protection, output escaping on all displayed comment text.
- Data retention policy aligned with India's DPDP Act — configurable per-client retention window, deletion-on-request process documented.

None of this changes with shared hosting — it's all application-layer, not infrastructure-dependent.

---

## 6. Lead-Generation Intelligence Layer (unchanged from v2, confirmed compatible)

- Phone regex (Indian + international formats) + Claude API intent classification for Hinglish/ambiguous comments — runs fine as a normal outbound HTTPS call from shared hosting.
- Lead scoring (Hot/Warm/Cold).
- Cross-platform deduplication by phone number.
- WhatsApp deep-link (`wa.me/<number>`) on captured leads.
- Business-hours-aware reply variants.
- A/B tested reply templates.
- SLA escalation for unactioned Hot leads (checked each cron cycle, not a persistent timer — perfectly adequate at 1-minute granularity).
- Weekly AI-generated lead-quality report per client (Claude + TCPDF), reusing your Meta Ads dashboard's reporting pattern.

---

## 7. Database Schema (final, cron/shared-hosting-adjusted)

```
clients
 - id, agency_id, name, brand_logo, status, data_retention_months, created_at

platform_connections
 - id, client_id, platform, access_token (encrypted), refresh_token (encrypted),
   token_expires_at, last_successful_call_at, health_status,
   platform_account_id, connected_by, created_at

raw_events
 - id, client_id, platform, event_type, event_hash (unique), payload_json,
   processed, created_at

leads  [contact_phone encrypted at field level]
 - id, client_id, platform, source_comment_id, source_dm_id,
   contact_phone (encrypted), contact_name, contact_handle,
   status, score, source_post_id, duplicate_of_lead_id,
   assigned_to, notes, captured_at

automation_rules
 - id, client_id, platform, trigger_type, trigger_value, action_type,
   reply_template_variants (json), business_hours_variant, is_active

jobs / failed_jobs (Laravel's native database queue tables, extended)
 - standard Laravel columns + client_id, action_type, attempt_count

action_log (append-only)
 - id, client_id, platform, action_type, target_id, status,
   error_message, attempt_count, created_at

rate_limit_counters
 - id, client_id, platform, window_start, call_count

platform_health
 - id, platform, consecutive_failures, status (healthy/held), last_checked_at

pii_access_log (append-only)
 - id, user_id, lead_id, action, created_at

team_members
 - id, agency_id, name, email, role, assigned_clients (json), two_factor_enabled

sla_escalations
 - id, lead_id, sla_deadline, escalated_to, escalated_at, resolved_at

linkedin_alerts
 - id, client_id, alert_type, source_url, status, created_at
```

---

## 8. UI/UX Guidelines

Navy / white / grey / gold palette matching Digital Rubix's Meta Ads dashboard. Dense, scannable layout. Status color-coding: green = auto-handled, amber = needs review, red = manual/failed action. System Health panel visible on the admin dashboard, now specifically showing "last cron run" timestamp so you can see at a glance if cron has stopped firing (the main shared-hosting risk to watch for).

---

## 9. How to Get API Access — Step-by-Step (unchanged, confirmed still accurate)

**Instagram + Facebook:** developers.facebook.com → Meta Business App → link to existing Business Manager → add Instagram Graph API + Messenger products → request `instagram_manage_comments`, `instagram_manage_messages`, `pages_manage_engagement`, `pages_messaging` via App Review (budget 1–3 weeks) → subscribe to webhooks per client Page/IG account once approved.

**Twitter/X:** developer.x.com → create Project/App → pay-per-use billing (~$0.005/read, $0.015/write-DM, $0.20 for link-containing posts) → OAuth 2.0 PKCE credentials per client.

**YouTube:** console.cloud.google.com → enable YouTube Data API v3 → OAuth consent screen (plan for Google verification once past 100 test users, relevant at ~50 clients) → 10,000 units/day default quota, request increase if needed.

**GMB:** developers.google.com/my-business → apply for Business Profile API access (approval required) → use for review read/reply only.

**LinkedIn:** self-serve API covers sign-in and own-content posting/replies only — do not pursue scraping tools, real ban risk to client accounts. Enterprise automation requires LinkedIn's Marketing Developer Platform partner program, separate initiative.

**Pinterest:** confirmed no comments/DM API for third parties — excluded from this build entirely.

---

## 10. Phased Build Plan

**Phase 1 — Reliable Core + Meta (Instagram + Facebook)**
- Multi-tenant auth (RBAC + 2FA), OAuth connection manager with health monitoring
- Webhook receiver with signature verification + idempotency
- Cron-triggered queue processing with DB-based retry/backoff/dead-letter + locking
- Lead detection (phone regex + Claude intent classification), lead scoring
- Auto-reply + auto-hide engine for IG/FB
- Unified inbox + lead pipeline UI + System Health panel

**Phase 2 — Twitter/X + WhatsApp Handoff**
- OAuth + usage-cost tracking, WhatsApp deep-link, SLA escalation system

**Phase 3 — YouTube + GMB (reviews)**
- Cron-polling pipeline for both, extend lead scoring/dedup

**Phase 4 — LinkedIn Alerts + Reporting + Hardening**
- LinkedIn notification-only module, weekly AI-generated client reports
- A/B template testing, business-hours variants
- Security review, backup/DR test, penetration test before full client rollout

---

## 11. Antigravity Kickoff Prompt (Final — Hostinger Shared Hosting)

```
Build a multi-tenant SaaS web application in PHP 8.2 (Laravel), MySQL,
Tailwind CSS, and Alpine.js called "Social Inbox Automation," designed to
run on Hostinger SHARED web hosting — no Redis, no persistent background
workers, no Supervisor/systemd access available. All queue processing
must be cron-triggered, not a long-running daemon.

PURPOSE: Capture leads from Instagram and Facebook comments/DMs (Phase 1
scope) by detecting phone numbers and buying intent, auto-replying,
auto-hiding the comment, scoring the lead, and routing it to a team
member — running reliably for 50+ client accounts on shared hosting.

BUILD THESE FOUNDATIONS FIRST:

1. Multi-tenant auth: Agency Admin (2FA mandatory), Client Manager, Team
   Executive roles, each scoped by client_id enforced at the query layer,
   not just the UI.

2. Platform Connection Manager: OAuth 2.0 for Instagram Business/Creator
   + Facebook Pages via Facebook Login for Business. Encrypt access/
   refresh tokens at rest (AES-256). A daily cron job checks token
   expiry, attempts silent refresh where supported, and alerts 7 days
   before any expiry needing manual re-auth.

3. Webhook receiver: a standard PHP/Laravel route (works normally on
   shared hosting, no special infra needed) that verifies Meta's
   X-Hub-Signature-256 header, generates a unique event_hash (platform +
   object_id + timestamp), rejects duplicates, inserts into raw_events,
   and returns 200 OK immediately without processing inline.

4. Queue processing via CRON, not a persistent worker: use Laravel's
   `database` queue driver (no Redis). Set up a cron entry to run every
   1 minute: `php artisan queue:work --stop-when-empty --max-time=50`.
   Use Laravel's cache lock (database-backed) to prevent overlapping
   runs if one cron tick is still finishing when the next fires.
   Configure 5 retry attempts with escalating backoff (30s, 2min, 10min,
   30min, 1hr) using the queue's available_at mechanism. After max
   attempts, move to a failed_actions table (extend Laravel's
   failed_jobs table with client_id and action_type) and trigger an
   immediate alert to the assigned team member — never silently drop
   a failed action.

5. Rate limiting and circuit breaker, both DB-based (no Redis): a
   rate_limit_counters table tracks calls per platform per client per
   time window, checked before each outbound API call. A platform_health
   table tracks consecutive failures per platform; if failures spike
   past a threshold, new jobs for that platform are held instead of
   attempted, with a lightweight probe each cron cycle to detect
   recovery and resume automatically.

6. Lead Detection Engine: regex for Indian (+91, 10-digit, spaced/
   dashed) and international phone formats. Call the Anthropic Claude
   API server-side (standard HTTPS call, no special infra needed on
   shared hosting) to classify ambiguous or Hinglish comments for buying
   intent ("bhai price batao," "interested," "call me") rather than
   relying on English-only keyword matching. Filter spam/bot comments
   before triggering any action. Score each lead Hot/Warm/Cold.

7. Auto-Reply + Auto-Hide Engine: per-client, per-platform rules with
   multiple reply-template variants (A/B testing) and a business-hours-
   aware variant. After a lead is captured and replied to, call the
   Graph API hide-comment endpoint and log to an append-only action_log
   table (no update/delete permitted, even for admins).

8. PII protection: encrypt the contact_phone column at the field level
   (separate from token encryption). Log every view/export of lead PII
   to an append-only pii_access_log table.

9. Deduplication: merge leads sharing a phone number across platforms
   into a single lead record via duplicate_of_lead_id.

10. WhatsApp handoff: generate a wa.me deep link on each lead card once
    a phone number is captured.

11. SLA escalation: configurable per-client deadline for Hot leads,
    checked each cron cycle — if unactioned past the deadline, notify
    the assigned executive, then escalate to the manager.

12. Dashboard UI: Unified Inbox, Lead Pipeline (Kanban), Automation
    Rules Builder with live template preview, Client Switcher, and a
    System Health panel showing: timestamp of the last successful
    cron-triggered queue run (critical for detecting if cron has
    stopped firing — the main shared-hosting failure risk), per-
    platform connection health, and failed-action count.

13. Expose a /health endpoint reporting DB connectivity, last successful
    queue run timestamp, and per-platform last_successful_call_at, for
    external uptime monitoring (e.g., UptimeRobot pinging every 5
    minutes, alerting if the last queue run is older than ~5 minutes).

14. Backup: in addition to Hostinger's built-in backups, add a daily
    cron job that dumps the MySQL database (encrypted) to external
    storage — do not rely solely on the hosting provider's backup for
    a system holding client lead/PII data.

DESIGN: Navy / white / grey / gold palette matching the existing Digital
Rubix Meta Ads dashboard. Dense, scannable, status color-coded (green =
auto-handled, amber = needs review, red = manual/failed action). Fully
responsive, mobile-friendly at minimum for the Lead Pipeline and System
Health views.

EXPLICITLY OUT OF SCOPE: LinkedIn automation (notification-only), Google
Business Profile messaging (API discontinued by Google in 2024), and
Pinterest entirely (no public comments or messaging API exists for
third-party apps on that platform).

Start by scaffolding the Laravel project, all database migrations
(including failed_actions, rate_limit_counters, platform_health, and
pii_access_log tables), the multi-tenant auth + RBAC system, and the
idempotent webhook receiver + cron-triggered queue skeleton with
retry/backoff and locking BEFORE building any dashboard UI. Verify the
Hostinger plan includes SSH and cron job access before beginning setup —
required for Composer/Artisan commands and the queue cron entry.
```

---

## 12. Open Decisions Before Build Starts

1. **Confirm Hostinger plan tier** — needs SSH access + cron jobs + enough PHP memory/execution time for Laravel + Composer. Business or Premium shared plan, not the entry-level plan.
2. **Claude API budget** for intent classification volume across 50 clients' daily comment traffic.
3. **Data retention period** per client contract (DPDP Act alignment).
4. **Pricing model** — whether Twitter/X pay-per-use and Claude API costs are passed through to clients or absorbed.
5. **Growth trigger point** — define in advance what load level (e.g., X webhook events/minute or Y clients) signals it's time to move queue processing to a small VPS, so it's a planned upgrade rather than a reactive scramble.
