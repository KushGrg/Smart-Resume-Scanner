# Mary UI Starter Kit for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/chandanshakya/mary-ui-starter-kit)](https://packagist.org/packages/ChandanShakya/mary-ui-starter-kit)
[![Total Downloads](https://img.shields.io/packagist/dt/ChandanShakya/mary-ui-starter-kit)](https://packagist.org/packages/chandanshakya/mary-ui-starter-kit/stats)

A production-ready Laravel starter kit combining the power of [Laravel Volt](https://livewire.laravel.com/docs/volt) with [Mary UI](https://github.com/robsontenorio/mary). This package provides a complete authentication system and role-based authorization powered by Spatie's Laravel Permission package.

## Features

- **Laravel 12+** - Built with the latest Laravel version
- **Laravel Volt** - Improved Livewire component authoring experience
- **Mary UI** - Beautiful UI components built on top of Tailwind CSS and DaisyUI
- **Authentication System** - Complete authentication flow including:
  - User registration
  - Login
  - Email verification
  - Password recovery
- **Role-based Authorization** - User roles and permissions management using Spatie's Laravel Permission
- **Admin Panel** - Ready-to-use admin interface for managing users, roles, and permissions
- **Modern Frontend** - Tailwind CSS 4 with DaisyUI components
- **Vite** - Fast frontend tooling with hot module replacement

## Installation

You can create a new Mary UI Starter Kit project via laravel installer:

```bash
laravel new my-app --using=chandanshakya/mary-ui-starter-kit
```

or using composer

```bash
composer create-project chandanshakya/mary-ui-starter-kit
```

Or clone the repository manually:

```bash
# Clone the repository
git clone https://github.com/ChandanShakya/mary-ui-starter-kit.git
cd mary-ui-starter-kit

# Install PHP dependencies
composer install

# Copy environment file and generate app key
cp .env.example .env
php artisan key:generate

# Set up the database
php artisan migrate --seed

# Install frontend dependencies
npm install
# or if you use Yarn
yarn

# Run the development server
php artisan serve
# In a separate terminal
npm run dev
# or
yarn dev
```

## Development

For a streamlined development experience, you can use the provided `dev` command:

```bash
composer dev
```

This will concurrently run:

- Laravel development server
- Queue worker
- Laravel Pail for log monitoring
- Vite development server with hot module replacement

## Default User Credentials

After running the migrations and seeders, you can login with the following credentials:

| Role  | Email             | Password |
|-------|-------------------|----------|
| Admin | admin@example.com | password |
| User  | user@example.com  | password |

## Requirements

- PHP 8.2+
- Composer
- Node.js & NPM

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Security Vulnerabilities

If you discover a security vulnerability within this starter kit, please send an e-mail to Chandan Shakya via [email@chandanshakya.com.np](mailto:email@chandanshakya.com.np). All security vulnerabilities will be promptly addressed.

## License

The Mary UI Starter Kit is open-source software licensed under the [MIT license](https://raw.githubusercontent.com/ChandanShakya/mary-ui-starter-kit/refs/heads/main/LICENSE).
