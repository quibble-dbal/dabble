# Deleting
Identical to update queries, but without the second parameter:

```php
<?php

$affectedRows = $db->delete('tablename', $where);

```

If no rows are affected (i.e., the delete failed or no rows were matched) a
`Dabble\Query\DeleteException` is thrown.

## `Dabble\Query\Delete`
You can also use the low-level `Delete` object:

```php
<?php

$delete = new Dabble\Query\Delete(
    $table,
    new Dabble\Query\Where($where),
    new Dabble\Query\Options($options)
);
echo $delete->__toString(); // E.g. DELETE FROM foo WHERE bar = 'baz'
$delete->execute();

```

> Note that the Options object does not check if you're specifying valid
> options. E.g. for deletes, probably only `LIMIT` and maybe `OFFSET` usually
> make sense.

