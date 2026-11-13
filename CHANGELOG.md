# Changelog

## Unreleased

### Dependency alignment

- Updated Pinia to `^3.0.4` to satisfy the existing `@pinia/nuxt@0.11.3` peer requirement.
- Updated Laravel Tinker to `^3.1` and refreshed the Composer lockfile with Laravel 13-compatible dependencies.

### CI and tests

- Updated frontend CI to Node 26, supported by the current Nuxt dependency set.
- Enabled PCOV in backend CI and migrated PHPUnit test annotations to native attributes.
- Fixed frontend test typing so CI type-checking covers the existing unit tests.
- Docker validation passed: frontend 68 unit tests and type-check; backend 82 unit tests.
