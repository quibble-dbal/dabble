# Dabble
PHP5 database abstraction layer. Writing SQL by hand is fine craft, but often
it's more convenient to juggle arrays, e.g. to dynamically add conditions. Using
Dabble this is made easy, while still allowing you to write literal SQL where
you need.

## Installation

### Using Composer (recommended)
```bash
composer install --save monomelodies/dabble
```

### Manual
Download or clone the library. Register the `/path/to/dabble/src` for the
namespace prefix `Dabble` in your autoloader.

## Setting up a connection
Instantiate a Dabble database object using your credentials:

```php
<?php
    
use Dabble\Adapter\Mysql;

$db = new Mysql($dsn, $user, $pass, $options);

```

The database type (e.g. `mysql:` in the above example) is added to the `$dsn`
string by Dabble.

The actual connection is opened in a just-in-time manner; hence, feel free to
define as many Dabble adapters as you like (e.g. large sites connecting to
various databases depending on the route). Connections aren't opened until the
adapter is actually used. This allows you to define all your adapters in a
central place.

Dabble supports four 'main' types of queries: [`select`](usage/select.md),
[`insert`](usage/insert.md), [`update`](usage/update.md) and
[`delete`](usage/delete.md). These have corresponding method names on the
`Dabble\Adapter` object. They all follow a similar syntax where argument one is
the table name, and further arguments are arrays of key/value pairs.

Of course, regular PDO methods are also available for fine-grained tuning.

