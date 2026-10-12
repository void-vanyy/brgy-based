<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Santa Rosa City e-Services

A barangay e-services portal covering all 18 barangays of Santa Rosa City,
Laguna. Built to run on **localhost only** — Laravel 12, MariaDB via XAMPP,
Apache, and a hand-written CSS design system (no Node build step).

Sign in with any seeded account, password `password`. The login page lists
the three most useful: the city admin (all 18 barangays), a barangay official
(Tagapo), and a resident.

### Local setup

```powershell
# 1. XAMPP: start Apache + MySQL, then create an empty `brgy_db` in phpMyAdmin
# 2. From the project root:
C:\xampp\php\php.exe artisan migrate:fresh --seed
C:\xampp\php\php.exe artisan config:cache      # see the note below
C:\xampp\php\php.exe artisan view:clear
```

The site is served from `http://localhost/brgy` (an `htdocs` junction pointing
at this project's `public/` folder).

### Why `config:cache` is required here

Apache runs PHP as **mod_php under the threaded `mpm_winnt` MPM**, so all
requests share one process. Laravel loads `.env` with `putenv()`, which is
process-global and not thread-safe in a threaded SAPI. Under concurrent
requests the environment races, and a request can boot with no `.env` at all
— it then falls back to `APP_ENV=production`, no `APP_KEY`, and SQLite.

Measured on this box: without the config cache, 12 concurrent requests
produce a mix of 503s and 500s. With it, 120/120 requests succeed and no
failures are logged.

**The trade-off:** `.env` is no longer read per request, so after editing
`.env` you must re-run `php artisan config:cache`. If the site suddenly looks
like it is ignoring your `.env`, that command is the fix.

`bootstrap/cache/` is gitignored, so the cache (which contains `APP_KEY`) is
never committed — a fresh clone must run `config:cache` once.

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

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

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
