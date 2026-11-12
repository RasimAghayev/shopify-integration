# Technical Debt — Shopify Integration

**Baseline:** `420fb6c1975e68b6d5353b1b6af5cfa69622d636`. Priorities are evidence-based from source/config review; runtime test status remains unverified where noted.

## P0 — Critical

None confirmed by the Phase 3 source audit.

## P1 — High

| ID | Debt | Evidence / risk | Target phase | Acceptance evidence |
|---|---|---|---|---|
| TD-01 | No API authentication/authorization on read/write routes | `routes/api.php` has no auth middleware; all inspected FormRequests allow authorization. Public exposure allows unauthenticated destructive/integration actions. | 9 | Authenticated roles/policies for sensitive operations; anonymous access denied; tests for every route; rate limits and authorization results verified in Docker. |
| TD-02 | Composer manifest and lock disagree | `backend/composer.json` requires Laravel `^13.0`, while lock resolves `v12.69.0`. | 6 (unblock baseline in 5) | Composer validation/install succeeds from clean lock in Docker; locked framework satisfies declared constraint; dependency map refreshed. |
| TD-03 | Frontend dependency advisories | `npm audit --json` exit 1; 20 findings including 5 high. | 6, then 9/10 | Findings reviewed against current registry/advisories; safe fixes applied or individual blockers documented; audit result recorded. |
| TD-04 | MCP request authentication is not wired | `mcpRequest()` obtains a token but sends a literal masked placeholder in `Authorization`. | 9 | Authenticated integration test with request assertion; no real token in logs; success/failure behavior established. |
| TD-05 | Container build contexts lack `.dockerignore` | Backend production build uses `COPY . .`; ignored local environment/credential files could enter build context. | 5, verify again in 9/10 | Build-context/image scan proves `.env`, keys, `.git`, vendor/node_modules, and other irrelevant files excluded; no credential in image layers. |

## P2 — Medium

| ID | Debt | Evidence / risk | Target phase | Acceptance evidence |
|---|---|---|---|---|
| TD-06 | CI can miss regressions and is misaligned with branch | CI filters `main`/`develop`, backend runs only Unit, frontend lint is non-blocking; local origin HEAD observed as `master`. | 4, 10 | Correct protected branch triggers after Gate 4 decision; all required checks block; feature/integration and frontend suites pass in Docker. |
| TD-07 | CI build publishing is not tag-only | `build-deploy.yml` pushes images on branch events as well as tags. | 10 | Publishing job only runs on approved protected tags; PRs/forks receive no secrets; permissions/timeouts and runner durations documented. |
| TD-08 | Response-contract tests disagree with implementation | Inventory, bulk, list and delete feature/integration assertions expect different envelopes/keys/status behavior than current handlers/resources. | 11 | Contract chosen and documented; tests assert actual intended behavior and pass; no weakened assertions. |
| TD-09 | Product/variant persistence lacks atomic reconciliation | `save()` upserts parent then each variant without transaction and does not remove variants absent from the Shopify payload. | 8/9/11 | Failure-injection/reconciliation tests; transaction semantics established; stale variant behavior defined and verified. |
| TD-10 | Inventory updates leave cached lists stale | Inventory use case saves without invalidating the product-list cache tag; TTL is 300 seconds. | 9/11 | Cache invalidation test for all product/inventory mutations; read-after-write behavior verified. |
| TD-11 | Shopify product listing is not paginated correctly | `getProducts()` ignores `page` and returns one response batch. | 9/11 | Shopify pagination/cursor behavior and rate-limit handling tested; no item loss across pages. |
| TD-12 | Queue uniqueness is only nominal | Job defines `uniqueId()` but does not implement `ShouldBeUnique`. | 9/11 | Duplicate dispatch policy explicit; tests verify expected dedupe or deliberate non-dedupe behavior. |
| TD-13 | Input bounds and external-call limits need work | List `page`/`per_page` lack explicit upper/lower bounds; immediate bulk loop permits up to 100 synchronous external requests. | 9/11 | Validation, rate limits, payload limits, timeouts and error behavior tested; bounded resource use documented. |
| TD-14 | Test DB config differs between local and CI | PHPUnit selects SQLite, but Laravel database config has only PostgreSQL connections; CI sets Postgres env for its unit job. | 5/11 | Clean documented Docker test path uses a supported DB configuration; complete suites execute in CI. |
| TD-15 | Backup/recovery is not operationally proven | Fixed paths/identifiers, no evidence of encryption/off-site copy/restore tests; restore drops and recreates DB. | 9/15 | Documented protected retention, tested restore into a non-production disposable DB, alerts/checksums; production actions require Gate 6. |
| TD-16 | Development/production runtime config needs hardening | Fixed development credentials; production mutable `latest` tags and incomplete required-env validation; Redis auth defaults need validation. | 5/9 | Placeholder-only examples, fail-fast config validation, immutable deployment references, and container security verification. |

## P3 — Low / maintainability

| ID | Debt | Evidence / risk | Target phase |
|---|---|---|---|
| TD-17 | README stack badges and framework descriptions are stale | README describes PHP 8.3+/Laravel 12/Nuxt 3; current declarations are PHP `^8.4`, Laravel `^13.0`, Nuxt `^4.6.3`. | 15 |
| TD-18 | No checked-in OpenAPI contract | API is documented in prose/routes but no machine-readable contract exists. | 9/15 |
| TD-19 | E2E command is present without observed Playwright tests/config | `test:e2e` script/dependency exist but no tracked Playwright spec/config was found. | 7/11/15 |
| TD-20 | Coverage is configured but not evidenced or thresholded | PHPUnit/Vitest report settings exist; no generated report or verified threshold. | 5/11 |
| TD-21 | Dependency license/legal review is incomplete | Repository MIT license is present; transitive package license and Shopify platform/API terms were not reviewed. | 2 refresh / 6 / 15 |

## Burndown targets

1. **Before Phase 5 baseline:** resolve TD-02 or record an explicit reproducible blocker; add build-context exclusions; define one deterministic Docker verification command and capture the red/green baseline.
2. **Before Phase 6 closes:** refresh dependency inventory, triage all current npm/Composer advisories, reconcile lockfiles, update dependencies one group at a time, and compare to Phase 5 baseline.
3. **Before Phase 9 closes:** no unauthenticated write surface; external calls, cache, queue, data boundaries and secrets have verified controls.
4. **Before Phase 10 closes:** CI tests Docker artifacts, quality/security checks are blocking, image publish is tag-only, real run times are recorded.
5. **Before Phase 11 closes:** API contract and tests agree, all suites run in Docker/CI, coverage is measured honestly, and pre-existing failures are either fixed in their proper phase or documented.
6. **Before Phase 15 closes:** README/deployment/operations/security docs and manifests describe the verified implementation; license and operational limitations remain explicit.
