# Laravel Tail DB

[![tests](https://github.com/muhdfaiz/laravel-tail-db/workflows/testing/badge.svg)](https://github.com/muhdfaiz/laravel-tail-db/actions)
[![Version](https://poser.pugx.org/muhdfaiz/laravel-tail-db/version)](//packagist.org/packages/muhdfaiz/laravel-tail-db)
[![Total Downloads](https://poser.pugx.org/muhdfaiz/laravel-tail-db/downloads)](//packagist.org/packages/muhdfaiz/laravel-tail-db)
[![License](https://poser.pugx.org/muhdfaiz/laravel-tail-db/license)](//packagist.org/packages/muhdfaiz/laravel-tail-db)

A package to monitor, troubleshoot and optimize SQL query using console.

<img src="https://muhdfaiz.github.io/laravel-tail-db/assets/images/postgresql.png" width="600" alt="">

## Features

- **Monitor SQL query.** Display Realtime SQL query executed from application in the console.

- **Optimize SQL query.** Automatically run `explain` command and output to console.

- **Detect slow SQL query.** Highlight slow SQL query according to your config.

## Requirements

- PHP: ^7.0
- Laravel: ~5.5,~5.6,~5.7,~5.8,~6.0,~7.0,~8.0


## Installation

```
composer require muhdfaiz/laravel-tail-db
```

## Usage

```
php artisan tail:db
```

## Documentation

For detail of installation instructions, in-depth usage, please take a look at the [official documentation](https://muhdfaiz.github.io/laravel-tail-db/).

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.