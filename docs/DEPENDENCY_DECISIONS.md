# Dependency Decisions

## 2026-11-13 — Unblock PR #125 dependency installation

This is the narrowly scoped, user-authorized Phase 6 exception recorded in the orchestration approval log. It is not approval for the full Phase 6 upgrade or dependency-bot PR triage.

### Frontend

- **Change:** Pinia `^2.1.7` → `^3.0.4`; `@pinia/nuxt` remains `^0.11.3`.
- **Reason:** The existing `@pinia/nuxt@0.11.3` requires Pinia `^3.0.4`; the prior manifest and lock selected Pinia 2 and caused `npm ci` to fail with `ERESOLVE`.
- **Compatibility:** The existing Vue Router `^5.0.3` peer range is compatible with Pinia `^3.0.4`.
- **Verification:** `npm ci`, `npm run test:unit -- --run` (68 tests), and `npm run type-check` passed in Docker with Node 26. Node 20 also installed dependencies and passed unit tests but is unsupported by the current Nuxt version.
- **CI alignment:** `.github/workflows/ci.yml` now selects Node 26 for frontend jobs. Existing frontend tests were corrected to satisfy strict TypeScript checks without changing production code.

### Backend

- **Change:** Laravel Tinker `^2.10` → `^3.1`; refreshed `backend/composer.lock` with `composer update laravel/framework laravel/tinker --with-all-dependencies`.
- **Reason:** The repository already requires Laravel `^13.0`, but its lockfile selected Laravel 12 and Tinker 2. The Tinker constraint needed to move to v3 for Laravel 13 resolution.
- **Resolved versions:** Laravel Framework `v13.42.0`; Laravel Tinker `v3.1.0` (latest stable versions verified from Packagist at the time of the change).
- **Verification:** Composer update completed with no reported advisory; `composer install --no-interaction --prefer-dist` and `composer validate --no-check-publish` passed in Docker with PHP 8.4.
- **CI alignment:** Backend CI now enables PCOV. The 82 deprecated `/** @test */` docblock annotations were converted to PHPUnit's native `Test` attributes in test files.
- **Verification:** With PCOV 1.0.12 in a PHP 8.4 Docker container and `.env.example` mounted read-only as `.env`, `./vendor/bin/phpunit --testsuite Unit` passed: 82 tests, 177 assertions, exit 0.

### Scope and outcome

- No new dependency was introduced.
- Application production code was not changed. CI workflow and test files were updated under the separately approved narrow Phase 10/11 blocker-remediation scope.
- Frontend tests, type-checking, backend tests, and `git diff --check` passed in Docker.
- `npm ci` reported 20 dependency audit findings (5 high); dependency vulnerability triage is outside this blocker-only scope.
- The frontend lint command still fails because ESLint configuration is absent; the existing CI lint step uses `continue-on-error: true`.
- PR #125 remains unmerged. Its GitHub checks must be rerun after these changes are committed and pushed; Phase 4 remains blocked until the PR checks pass.
- The backend development Dockerfile build also remains blocked by its GD extension installation step (`cp: can't stat 'modules/*'`); it was not changed.
- No skill proposal is made because this exception did not reach a green phase result (R9).
