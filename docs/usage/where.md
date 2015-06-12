# The `where` array
Select, update and delete statements of course accept a `where` clause. This is
passed to Dabble as an array. This is how it works:

## Hash of key/value pairs
Essentially, the `where` array is a hash of key/value pairs, i.e.:

```php
<?php

$where = ['foo' => 'bar'];
// WHERE foo = 'bar'

```

## `AND`/`OR`
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

## Numeric indices
Numeric indices are simply passed on to the next "level of nesting".

## Subqueries and other raw data
To force passing non-escaped data, use `Dabble\Query\Raw` instead of an actual
value:

```php
<?php

use Dabble\Query\Raw;

$where = ['foo' => new Raw("SELECT id FROM sherwood WHERE merryman = 'robin'")];

```

