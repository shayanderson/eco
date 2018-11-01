## Bulk Database Methods
Bulk database methods are available for executing bulk queries.

#### Bulk Database Methods
- [Bulk Insert](#bulk-insert)
- [Bulk Replace](#bulk-replace)

### Bulk Insert
The database bulk `insert()` and `insertIgnore()` methods allow inserting multiple rows, example:
```php
// INSERT INTO table(x, y) VALUES
// (1, 2),
// (3, 4)
$bulk = db()->bulk()->insert('table');
$bulk->add(['x' => 1, 'y' => 2]);
$bulk->add(['x' => 3, 'y' => 4]);
$affected = $bulk->execute();

// the same as above except using objects instead of arrays
$row = new stdClass;
$row->x = 1;
$row->y = 2;
$row2 = new stdClass;
$row2->x = 3;
$row2->y = 4;
$bulk = db()->bulk()->insert('table');
$bulk->add($row);
$bulk->add($row2);
$affected = $bulk->execute();

// the same as above except use single array
$bulk = db()->bulk()->insert('table');
$bulk->addGroup([
    ['x' => 1, 'y' => 2],
    ['x' => 3, 'y' => 4]
]);
$affected = $bulk->execute();

// user INSERT IGNORE
$bulk = db()->bulk()->insertIgnore('table');
// ...
```

### Bulk Replace
The database bulk `replace()` method allows replacing multiple rows, example:
```php
// REPLACE INTO table(x, y) VALUES
// (1, 2),
// (3, 4)
$bulk = db()->bulk()->replace('table');
$bulk->add(['x' => 1, 'y' => 2]);
$bulk->add(['x' => 3, 'y' => 4]);
$affected = $bulk->execute();

// the same as above except using objects instead of arrays
$row = new stdClass;
$row->x = 1;
$row->y = 2;
$row2 = new stdClass;
$row2->x = 3;
$row2->y = 4;
$bulk = db()->bulk()->replace('table');
$bulk->add($row);
$bulk->add($row2);
$affected = $bulk->execute();

// the same as above except use single array
$bulk = db()->bulk()->replace('table');
$bulk->addGroup([
    ['x' => 1, 'y' => 2],
    ['x' => 3, 'y' => 4]
]);
$affected = $bulk->execute();
```