<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Hospital Queue — Project README

This repository contains the Hospital Queue application (Laravel + Vite). The sections below explain how to get it running locally and how to prepare the repository for GitHub/CI-based deploys.

### Getting started (development)

1. Copy the example env and install PHP dependencies:

```powershell
cp .env.example .env
composer install
php artisan key:generate
```

2. Install JS dependencies and run the dev server (Vite) or build assets:

```powershell
npm ci
npm run dev   # development (hot-reload)
# or for a production-ready build:
npm run build
```

3. Prepare the database (adjust `.env` DB settings first):

```powershell
php artisan migrate --seed
php artisan db:seed
```

4. Run the app locally:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
# open http://127.0.0.1:8000
```

### Tests

Run the PHPUnit suite:

```powershell
php artisan test
```

### GitHub / Repository guidance

- Commit application code only (do NOT commit `vendor/` or `node_modules/`).
- Commit `composer.lock` and `package-lock.json` for reproducible installs.
- Add a `.env.example` (do not commit `.env` with secrets).
- Prefer building assets in CI rather than committing `public/build/`. If your deploy target cannot build assets, commit `public/build/` intentionally and document it in the README.

### CI (recommended)

Add a CI job that:
- checks out the code
- installs PHP dependencies (Composer)
- installs Node dependencies and builds assets
- runs PHPUnit

I can add a GitHub Actions `ci.yml` if you want.

### Quick checklist before pushing to GitHub

- [ ] Add `.gitignore` (ignore `vendor/` and `node_modules/`).
- [ ] Add `.env.example` with placeholders.
- [ ] Confirm `composer.lock` and `package-lock.json` are committed.
- [ ] Add a CI workflow (`.github/workflows/ci.yml`) to build assets and run tests.

---

If you want, I can create the `.gitignore` and `.env.example` files now and/or add a GitHub Actions workflow. Which would you like me to add next?
