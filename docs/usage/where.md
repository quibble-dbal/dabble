# `WHERE` clauses
Select, update and delete statements of course accept a `WHERE` clause. Dabble
offers a flexible way to build these clauses.

## For methods on a `Dabble\Adapter` object
For methods on an [adapter](../api/adapter.md) (`fetch`, `select` etc.), you
pass the clauses as an array:

```php
<?php

$results = $adapter->select('tablename', ['fields'], ['foo' => 'bar']);
// WHERE foo = 'bar'

```

### `AND`/`OR`
For all *even* nesting levels, the clause is `AND`. For all *uneven* nesting
levels, the clause is `OR`. Hence:

```php
<?php

$where = ['foo' => 'bar', 'baz' => 'buz'];
// WHERE (foo = 'bar' AND baz = 'buz')

$where = [['foo' => 'bar', 'baz' => 'buz']];
// WHERE ((foo = 'bar' OR baz = 'buz'))

$where = ['foo' => 'bar', 'baz' => 'buz', ['darth' => 'vader']];
// WHERE (foo = 'bar' AND baz = 'buz' OR (darth = 'vader'))

```

You can nest as many levels as your own sanity can handle.

### Numeric indices
Numeric indices are simply passed on to the next "level of nesting".

## For Query objects
Internally, Dabble uses a set of helper classes to generate queries. The `WHERE`
array is passed as a constructor to the `Dabble\Query\Where` class. You can use
this manually, too:

```php
<?php

$where = new Dabble\Query\Where(['foo' => 'bar']);
echo "$where"; // WHERE foo = ?
echo $where->getBindings()[0]; // bar

```

### Subqueries and other raw data
To force passing non-escaped data, use `Dabble\Query\Raw` instead of an actual
value:

```php
<?php

use Dabble\Query\Raw;

$where = ['foo' => new Raw("SELECT id FROM sherwood WHERE merryman = 'robin'")];

```

You can also pass subqueries as Query objects:

```php
<?php

$adapter->select(
    'tablename',
    ['alias' => new Select('othertable', 'field', new Where(['id' => 1]))]
);

```

