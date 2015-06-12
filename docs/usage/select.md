# Selecting
Various methods exist to aid in selecting data, besides `PDO::query`.

## Basic select
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

## Handling results
The results from `Dabble::select` are returned as a lambda implementing a
`Generator`.

```php
<?php

foreach ($result() as $row) {
    echo $row['field'];
}

```

Returned rows are fetched using `PDO::FETCH_ASSOC`.

## Fetch everything
Instead of `select`, you can also query using `fetchAll` with the same
arguments. This returns an array with all results just as
`PDOStatement::fetchAll` would.

## Single-row queries
If you know beforehand you will only need a single row from a result set, use
`Dabble\Adapter::fetch` instead:

```php
<?php

$row = $db->fetch('tablename', $fields, $where, $options);

```

This will return a single array of key/value pairs, and automatically injects
`LIMIT 1` into your query.

## Single-column queries
If you know beforehand you will only need a single column from a result set, use
`Dabble\Adapter::column` instead:

```php
<?php

$col = $db->column('tablename', $fields, $where, $options);

```

Like `row`, this adds `LIMIT 1` to the query, and also drops all fields except
the first prior to execution.

Using the array syntax makes it easy to dynamically edit your query parameters
without having to worry about commas and stuff. For instance:

```php
<?php

$fields = ['foo', 'bar'];
if ($someCondition) {
    $fields[] = 'baz';
}
$db->select('table', $fields);

```

## Nothing found?
All select queries from Dabble throw a `Dabble\Query\SelectException` if no rows
matched your SQL query. This avoids clunky `if (false !== ($results = fn()))`
type constructs, and allows you to structure your code logically using
`try/catch` statements.

> Note that `PDO::query` and `PDOStatement::fetch` are untouched and will not
> throw these exceptions.

