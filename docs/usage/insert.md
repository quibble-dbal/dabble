# Inserting
Dabble adapters offer an array-based version of inserts:

```php
<?php

$affectedRows = $db->insert('tablename', $columnValuePairs);

```

`$columnValuePairs` contains a hash where the keys are the column names to
insert into, and the values are the values to associate with. The values are
properly parameterized, except when they are an instance of `Dabble\Query\Raw`
(in which case they will be inserted verbatim and escaping is up to the
programmer).

If no rows are affected (i.e., the insert failed) a
`Dabble\Query\InsertException` is thrown.

## Examples
Insert a row where column `bar` has the string `NOW()`:

```php
<?php

$db->insert('foo', ['bar' => 'NOW()']);

```

Insert a row where column `bar` has the result of the SQL function `NOW()`:

```
<?php

use Dabble\Query\Raw;

$db->insert('foo', ['bar' => new Raw('NOW()')]);

```

> Note that adapters also offer a `now()` method, with possible vendor-specific
> syntax for the current timestamp.

## `Dabble\Query\Insert`
You can also use the low-level `Insert` object:

```php
<?php

$insert = new Dabble\Query\Insert(
    $table,
    $hashOfFieldsAndValues
);
echo $insert->__toString(); // E.g. INSERT INTO foo (bar) VALUES ('1')
$insert->execute();

```

`Insert::execute` returns the number of affected rows. If no rows were
affected, a `Dabble\Query\InsertException` is thrown. If your SQL contains
errors, a `Dabble\Query\SqlException` is thrown.

