# Deleting
Identical to update queries, but without the second parameter:

```php
<?php

$affectedRows = $db->delete('tablename', $where);

```

If no rows are affected (i.e., the delete failed or no rows were matched) a
`Dabble\Query\DeleteException` is thrown.

