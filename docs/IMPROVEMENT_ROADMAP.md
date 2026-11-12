# Improvement Roadmap — Shopify Integration

**Plan status:** Gate 10 approved by the user on 2026-11-12. This document authorizes no Phase 4 execution in this task. Gate 4 is approved for the future master→main migration, which remains unexecuted.

## Phase 4 — Git hygiene

**Tasks**
- Back up the canonical repository mirror before destructive Git work; run gitleaks on full history, review large/binary files and commit integrity, and report only redacted secret location/SHA.
- Review branch inventory and non-bot PRs. Per current user-provided context, the 31 open PRs are all dependency bots; do not merge/close them in this task or Phase 4/6 per policy.
- Check `.gitignore`, `.gitattributes`, and secret-scan hooks; verify Dependabot coverage, schedule, PR limits and prefixes against CLAUDE.md decision 14.
- Reconcile workflow branch filters with the verified default branch.

**Gate:** local origin HEAD advertises `master`; Gate 4 migration is authorized, but do not execute it until Phase 4. This task does not rename or change repository settings.
**Acceptance:** history and branch audit documented; no non-bot PR left unresolved; mirror recoverable; approved default-branch migration performed and verified during Phase 4.

## Phase 5 — Container baseline

**Tasks**
- Add appropriate `.dockerignore` files; exclude local env/keys, `.git`, dependencies, caches, coverage, and build outputs.
- Add a Docker `test` stage and a single `make verify` path that installs from lockfiles and runs backend/frontend tests plus applicable checks.
- Replace nondeterministic `npm install` build steps with lockfile-enforcing install; validate Composer clean install after lock alignment.
- Exercise dev/prod Compose health/readiness and required configuration; create/refresh `.env.example` placeholders only.
- Record build/test output, API/CLI snapshots, image sizes/scans, current known failures, and coverage status in `docs/BASELINE.md`.

**Acceptance:** documented baseline from containers; image context/layers contain no secrets; no `make up` before verify passes.

## Phase 6 — Latest dependencies and runtimes

**Tasks**
- Refresh `projects/info/deps/shopify-integration.md` and the aggregate dependency map from the actual Phase 5 baseline and live registries; Phase 2 snapshot is stale.
- Resolve Laravel `^13.0` versus locked Laravel `v12.69.0`; run Composer validation/outdated/audit from the Docker environment.
- Upgrade runtime/base image, framework, direct/transitive libraries, test/static-analysis tools, and CI actions in topological order, one logical group at a time. Review all npm audit advisories and package license data.
- Handle dependency-bot PRs only in this phase according to latest stable + verification; close only superseded PRs per program rules.
- Update migration notes/CHANGELOG for breaking dependency migrations; do not change this repo's public API without Gate 5.

**Acceptance:** every item is latest stable or has a documented highest-compatible blocker; lockfiles consistent; `make verify` preserves Phase 5 baseline.

## Phase 7 — Dead code and restructure

**Tasks**
- Cross-check Composer/npm unused-dependency and dead-export tools with test coverage, Laravel registration/reflection, Nuxt auto-imports and dynamic dispatch.
- Inspect Playwright dependency/script before any removal; determine whether E2E coverage is intended.
- Delete only proven dead code/dependencies in separate commits; align layout with upgraded framework conventions where a real mismatch exists.

**Acceptance:** each removal has tool/test evidence and its own commit; behavior preserved; `make verify` green. No deletion by guess.

## Phase 8 — Architecture

**Tasks**
- Reassess existing boundaries and domain complexity after upgrades.
- Consider targeted fixes for parent/variant transaction/reconciliation and cache invalidation where tests demonstrate a concrete failure.
- Apply KISS/YAGNI first; use SOLID/Clean/Hexagonal/DDD/EDA/modular-monolith/GoF only where a current problem justifies it.
- Add ADRs only for applied architecture decisions; record one-line rationale when a principle is deliberately not applied.

**Acceptance:** no pattern-driven redesign; small behavior-preserving changes; ADRs explain the why; `make verify` green.

## Phase 9 — Security, performance, and operability

**Tasks**
- Design and implement authentication/authorization for all routes; rate-limit sync/bulk and constrain pagination and payloads.
- Establish explicit CORS policy, safe error responses, robust input validation, and API contract tests.
- Fix Shopify MCP token use; set external-call timeout/retry/rate-limit behavior; sanitize logs and prevent credential/catalog data disclosure.
- Audit cache invalidation, queue duplication/retry semantics, transaction boundaries, concurrency, pagination and SQL/query plans.
- Add `/health/live` and `/health/ready`; document environment-only configuration and standalone behavior/stub or explicit clear error.
- Secure/validate backup and restore, retention and restore checks; do not touch production without Gate 6.
- Add/refresh `openapi.yaml` if the maintained API contract warrants it; adopt only tooling allowed by dependency-analysis decisions.

**Acceptance:** auth/input/security/error/performance checks documented and tested in Docker; health checks work; no production mutation without Gate 6.

## Phase 10 — CI/CD

**Tasks**
- Run formatting, lint, type-check, PHP static analysis and secret scan in parallel before Docker tests/build.
- Make lint/type/test and security gates blocking; include complete backend suite and frontend suite; build/test using the Phase 5 Docker target.
- Add dependency/image scanning, SHA-pin actions, least-privilege permissions, job timeouts, and fork-safe secret behavior.
- Align push/PR triggers with the Gate 4-approved branch policy; publish images only on protected tags, not ordinary branch pushes.
- Run real CI, record each job duration/slowest jobs and compare with the runner-time budget.

**Acceptance:** real CI run green within budget or overrun justified; no secret exposure to fork PRs; publish conditions verified.

## Phase 11 — Tests

**Tasks**
- Establish/refresh `docs/TESTING_STRATEGY.md` and `docs/TEST_REPORT.md`, using `TC_<MODULE>_<SCENARIO>_<NUMBER>` IDs and PASS/FAIL/BLOCKED/NOT RUN/SKIPPED/N/A statuses.
- Align inconsistent Feature/Integration response assertions with the approved API contract; add auth, authorization, validation, 404, error, pagination, cache, transaction, variant reconciliation, queue and Shopify adapter tests.
- Add frontend store/API, component and error cases; add Playwright E2E only if the product need and runtime justify it.
- Run all tests in Docker, compare against `docs/BASELINE.md`, report real coverage and limitations. Do not hide or weaken failures.

**Acceptance:** complete Docker and CI test suites green; coverage honest; production bug findings handed to orchestrator rather than fixed by the test agent.

## Phases 12–15 — Later-stage planning only

- **12 Merge small repos:** no candidate is established by this repo-level audit; ecosystem owner must present grouping for Gate 3 before any merge, preserve history, and rerun required checks.
- **13 Shared library:** no extraction decision from this audit. Only consider after ecosystem duplication analysis demonstrates the program rule (same domain concept or duplication in at least three places); Gate 7 required.
- **14 Rename:** no rename is approved or planned here; any repo rename requires Gate 2 and ecosystem reference updates.
- **15 Documentation/manifest:** update README, deployment, operations, security, changelog, dependency decisions and `repo-manifest.yaml` after actual Phase 4–11 changes; generate diagrams from verified implementation. The Phase 3 docs are initial audit artifacts, not completion of Phase 15.

## Phase applicability and standing decisions

Phases 4–11 are applicable; **none is n/a**. Gate 10 plan approval and Gate 4 migration authorization were granted; Phase 4 remains pending execution. Standing decisions 1–14 and their target phases are mapped in `projects/info/analysis/shopify-integration.md`.
