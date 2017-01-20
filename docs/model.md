## Using Models
The `Model` class can be used to simplify model classes and database calls.

#### Model Topics
- [Basic Usage](#basic-usage)
- [Public Methods](#public-methods)
  - [Count](#count)
  - [Delete Single Row](#delete-single-row)
  - [Get Row](#get-row)
  - [Get All Rows](#get-all-rows)
  - [Check if Row Exists](#check-if-row-exists)
  - [Get Model Name](#get-model-name)
  - [Get Single Column Value](#get-single-column-value)
- [Protected Methods](#protected-methods)
  - [Advanced Delete](#advanced-delete)
  - [Insert](#insert)
  - [Replace](#replace)
  - [Update](#update)
  - [Truncate](#truncate)


### Basic Usage
First, model classes need to be "registered" in the *model registry* in the `app/com/model.php` file. Each model class is added in the comment block above the `EcoModelRegistry` class using the `@property <class> <property>` syntax, for example:
```php
/**
 * @property App\Model\Document $doc
 * @property App\Model\Document\Entity $doc_entity
 * @property App\Model\User $user
 */
class EcoModelRegistry extends \Eco\Factory\ModelRegistry {}
```
Now each of these registered model classes can be accessed using the `eco::model()` method or the helper function `model()` (used in all the examples below).

Here is an example model class `App\Model\Document`:
```php
namespace App\Model;

class Document extends \Eco\Model
{
    // database table name
    const NAME = 'document';
}
```
The class constant `NAME` **must be set** with the name of the database table the model represents.

Because this model extends the `Eco\Model` class basic methods can be used, for example:
```php
// SELECT COUNT(1) FROM doc
$count = model()->doc->count();

// SELECT * FROM doc WHERE id = 5
$row = model()->doc->get(5);
```


### Public Methods
These are the main model methods and can be overridden


##### Count
```php
// returns int
$count = model()->model_name->count();
```


##### Delete Single Row
Delete row by primary key ID value
```php
// returns int
$affected = model()->model_name->delete(5);
```


##### Get Row
Get single row by primary key ID value
```php
// returns stdClass (or null on no row)
$row = model()->model_name->get(5);
```


##### Get All Rows
```php
// returns array of stdClass objects (or empty array on no rows)
$rows = model()->model_name->getAll();
```


##### Check if Row Exists
Check if row exists by primary key ID value
```php
// returns boolean
$row = model()->model_name->has(5);
```


##### Get Model Name
```php
$name = model()->model_name->name();
```


##### Get Single Column Value
Get single column value for primary key ID value
```php
$col1 = model()->model_name->value(5, 'col1');
```


### Protected Methods
These are the methods that can only be used from inside a model classes and cannot not be  overridden - this keeps all advanced model logic *inside* the model classes


##### Advanced Delete
The `_delete()` method is more advanced than the `delete()` method, example:
```php
class ModelName extends \Eco\Model
{
    public function deleteActive()
    {
        // returns int (affected)
        return $this->_delete('WHERE is_active = ?', 1);
    }
}
```


##### Insert
Insert a row
```php
class ModelName extends \Eco\Model
{
    public function create()
    {
        // int (affected)
        $affected = $this->_insert(['x' => 1, 'y' => 2]);

        // return the insert ID
        return $this->_id();

        // or INSERT IGNORE
        $affected = $this->_insert(['x' => 1, 'y' => 2], true);

        // or use object
        $row = new stdClass;
        $row->x = 1;
        $row->y = 2;
        $affected =$this->_insert($row);
    }
}
```


##### Replace
Replace method `_replace()` is used the same way as the `_insert()` method


##### Update
```php
class ModelName extends \Eco\Model
{
    public function create()
    {
        // update all
        // returns int (affected)
        return $this->_update(['x' => 1, 'y' => 2]);

        // or with WHERE clause
        return $this->_update('WHERE a = :a',
            ['x' => 1, 'y' => 2, ':a' => 1]);
    }
}
```


##### Truncate
```php
class ModelName extends \Eco\Model
{
    public function truncate()
    {
        // do truncate
        $this->_truncate();
    }
}
```