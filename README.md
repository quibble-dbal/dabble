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

Dabble supports four 'main' types of queries: `select`, `insert`, `update` and
`delete`. These have corresponding method names on the `Dabble\Adapter` object.
They all follow a similar syntax where argument one is the table name, and
further arguments are arrays or key/value pairs.

## Select queries
```php
<?php

$result = $db->select('tablename', $fields, $where, $options);

```

Internally this is converted to an SQL query used in a prepared statement:

```sql
SELECT $fields FROM 'tablename' WHERE $where $options
```

The tablename is simply a string that is injected verbatim; the other arguments
can be either strings (for 'raw' querying) or arrays or key/value pairs. For
full documentation on all the options here, see the API docs for
`Adapter::select`.

If the query does not yield any results, a `Dabble\Query\SelectException` is
thrown.

### Handling results
Multi-row Dabble results are returned as a lambda implementing a `Generator`.

```php
<?php

foreach ($result() as $row) {
    echo $row['field'];
}

```

Returned rows are fetched using `PDO::FETCH_ASSOC`.

Instead of `select`, you can also query using `fetchAll` with the same
arguments. This returns an array with all results just as
`PDOStatement::fetchAll` would.

### Single-row queries
If you know beforehand you will only need a single row from a result set, use
`Dabble\Adapter::fetch` instead:

```php
<?php

$row = $db->fetch('tablename', $fields, $where, $options);

```

This will return a single array of key/value pairs, and automatically injects
`LIMIT 1` into your query.

### Single-column queries
If you know beforehand you will only need a single column from a result set, use
`Dabble\Adapter::column` instead:

```php
<?php

$col = $db->column('tablename', $fields, $where, $options);

```

Like `row`, this adds `LIMIT 1` to the query, and also drops all fields except
the first prior to execution.

## Insert queries
```php
<?php

$affectedRows = $db->insert('tablename', $columnValuePairs);

```

`$columnValuePairs` can be either a string of literal SQL, or a hash where the
keys are the column names to insert into, and the values are the values to
associate with. The values are properly parameterized, except when they are an
instance of `Dabble\Query\Raw` (in which case they will be inserted verbatim and
escaping is up to the programmer).

If no rows are affected (i.e., the insert failed) a
`Dabble\Query\InsertException` is thrown.

## Update queries
Identical to insert queries, except a third parameter contains a 'where' clause.
The third parameter is required to prevent accidents.

```php
<?php

$affectedRows = $db->update('tablename', $columnValuePairs, $where);

```

To force an update without a where-clause, simply do something like the
following:

```php
<?php

$db->update('tablename', $columnValuePairs, [1 => 1]); // WHERE 1 = '1'

```

If no rows are affected (i.e., the update failed or no rows were matched or
changed) a `Dabble\Query\UpdateException` is thrown.

## Delete queries
Identical to update queries, but without the second parameter:

```php
<?php

$affectedRows = $db->delete('tablename', $where);

```

If no rows are affected (i.e., the delete failed or no rows were matched) a
`Dabble\Query\DeleteException` is thrown.

