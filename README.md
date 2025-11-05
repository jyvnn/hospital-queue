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

Hospital Queue is a small Laravel + Vite application used to manage patient check-ins, doctors, and appointments. This project now includes a lightweight chatbot (DocChat) that can answer simple queries about doctors and the current queue.

This README focuses on how to run the app locally, where to find chatbot functionality, and a few developer notes about recent changes.

Contents
- Features
- Quick start (development)
- Chatbot (DocChat)
- Tests
- Deploy / CI guidance
- Contributing & notes

Features
- Patient queue and dashboard (check-in, in-progress, completed)
- Doctor and appointment management
- Simple chatbot (DocChat) accessible from the site — supports basic commands (see below)

Quick start (development)
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

Chatbot (DocChat)
-----------------
DocChat is a lightweight chat UI implemented with a blade view and a small controller handler. It is not a full BotMan conversation stack yet, but it's functional and designed to be extended.

Where to find it
-- Chat UI: `resources/views/botman/chat.blade.php`
-- Controller: `app/Http/Controllers/BotManController.php`
-- Routes: registered in `routes/admin.php` as `/chat` (UI) and `/botman` (AJAX endpoint)

How to use
- Open the chat UI at `/chat` (or click the floating chat button on the site when authenticated).
- Supported commands (short list):
	- `hi` — Greet the bot
	- `help` — Show the concise list of supported commands
	- `list doctors` / `doctors` — Show available doctors (name, specialty, availability)
	- `queue` / `waiting` — Show queue summary and a short list of next patients

Implementation notes
- The chat UI sends AJAX POST requests to `/botman` with JSON { message } and expects JSON replies { reply }.
- The controller currently implements a small set of canned/intention-based replies and queries the database for doctors, appointments, and patients. It uses `App\\Models\\Doctor`, `Appointment`, and `Patient`.
- `Appointment` now contains `doctor()` and `patient()` Eloquent relations so responses may include related names without extra queries (eager-loading is used where appropriate).
- The floating chat button is shown only to authenticated users (wrapped with Blade `@auth`). If you want the chat public, edit `resources/views/layouts/app.blade.php`.

Tests
-----
Run the PHPUnit suite:

```powershell
php artisan test
```

If you add controller behaviour or message parsing, consider adding Feature tests for `BotManController` to exercise the different message paths.

Deploy / CI guidance
--------------------
- Commit application code only (do NOT commit `vendor/` or `node_modules/`).
- Commit `composer.lock` and `package-lock.json` for reproducible installs.
- Add a `.env.example` and do not commit `.env` with secrets.
- Prefer building assets in CI and deploying built assets to production. If your deploy target cannot build assets, commit `public/build/` intentionally and document it.

Recommended CI steps
- checkout
- composer install --no-interaction --prefer-dist --optimize-autoloader
- npm ci && npm run build
- php artisan migrate --force
- php artisan test

Developer notes / suggestions
----------------------------
- Consider adding inverse relations (`appointments()`) to `Doctor` and `Patient` if you need to traverse from doctors/patients to their appointments.
- Add `$casts = ['scheduled_at' => 'datetime']` to `Appointment` for automatic Carbon casting.
- Keep the chat command list DRY by extracting the commands into a config array if you expect to reuse them in multiple places.

Contributing
------------
If you'd like me to add a GitHub Actions workflow, a `.gitignore`, or a `.env.example`, tell me which and I will create them.

License
-------
This project follows the licensing of its dependencies. The Laravel framework is MIT-licensed.

