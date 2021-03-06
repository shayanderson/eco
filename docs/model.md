## Using Models
The `Model` class can be used to simplify model classes and database calls.

#### Model Topics
- [Basic Usage](#basic-usage)
  - [Public Methods](#public-methods)
- [Count](#count) - [`count()`](#count)
- [Get Single Row](#get-single-row) - [`get()`](#get-single-row)
- [Get All Rows](#get-all-rows) - [`getAll()`](#get-all-rows)
- [Check if Rows Exist](#check-if-rows-exist) - [`has()`](#check-if-rows-exist)
- [Get Single Column Value](#get-single-column-value) - [`value()`](#get-single-column-value)
- [Delete](#delete) - [`delete()`](#delete)
- [Insert](#insert) - [`insert()`](#insert)
- [Replace](#replace) - [`replace()`](#replace)
- [Update](#update) - [`update()`](#update)
- [Index](#index) (Pagination) - [`index()`](#index)
- [Truncate](#truncate) - [`truncate()`](#truncate)
- [Execute a Query](#execute-a-query) - [`query()`](#execute-a-query)



### Basic Usage
First, model classes need to be "registered" in the *model registry* in the `app/com/model.php` file. Each model class is added in the comment block above the `EcoModelRegistry` class using the `@property <class> <property>` syntax, for example:
```php
/**
 * @property App\Model\Document $doc
 * @property App\Model\Document\Entity $doc_entity
 * @property App\Model\User $user
 */
class EcoModelRegistry extends \Eco\System\Registry\Model {}
```
Now each of these registered model classes can be accessed using the `eco::model()` method or the helper function `model()` (used in examples below).

Here is an example model class `App\Model\Document`:
```php
namespace App\Model;

class Document extends \Eco\Model
{
    // database table name
    const NAME = 'document';

    // database primary key column name
    // only required if PK column name is not 'id'
    // const PK = 'table_id';

    // database connection ID
    // only required if not using the default connection ID
    // const CONNECTION_ID = 2;

    public function get($id)
    {
        // SELECT * FROM document WHERE id = $id
        return $this->db->get($id);
    }
}
```
The class constant `NAME` **must be set** with the name of the database table the model represents.

Now the `App\Model\Document` class can be used:
```php
// SELECT * FROM document WHERE id = 5
$row = model()->doc->get(5);
```

#### Public Methods
Because the `App\Model\Document` extends the `Eco\Model` class there are several public (final) methods available by default that can be called *outside* the `Document` class:
```php
// get count of all rows
$count = model()->doc->countRows();

// get row with primary key value of 5 (numeric values only)
$row = model()->doc->getRow(5);

// get all rows
$rows = model()->doc->getRows();

// check if row with primary key value of 5 exists (numeric values only)
$has = model()->doc->hasRow(5);

// get the table name (in this case "document")
$name = model()->doc->name();
```
All the other methods listed below are used under the `Eco\Model` private propery `db` and cannot be used outside the model class.


### Count
The `count()` method returns `int`
```php
// count all rows
$count = $this->db->count();

// with SQL
$row = $this->db->count('WHERE x = ? AND y = ?', 1, 2);
// or without WHERE keyword
$row = $this->db->count('x = ? AND y = ?', 1, 2);
```
> The `model()->name->countRows()` method can be used outside the model class to count all rows (see [public methods](#public-methods))


### Get Single Row
The `get()` method returns a single row as `stdClass`, or `null` on no results:
```php
// get by primary key value
$row = $this->db->get(5);

// with SQL
$row = $this->db->get('WHERE x = ? AND y = ?', 1, 2);
// or without WHERE keyword
$row = $this->db->get('x = ? AND y = ?', 1, 2);

// with columns
// SELECT col, col2 AS c2 FROM table WHERE x = 1 AND y = 2 LIMIT 1
$row = $this->db->get('(col, col2 AS c2) WHERE x = ? AND y = ?', 1, 2);
// or without WHERE keyword
$row = $this->db->get('(col, col2 AS c2) x = ? AND y = ?', 1, 2);
```
> The `model()->name->getRow($id)` method can be used outside the model class to get a single row by numeric primary key value (see [public methods](#public-methods))


### Get All Rows
The `getAll()` method returns an `array` of `stdClass` objects (or empty `array` on no rows)
```php
// get all rows
$rows = $this->db->getAll();

// with SQL
$rows = $this->db->getAll('ORDER BY x, y');
$rows = $this->db->getAll('WHERE x = ? AND y = ?', 1, 2);

// with columns
// SELECT col, col2 AS c2 FROM table WHERE x = 1 AND y = 2
$rows = $this->db->getAll('(col, col2 AS c2) WHERE x = ? AND y = ?', 1, 2);
```


### Check if Rows Exist
The `has()` method returns `boolean` value
```php
// check by primary key value
$has = $this->db->has(5);

// with SQL
$has = $this->db->has('WHERE x = ? AND y = ?', 1, 2);
// or without WHERE keyword
$has = $this->db->has('x = ? AND y = ?', 1, 2);
```
> The `model()->name->hasRow($id)` method can be used outside the model class to check if a single row exists by numeric primary key value (see [public methods](#public-methods))


### Get Single Column Value
Get single column value for primary key value
```php
// with SQL
$col = $this->db->value('column_name WHERE x = ? AND y = ?', 1, 2);
// or without WHERE keyword
$col = $this->db->value('column_name x = ? AND y = ?', 1, 2);
```


### Delete
The `delete()` method returns `int` (affected)
```php
// delete by primary key value
$has = $this->db->delete(5);

// with SQL
$has = $this->db->delete('WHERE x = ? AND y = ?', 1, 2);
// or without WHERE keyword
$has = $this->db->delete('x = ? AND y = ?', 1, 2);
```


### Insert
The `insert()` method returns `int` (affected)
```php
$affected = $this->db->insert(['x' => 1, 'y' => 2]);
$insert_id = $this->db->id();
// or use single method
$insert_id = $this->db->insertId(['x' => 1, 'y' => 2]);

// or INSERT IGNORE
$affected = $this->db->insertIgnore(['x' => 1, 'y' => 2]);

// or use object
$row = new stdClass;
$row->x = 1;
$row->y = 2;
$affected = $this->db->insert($row);
```


### Replace
Replace method `replace()` is used the same way as the `insert()` method


### Update
The `update()` method returns `int` (affected)
```php
// update all
$affected = $this->db->update(['x' => 1, 'y' => 2]);

// update by primary key value
$affected = $this->db->update(5, ['x' => 1, 'y' => 2]);
// or update by primary key value and use object
$row = new stdClass;
$row->x = 1;
$row->y = 2;
$affected = $this->db->update(5, $row);

// update by SQL
$affected = $this->db->update('WHERE a = :a',
	['x' => 1, 'y' => 2, ':a' => 1]);
// or without WHERE keyword
$affected = $this->db->update('a = :a',
	['x' => 1, 'y' => 2, ':a' => 1]);
```
The `updateIgnore()` method can be used for `UPDATE IGNORE` statements, example:
```php
$affected = $this->db->updateIgnore(5, ['x' => 1, 'y' => 2]);
```


### Index
The `index()` is a pagination method and returns a `Eco\System\Database\Pagination` object, example:
```php
// SELECT * FROM table
$index = $this->db->index();

// with sql
$index = $this->db->index('ORDER BY x, y');
$index = $this->db->index('WHERE x = ? AND y = ?', 1, 2);

// with columns
// SELECT col, col2 FROM table WHERE x = 1 AND y = 2
$index = $this->db->index('(col, col2) WHERE x = ? AND y = ?', 1, 2);
```
> See how to use the `Pagination` object [here](https://github.com/shayanderson/eco/blob/master/docs/database.md#pagination)


### Truncate
```php
// do truncate
$this->db->truncate();
```


### Execute a Query
Any query can be executed:
```php
// SELECT a.col, b.col2
// FROM table a
// JOIN table2 b ON b.id = a.b_id
// WHERE a.x = 1 AND b.y = 2
$rows = $this->db->query('SELECT a.col, b.col2'
    . ' FROM ' . self::NAME . ' a'
    . ' JOIN ' . model()->table2->name() . ' b ON b.id = a.b_id'
    . ' WHERE a.x = ? AND b.y = ?', 1, 2);
```