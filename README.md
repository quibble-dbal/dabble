# Dabble
PHP5 database abstraction layer. Writing SQL by hand is fine craft, but often
it's more convenient to juggle arrays, e.g. to dynamically add conditions. Using
Dabble this is made easy, while still allowing you to write literal SQL where
you need.

Dabble is an _extension_ of PHP's native `PDO` class, so should work with any
existing code seamlessly.

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

## Querying
Dabble supports four 'main' types of queries: [`select`](usage/select.md),
[`insert`](usage/insert.md), [`update`](usage/update.md) and
[`delete`](usage/delete.md). These have corresponding method names on the
`Dabble\Adapter` object. They all follow a similar syntax where argument one is
the table name, and further arguments are arrays of key/value pairs.

There are also helper classes that these are actually a front for.

Of course, regular PDO methods are also available for fine-grained tuning.

## FAQ

- #### Can I use Dabble in combination with [insert ORM library]? ####

    Sure - as long as your library uses `PDO`. Dabble is simply an _extension_
    to `PDO`, so you should be good to go.

- #### What's the name "Dabble" at? ####

    It's a sort-of portmanteau of "database abstraction layer". And it sounded
    cute :)

- #### How well-tested is the codebase? ####

    Some of code dates back 10 years. Dabble is based on the database
    abstraction layer shipped in the [Monolyth framework](http://monolyth.monomelodies.nl)
    up to version 5. This in turn is based on code lifted from [CU2](http://www.cu2.nl)
    which Marijn's company owned from 2009 to 2014.

    So yes, it's pretty well tested in the real world :)

