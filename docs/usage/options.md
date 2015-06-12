# The `options` array
Used mainly during `select`-style queries, the options argument is also a
hash. Essentially, it accepts key/value pairs of option names and their values.

The keys are automatically uppercased, so casing is irrelevant.

## `group`
Array of fields to group by.

## `order`
Either a literal string to order by, or an array in the following form:

```php
<?php

$options['order'] = [
    'foo' => 'asc',
    'bar' => 'desc',
];
// ORDER BY foo ASC, bar DESC

```

## `limit`
`LIMIT` the query to `$value`.

## `offset`
`OFFSET` the query to `$value`.

