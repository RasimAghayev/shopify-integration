# Repository Audit — Shopify Integration

**Baseline:** worktree `phase-3/audit-plan` at `420fb6c1975e68b6d5353b1b6af5cfa69622d636`.
**Audit mode:** source/config/docs review and redacted working-tree secret scan. No application/dependency edits, CI/Docker execution, dependency-bot PR operations, or production access. This audit's documentation is being submitted in a docs-only PR.

## Inventory

- **Business:** Shopify catalog sync and administration, with a Laravel API and Nuxt admin UI.
- **Languages:** PHP, TypeScript/JavaScript, SQL migrations, YAML, shell, Nginx configuration, Docker/Compose.
- **Modules:** Laravel `backend/src` Domain/Application/Infrastructure/Presentation; Vue/Nuxt `frontend/src` pages/components/composables/Pinia stores/types/utils; Nginx and backup/restore scripts.
- **Services:** PHP-FPM API, Redis queue worker/cache, Nuxt server, PostgreSQL, Nginx.
- **External integration:** Shopify Admin REST and global MCP code paths.
- **Tracked tests:** 12 backend PHPUnit test files plus test base; four frontend Vitest files. Frontend source test declarations counted 68; no measured coverage. No tracked Playwright specs or config.
- **Existing docs:** verbose README, VPS deployment guide, front-end design note, SOLID/TDD guide. The four Phase 3 architecture/audit/debt/roadmap docs were absent before this audit.
- **License:** root `LICENSE` declares MIT. No full transitive license review or Shopify terms review was performed.

## Current dependencies (not the older Phase 2 snapshot)

| Layer | Manifest | Lock / image evidence | Audit note |
|---|---|---|---|
| PHP / framework | PHP `^8.4`, Laravel `^13.0`, Guzzle `^7.10` | PHP image `8.5-fpm-alpine`; composer.lock Laravel `v12.69.0`, Guzzle `7.16.1`, PHPUnit `11.5.57`, PHPStan `2.1.40` | Laravel manifest/lock mismatch is confirmed from current files. Composer validate/outdated could not run because PHP and Composer are not installed on the host. |
| Frontend | Nuxt `^4.6.3`, Vue `^3.4.0`, Pinia `^2.1.7`, `@pinia/nuxt ^0.11.3`; dev tools include Vitest, ESLint, TypeScript, Playwright | Lock resolves Nuxt `4.6.3`, Vue `3.5.45`, Pinia `2.3.1`, Vitest `4.1.12`, ESLint `10.14.0`, TypeScript `5.9.3`; Docker uses Node `26-alpine` | Lock root declarations match `package.json`; latest stable versions must be refreshed from live registries in Phase 6. |
| Database/cache | — | PostgreSQL `16-alpine`, Redis `7-alpine` | Tags are major-pinned but not digest-pinned. |
| Proxy | — | `nginx:alpine` | Floating base tag. |
| CI actions | GitHub Actions | Checkout `v7`, setup-php `v2`, cache `v6`, setup-node `v7`, Docker metadata `v5`, build-push `v6`, login `v4`, SSH action `v1.2.5` | Tag references, not SHA-pinned; refresh/verify in Phase 6/10. |

The Phase 2 handoff is tied to older commit `1299645` and its inventory says Laravel 12 / Nuxt 3. The audit uses the actual code and lockfiles at `420fb6c`; the Phase 2 inventory must be refreshed before Phase 6 relies on it. Dependency license enumeration and PHP latest-stable registry checks remain unconfirmed.

### npm advisory result

`npm audit --json` in `frontend/` exited **1** and reported **20** vulnerabilities: 5 high, 13 moderate, 2 low, 0 critical. High-severity package names reported included `brace-expansion`, `editorconfig`, `js-cookie`, `minimatch`, and `picomatch`. No package was changed. Re-run after resolving the Composer lock issue and before/through Phase 6; evaluate advisory paths and safe updates against current registry data.

## Configuration, container, and deployment review

- `.env.example` / `.env.prod.example` expose configuration names and placeholders, including Shopify auth values and DB/Redis settings. `APP_KEY` is blank in examples; no actual secret was identified.
- Development Compose and CI use fixed example database credentials. Do not reuse them outside isolated test/development contexts.
- No `.dockerignore` is tracked for backend, frontend, or Nginx contexts. The backend production image copies its whole context; a local `.env` or other ignored file could be copied if present.
- Backend image has base/development/production stages but no test stage. Frontend has development/builder/production stages and uses `npm install` rather than `npm ci`; no test stage.
- Production Compose uses mutable `latest` application images and environment interpolation without an explicit fail-fast check for all required values. Redis password behavior must be verified before deployment.
- Nginx configuration has no TLS listener/security-header policy; it proxies HTTP to Nuxt and PHP-FPM.
- Backup/restore scripts use fixed filesystem paths and DB identifiers; encryption/off-host storage/restore verification are not evidenced. Restore drops/recreates the target database, so any real use is Gate 6.

## API, data, and code-quality observations

1. **Authentication/authorization:** routes have no auth middleware; all inspected FormRequest `authorize()` methods return `true`. Mutating endpoints can sync, queue work, update inventory, or delete products without caller identity/permissions.
2. **Shopify MCP:** code fetches OAuth token but supplies a literal masked placeholder in `Authorization`; no request success was tested.
3. **Database:** products have unique SKU and nullable unique Shopify ID, status/created/composite indexes, and soft deletes. Variants reference products with cascade on hard delete and unique `(product_id, sku)`. No explicit check constraints for nonnegative prices/inventory or status/currency values were found in migrations.
4. **Query/write behavior:** repository read queries eager-load variants. Repository writes loop through variants with individual upserts, without parent/child transaction or removal of missing variants. `getProducts()` ignores `page`; no full Shopify pagination is implemented.
5. **Cache/queue:** product list TTL is five minutes; sync invalidates its tag but inventory updates do not. Queue job has retry/backoff settings and `uniqueId()` but does not implement Laravel's `ShouldBeUnique`.
6. **Static API/test discrepancies:** tests expect response keys/envelopes not returned by current code. Examples: inventory test expects `data.inventory`, controller returns `sku`/`quantity`; queue test expects `queued_count`, controller returns `count`; bulk test expects snake-case result keys, result returns camelCase; list/integration tests expect `per_page`/`meta` shapes that differ from current repository/resource metadata; deletion of a missing SKU is expected to return 404 but repository deletion silently returns and controller returns success. These are source-level discrepancies only; no PHP test execution was available.
7. **Frontend quality:** Vitest unit tests cover components/store utilities/formatters/validators; store network actions have limited direct coverage in inspected test files. Playwright is declared but no E2E suite/config was found.
8. **Documentation freshness:** README badges/claims identify PHP 8.3+, Laravel 12, Nuxt 3, while current declarations are PHP `^8.4`, Laravel `^13.0`, Nuxt `^4.6.3`. README also acknowledges no authentication is required, which is not an acceptable production posture for write endpoints.

## CI/CD and Git hygiene

- CI triggers for push/PR to `main` or `develop`; the local origin's advertised HEAD branch was `master`. Verify the real GitHub default branch/protections in Phase 4; do not migrate it without Gate 4 approval.
- Backend CI runs only the Unit suite, not Feature/Integration; frontend lint has `continue-on-error: true`; no Docker-based test gate, PHPStan/Pint job, secret scan, SCA/image scan, or real duration report was evidenced.
- Build/deploy workflow triggers on `main`, `develop`, and `v*` tags and pushes images for these events; Phase 10 must restrict publishing to protected tags.
- Dependabot exists with Composer/npm/Docker/Actions entries, but schedules do not specify Monday 06:00 UTC, required PR limits/commit prefixes are not fully configured, and the config must be aligned with standing decision 14.
- Current user context states all 31 open PRs are Renovate/Dependabot PRs. No dependency-bot PR operation or independent PR-list query was performed; they remain untouched.
- Worktree started clean and detached at the supplied tip; the existing target row was `Phase 3=running`. This audit created branch `phase-3/audit-plan`; it did not alter remote branches, default branch, or PRs.
- Working-tree gitleaks scan only; full-history gitleaks audit remains Phase 4.

## Redacted secret scan

Command: `gitleaks dir --no-banner --redact --report-format json --report-path - <worktree>` → **exit 0**, **0 findings**. No secret values were printed or recorded. This is a working-tree scan only, not a Git-history scan. Static fixed development credentials in Compose/CI are configuration risks, not reported as discovered live credentials.

## Verification limits

- `php` and `composer` were not available on the host. Backend tests, Composer validation/outdated, PHPStan, and Pint were not executed.
- `npm audit` ran; no npm build, lint, or Vitest suite was run.
- Docker was available as a command but no image build, container run, or Docker test was performed.
- Therefore this audit reports static evidence and audit results only; it does not claim that the app builds, tests pass, or is production-ready.
