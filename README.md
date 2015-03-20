# dabble
PHP5 database abstraction layer

## Installation

## Basic querying
Instantiate a Dabble database object using your credentials:

    use Dabble\Adapter\Mysql;

    $db = new Mysql($dsn, $user, $pass, $options);

The database type (e.g. `mysql:` in the above example) is added by Dabble.

Dabble supports four 'main' types of queries: `select`, `insert`, `update` and
`delete`. These have corresponding method names on the `Dabble\Adapter` object.
They all follow a similar syntax where argument one is the table name, and
further arguments are arrays or key/value pairs.

## Select queries
    $result = $db->select('tablename', $fields, $where, $options);

`select` returns a `Dabble\Result` object. Internally this is converted to:

    "SELECT $fields FROM 'tablename' WHERE $where $options"

The tablename is simply a string that is injected verbatim; the other arguments
can be either strings (for 'raw' querying) or arrays or key/value pairs. For
full documentation on all the options here, see the API docs for
`Adapter::select`.

If the query does not yield any results, a `Dabble\Query\SelectException` is
thrown.

### Handling results
Multi-row Dabble results are returned as a lambda implementing a `Generator`.

    foreach ($result() as $row) {
        echo $row['field'];
    }

Returned rows are fetched using `PDO::FETCH_ASSOC`.

### Single-row queries
If you know beforehand you will only need a single row from a result set, use
`Dabble\Adapter::fetch` instead:

    $row = $db->fetch('tablename', $fields, $where, $options);

This will return a single array of key/value pairs, and automatically injects
`LIMIT 1` into your query.

### Single-column queries
If you know beforehand you will only need a single column from a result set, use
`Dabble\Adapter::column` instead:

    $col = $db->column('tablename', $fields, $where, $options);

Like `row`, this adds `LIMIT 1` to the query, and also drops all fields except
the first prior to execution.

## Insert queries
    $affectedRows = $db->insert('tablename', $columnValuePairs);

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

    $affectedRows = $db->update('tablename', $columnValuePairs, $where);

To force an update without a where-clause, simply do something like the
following:

    $db->update('tablename', $columnValuePairs, [1 => 1]); // WHERE 1 = '1'

If no rows are affected (i.e., the update failed or no rows were matched or
changed) a `Dabble\Query\UpdateException` is thrown.

## Delete queries
Identical to update queries, but without the second parameter:

    $affectedRows = $db->delete('tablename', $where);

If no rows are affected (i.e., the delete failed or no rows were matched) a
`Dabble\Query\DeleteException` is thrown.
