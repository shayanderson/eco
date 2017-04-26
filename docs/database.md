## Database Class
The Database class is used to execute database calls and can be accessed using `eco::db()` or the helper function `db()` (used in examples below).

#### Database Topics
- [Connections](#database-connections)
- [Logging](#logging)
- [Count Rows](#count-rows)
- [Get Single Row](#get-single-row)
- [Get All Rows](#get-all-rows)
- [Check if Rows Exist](#check-if-rows-exist)
- [Get Single Value](#get-single-value)
- [Delete](#delete)
- [Insert](#insert)
- [Get Last Insert ID](#get-last-insert-id)
- [Replace](#replace)
- [Update](#update)
- [Execute a Query](#execute-a-query)
- [Call Stored Procedure](#call-stored-procedure)
- [Pagination](#pagination)
- [Transactions](#transactions)
- [Other Methods](#other-methods)


### Database Connections
Database connections must first be setup in the Eco configuration file `app/com/conf/eco.conf.php` under the `database` > `connection` section. Settings for a single connection are already set:
```php
'connection' => [
	1 => [
		'host' => 'localhost',
		'database' => 'dbname',
		'user' => 'dbuser',
		'password' => 'userpwd',
		'log' => false
```
This database connection ID is `1` (the array key). When using multiple connections use a different array key for each connection ID, for example:
```php
'connection' => [
	1 => [
		'host' => 'localhost',
		[...]
	'remote' => [
	    'host' => '0.0.0.0'
	    [...]
```
The default connection ID is `1` and can be used without using the connection ID, example:
```php
db()->count('table');
```
When another connection is used (that is not the default connection) the connection ID must be used for each call:
```php
db('remote')->count('table');
```
> The database connection object `Eco\System\Database\Connection` will automatically reconnect to the database server when a `server has gone away` error is thrown - it will only try to reconnect once per query.


### Logging
When query logging is enabled for a connection (see [connection settings](#database-connections)) it will retain the last 1,000 executed statements. The log can be used for debugging, for example:
```php
// execute queries
$rows = db()->getAll('table LIMIT 10');
$rows = db()->getAll('table2 WHERE x = ?', 1);

// get the log
$log = db()->log();
/* $log contains array of log entries:
Array
(
    [0] => Array
        (
            [query] => SELECT * FROM table LIMIT 10
            [params] =>
        )

    [1] => Array
        (
            [query] => SELECT * FROM table2 WHERE x = ?
            [params] => Array
                (
                    [0] => 1
                )

        )

)
*/
```
> Each database connection has its own separate log so the connection ID must be used when not using the default connection, example:
```php
$log = db()->log(); // default connection log
$log_remote = db('remote')->log(); // connection 'remote' log
```


### Count Rows
The `count()` method returns `int`, example:
```php
// SELECT COUNT(1) FROM table
$count = db()->count('table');

// SELECT COUNT(1) FROM table WHERE x = 1 AND y = 2
$count = db()->count('table WHERE x = ? AND y = ?', 1, 2);
```


### Get Single Row
The `get()` method returns a single row as a `stdClass` object, example:
```php
// SELECT * FROM table LIMIT 1
$row = db()->get('table');

// SELECT * FROM table WHERE x = 1 AND y = 2 LIMIT 1
$row = db()->get('table WHERE x = ? AND y = ?', 1, 2);

// or full query
$row = db()->get('SELECT col, col2 FROM table WHERE x = ? AND y = ?', 1, 2);
```
> The `get()` method will return `null` if there are no results


### Get All Rows
The `getAll()` method returns an array of rows (as `stdClass` objects), example:
```php
// SELECT * FROM table
$rows = db()->getAll('table');

// SELECT * FROM table WHERE x = 1 AND y = 2
$rows = db()->getAll('table WHERE x = ? AND y = ?', 1, 2);
```
> The `getAll()` method will return an empty `array` if there are no results


### Check if Rows Exist
The `has()` method returns a `boolean` value, `true` if the row(s) exists and `false` if not, example:
```php
// SELECT EXISTS(SELECT 1 FROM table)
$has = db()->has('table');

// SELECT EXISTS(SELECT 1 FROM table WHERE x = 1 AND y = 2)
$has = db()->has('table WHERE x = ? AND y = ?', 1, 2);
```


### Get Single Value
The `value()` method returns a single value as string, example:
```php
// SELECT a FROM table WHERE x = 1 AND y = 2
$a = db()->value('SELECT a FROM table WHERE x = ? AND y = ?', 1, 2);
// $a equals the value of 'table.a'
```


### Delete
The `delete()` method returns affected rows as `int`, example:
```php
// DELETE FROM table
$affected = db()->delete('table');

// DELETE FROM table WHERE x = 1 AND y = 2
$affected = db()->delete('table WHERE x = ? AND y = ?', 1, 2);
```


### Insert
The `insert()` method returns affected rows as `int`, example:
```php
// INSERT INTO table(x, y) VALUES(1, 2)
$affected = db()->insert('table', ['x' => 1, 'y' => 2]);

// the same as above except using an object instead of array
// INSERT INTO table(x, y) VALUES(1, 2)
$row = new stdClass;
$row->x = 1;
$row->y = 2;
$affected = db()->insert('table', $row);

// INSERT IGNORE INTO table(x, y) VALUES(1, 2)
$affected = db()->insertIgnore('table', ['x' => 1, 'y' => 2]);
```


### Get Last Insert ID
The `id()` method returns the last insert ID (as `int` if numeric ID), example:
```php
// do insert
$affected = db()->insert('table', ['x' => 1, 'y' => 2]);

// get insert ID
$id = db()->id();
```


### Replace
The `replace()` method returns affected rows as `int`, example:
```php
// REPLACE INTO table(x, y) VALUES(1, 2)
$affected = db()->replace('table', ['x' => 1, 'y' => 2]);

// the same as above except using an object instead of array
// REPLACE INTO table(x, y) VALUES(1, 2)
$row = new stdClass;
$row->x = 1;
$row->y = 2;
$affected = db()->replace('table', $row);
```


### Update
The `update()` method returns affected rows as `int`, example:
```php
// UPDATE table SET x = 1, y = 2
db()->update('table', ['x' => 1, 'y' => 2]);

// UPDATE table SET y = 2 WHERE x = 1
db()->update('table WHERE x = :x', ['y' => 2, ':x' => 1]);

// update using object
// UPDATE table SET x = 1, y = 2
$data = new stdClass;
$data->x = 1;
$data->y = 2;
db()->update('table', $data);
```


### Execute a Query
Any query can be executed using the `query()` method:
```php
// SELECT a.col, b.col2 FROM table a
//    JOIN table2 b ON b.id = a.b_id WHERE x = 1 AND y = 2
$rows = db()->query('SELECT a.col, b.col2 FROM table a'
    . ' JOIN table2 b ON b.id = a.b_id WHERE x = ? AND y = ?', 1, 2);
```
> An array of params can be used instead of method params, example:
```php
// SELECT * FROM table WHERE x = 1 AND y = 2
$rows = db()->queryArrayParam('SELECT * FROM table WHERE x = ? AND y = ?', [1, 2]);
```


### Call Stored Procedure
```php
// CALL sp_test(1, 2)
db()->call('sp_test', 1, 2);
```
The `call()` method return a `boolean` value - `true` on success, `false` on error. If another return type is required use `callAffected()` or `callRows()`, for example:
```php
// return number of affected rows
// CALL sp_updateQueue()
$affected = db()->callAffected('sp_updateQueue');

// return rows
// CALL sp_getQueueActive()
$rows = db()->callRows('sp_getQueueActive');
```


### Pagination
The `pagination()` method returns a `Eco\System\Database\Pagination` object, example:
```php
// SELECT a, b FROM table WHERE x = 1 AND y = 2 LIMIT <page>, <records_per_page>
$p = db()->pagination('SELECT a, b FROM table WHERE x = ? AND y = ?', 1, 2);

// has rows
if($p->has)
{
    foreach($p->rows as $v)
    {
        // print row
        echo "{$v->a}, {$v->b}<br />";
    }
    // print pagination controls
    echo $p;
)
else
{
    // warn no rows
}
```
> Pagination settings can be found in the Eco configuration file `app/com/conf/eco.conf.php` under the `database` > `pagination` section, including styles for pagination controls


### Transactions
Transactions can be used, for example:
```php
try
{
    // start transaction (autocommit off)
    db()->transaction();

    // execute queries

    // commit
    db()->commit();
}
catch(\PDOException $ex)
{
     // problem(s), do rollback
    db()->rollback();

    // warn client
}
```


### Other Methods
The following methods are also available

#### Dynamically Create Connection
A database connection can be dynamically created instead of using the Eco configuration settings, for example:
```php
db()->connectionRegister('db_id', 'localhost', 'database_name', 'user', 'password');
// now use like:
db('db_id')->count('table');
```

#### Close Connection
```php
// force close the database connection
db()->close();
```
> **Note:** the database connection will close automatically - the `close()` method only needs to be called when forcing a connection close.

#### Get Columns
Get table column names:
```php
// returns array of column names like:
// ['col', col2']
$columns = db()->getColumns('table');
```

#### Get Tables
Get database table names:
```php
// returns array of database table names like:
// ['table1', 'table2']
$tables = db()->getTables();
```

#### Truncate Table
A table can be truncated using:
```php
db()->truncate('table');
```