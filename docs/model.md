## Using Models
The `Model` class can be used to simplify model classes and database calls.

#### Model Topics
- [Basic Usage](#basic-usage)
- [Count](#count)
- [Get Single Row](#get-single-row)
- [Get All Rows](#get-all-rows)
- [Check if Rows Exist](#check-if-rows-exist)
- [Get Single Column Value](#get-single-column-value)
- [Delete](#delete)
- [Insert](#insert)
- [Replace](#replace)
- [Update](#update)
- [Truncate](#truncate)
- [Execute a Query](#execute-a-query)
- [Get Model Name](#get-model-name)


### Basic Usage
First, model classes need to be "registered" in the *model registry* in the `app/com/model.php` file. Each model class is added in the comment block above the `EcoModelRegistry` class using the `@property <class> <property>` syntax, for example:
```php
/**
 * @property App\Model\Document $doc
 * @property App\Model\Document\Entity $doc_entity
 * @property App\Model\User $user
 */
class EcoModelRegistry extends \Eco\System\ModelRegistry {}
```
Now each of these registered model classes can be accessed using the `eco::model()` method or the helper function `model()` (used in all the examples below).

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
        // SELECT * FROM doc WHERE id = $id
        return $this->db->get($id);
    }
}
```
The class constant `NAME` **must be set** with the name of the database table the model represents.

Because this model extends the `Eco\Model` class, methods can be used outside the model class, for example:
```php
// SELECT COUNT(1) FROM doc
$count = model()->doc->db->count();
```


### Count
The `count()` method returns `int`
```php
// count all rows
$count = model()->model_name->db->count();

// with SQL
$row = model()->model_name->db->count('WHERE x = ? AND y = ?', 1, 2);
// or without WHERE keyword
$row = model()->model_name->db->count('x = ? AND y = ?', 1, 2);
```


### Get Single Row
The `get()` method returns a single row as `stdClass`, or `null` on no results:
```php
// get by primary key value
$row = model()->model_name->db->get(5);

// with SQL
$row = model()->model_name->db->get('WHERE x = ? AND y = ?', 1, 2);
// or without WHERE keyword
$row = model()->model_name->db->get('x = ? AND y = ?', 1, 2);

// with columns
// SELECT col1, col2 FROM model_name WHERE x = 1 AND y = 2 LIMIT 1
$row = model()->model_name->db->get('(col, col2) WHERE x = ? AND y = ?', 1, 2);
// or without WHERE keyword
$row = model()->model_name->db->get('(col, col2) x = ? AND y = ?', 1, 2);
```


### Get All Rows
The `getAll()` method returns an `array` of `stdClass` objects (or empty `array` on no rows)
```php
// get all rows
$rows = model()->model_name->db->getAll();

// with SQL
$rows = model()->model_name->db->getAll('ORDER BY x, y');
$rows = model()->model_name->db->getAll('WHERE x = ? AND y = ?', 1, 2);

// with columns
// SELECT col1, col2 FROM model_name WHERE x = 1 AND y = 2
$rows = model()->model_name->db->getAll('(col, col2) WHERE x = ? AND y = ?', 1, 2);
```


### Check if Rows Exist
The `has()` method returns `boolean` value
```php
// check by primary key value
$has = model()->model_name->db->has(5);

// with SQL
$has = model()->model_name->db->has('WHERE x = ? AND y = ?', 1, 2);
// or without WHERE keyword
$has = model()->model_name->db->has('x = ? AND y = ?', 1, 2);
```


### Get Single Column Value
Get single column value for primary key value
```php
// with SQL
$col1 = model()->model_name->db->value('column_name WHERE x = ? AND y = ?', 1, 2);
// or without WHERE keyword
$col1 = model()->model_name->db->value('column_name x = ? AND y = ?', 1, 2);
```


### Delete
The `delete()` method returns `int` (affected)
```php
// delete by primary key value
$has = model()->model_name->db->delete(5);

// with SQL
$has = model()->model_name->db->delete('WHERE x = ? AND y = ?', 1, 2);
// or without WHERE keyword
$has = model()->model_name->db->delete('x = ? AND y = ?', 1, 2);
```


### Insert
The `insert()` method returns `int` (affected)
```php
$affected = model()->model_name->db->insert(['x' => 1, 'y' => 2]);
$insert_id = model()->model_name->db->id();

// or INSERT IGNORE
$affected = model()->model_name->db->insert(['x' => 1, 'y' => 2], true);

// or use object
$row = new stdClass;
$row->x = 1;
$row->y = 2;
$affected = model()->model_name->db->insert($row);
```


### Replace
Replace method `replace()` is used the same way as the `insert()` method


### Update
The `update()` method returns `int` (affected)
```php
// update all
$affected = model()->model_name->db->update(['x' => 1, 'y' => 2]);

// update by primary key value
$affected = model()->model_name->db->update(5, ['x' => 1, 'y' => 2]);

// update by SQL
$affected = model()->model_name->db->update('WHERE a = :a',
	['x' => 1, 'y' => 2, ':a' => 1]);
// or without WHERE keyword
$affected = model()->model_name->db->update('a = :a',
	['x' => 1, 'y' => 2, ':a' => 1]);
```


### Truncate
```php
// do truncate
model()->model_name->db->truncate();
```


### Execute a Query
Any query can be executed:
```php
// SELECT a.col, b.col2 FROM table a
//    JOIN table2 b ON b.id = a.b_id WHERE x = 1 AND y = 2
$rows = model()->model_name->db->query('SELECT a.col, b.col2 FROM table a'
    . ' JOIN table2 b ON b.id = a.b_id WHERE x = ? AND y = ?', 1, 2);
```


### Get Model Name
```php
$name = model()->model_name->name();
```