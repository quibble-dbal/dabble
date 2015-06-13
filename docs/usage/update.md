# Updating
Update queries are identical to [insert queries](insert.md), except that they
receive a third parameter for the `where` clause. The third parameter is
required to prevent accidents and works the same as for
[select queries](select.md).

## Examples

```php
<?php

$where = ['id' => 1];
$columnValuePairs = ['foo' => 'bar'];
$affectedRows = $db->update('tablename', $columnValuePairs, $where);
// UPDATE tablename SET foo = 'bar' WHERE id = '1'

```

To force an update without a where-clause, simply do something like the
following:

```php
<?php

$db->update('tablename', $columnValuePairs, [1 => 1]); // WHERE 1 = '1'

```

If no rows are affected (i.e., the update failed or no rows were matched or
changed) a `Dabble\Query\UpdateException` is thrown.

## `Dabble\Query\Update`
You can also use the low-level `Update` object:

```php
<?php

$update = new Dabble\Query\Update(
    $table,
    $hashOfFieldsAndValues,
    new Dabble\Query\Where($where),
    new Dabble\Query\Options($options)
);
echo $update->__toString(); // E.g. UPDATE foo SET bar = 'baz' WHERE fizz = 'buzz'
$update->execute();

```

> Note that the Options object does not check if you're specifying valid
> options. E.g. for updates, probably only `LIMIT` and maybe `OFFSET` usually
> make sense.

