# SQL "options"
When we say "options", we're referring to "that part of the query after the
`WHERE`-clause, i.e. `LIMIT`, `OFFSET` etc.

Like with `WHERE`, Dabble support two methods of specifying these:

- Via a hash as the last argument to one of the methods on `Dabble\Adapter`;
- By instantiating a `Dabble\Query\Options` object. It must be constructed
  with an array following the same syntax.

The keys are automatically uppercased, so casing is irrelevant.

## `GROUP`
String or array of fields to group by.

## `HAVING`
String with SQL `HAVING` clause.

## `ORDER`
Either a literal string to order by, or an array in the following form:

```php
<?php

$options['order'] = [
    'foo' => 'asc',
    'bar' => 'desc',
];
// ORDER BY foo ASC, bar DESC

```

## `LIMIT`
`LIMIT` the query to `$value`.

## `OFFSET`
`OFFSET` the query by `$value`.

