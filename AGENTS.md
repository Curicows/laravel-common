# AGENTS.md - Laravel Common

## Project Context

This is `curicows/laravel-common`, a shared Laravel package used by Curicows applications. Treat `composer.json` as the source of truth for versions: PHP 8.5, Laravel 13, PHPUnit 12, Orchestra Testbench 11, and Pint.

The Curicows backend consumes this package through a Composer path repository with symlink enabled. Package changes can affect linked applications immediately.

## Repository Shape

- Package source lives in `src/`.
- Tests live in `tests/`.
- The service provider is `Curicows\\LaravelCommon\\LaravelCommonServiceProvider`.
- Custom generators live under `src/Console/Commands/Generator`.

## Conventions

- Keep this package generic and reusable; do not put app-specific business rules here.
- Prefer existing base classes and helpers before adding new abstractions.
- Important shared types include `Dto`, `SearchDto`, `Repository`, `Service`, `Controller`, `BaseServiceProvider`, and `Subscriber`.
- Preserve publishable config, stubs, and migrations behavior.
- Be careful with breaking changes: downstream apps may receive them immediately through path symlinks.
- Do not change dependency versions unless the task explicitly requires it.

## Common Commands

- Install dependencies: `composer install`
- Run tests: `composer test`
- Lint check: `composer lint`
- Format: `composer format`
- TeamCity test report: `composer test:teamcity`

## Before Finishing Changes

- Run `composer test` for behavior changes.
- Run `composer lint` or `composer format` for PHP style changes.
- Consider testing at least one consuming app when changing public APIs, base classes, migrations, or service provider behavior.
- Run `git diff --check` before handing off.
