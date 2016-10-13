## Breadcrumb Class
The `\Eco\System::breadcrumb()` method is used to access the Breadcrumb class that can be used for an HTML navigation breadcrumb, for example in a controller add items:
```php
// add breadcrumbs
eco::breadcrumb()->add('Home', '/');
eco::breadcrumb()->add('Category 1', '/category-1.htm');
eco::breadcrumb()->add('Current Page');
```
Or the breadcrumbs can be set using an array:
```php
eco::breadcrumb()->add([
	'/' => 'Home',
	'/category-1.htm' => 'Category 1',
	'Current Page'
]);
```

Then in the view template display breadcrumb HTML:
```html+php
<?=eco::breadcrumb()?>
```
Which will output HTML like:
```html
<a href="/">Home</a> &raquo; <a href="/category-1.htm">Category 1</a> &raquo; Current Page
```

### Base Items
A base item, or base items, can be used:
```php
// globally set 'Home' as base item (will be included in every breadcrumb)
eco::breadcrumb()->base('Home', '/');
```
Or, multiple base items can be set using:
```php
eco::breadcrumb()->base([
	'/' => 'Home',
	'/account/' => 'Account'
]);
```

### Before and After Wrappers
*Before* and *after* wrappers can be used, for example:
```php
// must be set before adding items
eco::breadcrumb()->wrapper_before = '<div class="breadcrumb">';
eco::breadcrumb()->wrapper_after = '</div>';
```
Now the breadcrumb items will be wrapped like `<div class="breadcrumb">[items]</div>`

### Templates
Item templates are used to set the HTML for each breadcrumb item, for example:
```php
// set template for items
eco::breadcrumb()->template = '<a href="{$url}">{$title}</a>';
// set template for active item (item without URL)
eco::breadcrumb()->template_active = '{$title}';
```

### Item Separator
The breadcrumb item separator can be changed using:
```php
eco::breadcrumb()->separator = ' / ';
```

### Filters
Global filters can be used to modify all titles and/or URLs, for example:
```php
// upper case all titles
eco::breadcrumb()->filter_title = function($title) { return strtoupper($title); };

// urlencode all URL parts
eco::breadcrumb()->filter_url = function($url)
{
	return implode('/', array_map('urlencode', explode('/', $url)));
};
```

### Get Methods
A single breadcrumb array can be returned, for example:
```php
$first_item = eco::breadcrumb()->getItem(0);
```
This will return an array like `[0 => title, 1 => uri]`, or `[0 => title]` when there is no URI (last item), or `null` if breadcrumb index does not exist.

All breadcrumb items can be returned, for example:
```php
$items = &eco::breadcrumb()->getItems(); // array with all items
```