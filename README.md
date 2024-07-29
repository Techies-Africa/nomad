# Official Laravel Client for Nomad

<dl>
  <dt>Reference Docs</dt>
  <dd><a href="https://github.com/Techies-Africa/nomad">https://github.com/Techies-Africa/nomad</a></dd>
  <dt>License</dt>
  <dd>MIT</dd>
</dl>

## Overview

The Laravel Nomad Package streamlines the management of timezones within your application. It automatically adjusts timestamps to the user's local timezone when retrieving data, ensuring consistent and accurate datetime handling. This package simplifies timezone management across your application, providing a seamless experience for users in different timezones.

## Requirements

* [PHP 7.0 or higher](https://www.php.net/)

## Installation

You can install the package using **Composer** or by **Downloading the Release**.

### Composer

The preferred method of installation is via [Composer](https://getcomposer.org/). If you do not already have Composer installed, follow the [installation instructions](https://getcomposer.org/doc/00-intro.md).

To install the package, run the following command in your project root:

```sh
composer require techies-africa/nomad
```

After installing Nomad, publish its assets and migrations using the nomad:install Artisan command. After installing Nomad, you should also run the migrate command in order to create the column in the default table:

```sh
php artisan nomad:install

php artisan migrate
```

### Download the Release

If you prefer not to use composer, you can download the package in its entirety. The [Releases](https://github.com/Techies-Africa/nomad/releases) page lists all stable versions. Download any file for a package including its dependencies.
