## Form Class

The `\Eco\Form` class can be used for HTML forms, for example create a form object and use in application:
```php
class CreateUserForm extends \Eco\Form
{
    const FIELD_NAME = 'name';
	const FIELD_EMAIL = 'email';
	const FIELD_PASSWORD = 'pwd';

	public function __construct($user)
	{
	    // set form object with POST request + 'login_form' as form ID
		parent::__construct($_POST, 'form_create_user');

		$this->text(self::FIELD_NAME)
		    ->validateRequired('Name is required');

		$this->email(self::FIELD_EMAIL)
			->validateEmail('Email address is required');

		$this->password(self::FIELD_PASSWORD)
			->validateRequired('Password is required');

		if($this->isSubmitted() && $this->isValid()
		{
		    // process form
		    User::create($this->{self::FIELD_NAME},
		        $this->{self::FIELD_EMAIL},
		        $this->{self::FIELD_PASSWORD});
		}
	}
}
```
Before outputting the form set field decorators:
```php
// set decorator for field errors
\Eco\Form::$decorator_errors = '<div class="errors">{$errors}</div>';
\Eco\Form::$decorator_errors_message = '{$error}<br />';
```
Then in the view template display form:
```html+php
<form method="post">
	<!-- form listener -->
	<?=$form?>

	<label>Name:</label>
	<?php echo $form->getErrors('name'); // display validation errors ?>
	<?php echo $form->get('name'); // display username field ?><br />

	<label>Email:</label>
	<?=$form->getErrorsAndField($form::FIELD_EMAIL) // shorthand method ?><br />

	<label>Password:</label>
	<?=$form->getErrorsAndField($form::FIELD_PASSWORD)?><br />

	<input type="submit" value="Create" />
</form>
```

The `\Eco\Form::$default_value_filter` static property is auto set to:
```php
function($v) { return html_entity_decode($v, ENT_QUOTES); };
```
This allows quotes to be displayed as quotes in form fields instead of encoded (sanitized) quotes. This can be disabled by setting the `\Eco\Form::$default_value_filter` property to `false`, or a function.

### Form Fields
The `\Eco\Form` class uses the following methods for adding fields:

- `checkbox()` - checkbox input, example: `$form->checkbox('field_name', [1 => 'Option 1', 2 => 'Option 2'], 'default checked (optional)')`
- `hidden()` - hidden input, example: `$form->hidden('field_name', 'default value (optional)')`
- `password()` - password input, example: `$form->password('field_name', 'default value (optional)')`
- `radio()` - radio button, example: `$form->radio('field_name', [1 => 'Option 1', 2 => 'Option 2'], 'default checked (optional)')`
- `select()` - select list, example: `$form->select('field_name', [1 => 'Option 1', 2 => 'Option 2'], 'default selected (optional)')`
- `text()` - text input, example: `$form->text('field_name', 'default value (optional)')`
- `textarea()` - multi-line text input, example: `$form->textarea('field_name', 'default value (optional)')`

### Form Field Attributes
Form field attributes are added in the view template file, for example:
```html+php
<?=$form->get('username', ['class' => 'textclass', 'maxlength' => 30])?>
```
Will output the HTML:
```html
<input type="text" name="username" class="textclass" maxlength="30">
```

Global default field attributes can be used for form fields. These attributes are used when no other attributes have been set. The global attributes are:

- `\Eco\Form::$attributes_checkbox_radio` - for checkbox and radio button fields
- `\Eco\Form::$attributes_field` - for email, password and text input fields
- `\Eco\Form::$attributes_fields` - for all form fields
- `\Eco\Form::$attributes_select` - for select lists
- `\Eco\Form::$attributes_textarea` - for textarea fields

Global attribute example:
```php
// default field class
\Eco\Form::$attributes_field = ['class' => 'form-control'];
```

In HTML:
```html+php
<?=$form->get('username', ['maxlength' => 30])?>
```
Now the field as both attributes `class` and `maxlength`. However, a get method attribute will override a global attribute, for example:
```php
// set global attribute
\Eco\Form::$attributes_field = ['class' => 'my-class'];
```
In HTML:
```html+php
<?=$form->get('username', ['class' => 'custom-class', 'maxlength' => 30])?>
```
Now the field as the class attribute set to `custom-class`. If a global attribute is unwanted in a get method set the attribute value to `null`, for example:
```html+php
<?=$form->get('username', ['class' => null, 'maxlength' => 30])?>
```
The field now has no `class` attribute even if the global attribute `class` is set for the field type.

### Form Field Decorators
Global decorators can be used for form fields. The global decorators are:

- `\Eco\Form::$decorator_checkbox_radio` - for checkbox and radio button fields
- `\Eco\Form::$decorator_default_validation_message` - default validation error message when error message not used
- `\Eco\Form::$decorator_error` - for single error message
- `\Eco\Form::$decorator_errors` - for multiple error messages
- `\Eco\Form::$decorator_field` - for email, password and text input fields
- `\Eco\Form::$decorator_fields` - for all form fields
- `\Eco\Form::$decorator_options` - for checkbox and radio button options
- `\Eco\Form::$decorator_select` - for select lists
- `\Eco\Form::$decorator_textarea` - for textarea fields

A global decorator can be set before setting the form object like:
```php
// set global decorator for text (and password) fields
\Eco\Form::$decorator_field = '<div class="field">{$field}</div>';

// set
$this->form
	// add text field
	->text('username');
```
> A decorator can use the pattern like `{$string}<br />`, or simply `{$}<br />`, or if no `{$...}<br />` pattern is found the decorator is added to the end of the string like `[string]<br />`.

Now in the view template:
```html+php
<?=$form->get('username')?>
```
Will output the HTML:
```html
<div class="field"><input type="text" name="username"></div>
```
> Global decorators can be disabled for any field using:
```php
// false disables global decorator for this field only
<?=$form->get('username', null, false)?>
```

### Form Validator Methods
Form field validation methods can be used to validate form data, for example:
```php
$this->form
	// add text field
	->text('username')
		->validateRequired('Username is required')
		->validateLength(4, 30, 'Username must be between 4-30 characters');
```
Will apply the required and length validation rules.
> If no error message is used, for example:
```php
$this->form
	// add text field
	->text('username')
		->validateRequired();
```
The default validation error message decorator will be used, and by default the value is:
```php
\Eco\Form::$decorator_default_validation_message = 'Enter valid value for field \'{$field}\'';
```

Field validation error message can also be set *after* setting the validation rule, for example:
```php
$this->form
	// add text field
	->text('username')
		->validateRequired();
// more code
//  and logic here

// now set validation message for required rule
$this->form->field('username')
	->validateRequiredMessage('Username is required');
```

The form validation methods are:

- `validateEmail()` - validate email address
- `validateLength()` - validate value length
- `validateMatch()` - validate field x with field y value
- `validateRegex()` - validate value using regex pattern
- `validateRequired()` - validate value is required

Custom validation rules can also be used, for example:
```php
$this->form
	// add text field
	->text('username')
		->validate(function($v) { return $v !== 'some value'; },
			'Username field value does not equal \'some value\'');
```
This custom validation rule will flag the field value invalid if the value does not equal `some value`.

Forced errors can be used, for example:
```php
$this->form
	->text('username');

// do some logic to check valid login
if(!$valid_login)
{
	$this->form->field('username')->forceError('Invalid username and/or password');
}
```
Now if the login is invalid the error will be displayed to the user in the view template file.
> The method `field()` is used to make the form object refocus on a specific field

### Form Field Errors
Form field validation errors (and *forced* errors) can be displayed using two methods:

- `getError()`
- `getErrors()`

The `getError()` method is used to fetch the first field error message, for example in the controller file:
```php
$this->form
	// add text field
	->text('username')
		->validateRequired('Username is required')
		->validateLength(4, 30, 'Username must be between 4-30 characters');
```
Then in the view template file:
```html+php
<?php echo $form->getError('username'); // display first field error ?>
<label>Username:</label>
<?php echo $form->get('username'); // display username field ?><br />
```
Now if the form is submitted with no value for field `username` the error displayed will be `Username is required`.

The `getErrors` method will display all field errors, for example in the view template file:
```html+php
<?php echo $form->getErrors('username', '<br />'); // display all field errors ?>
<label>Username:</label>
<?php echo $form->get('username'); // display username field ?><br />
```
Now if the form is submitted with no value for field `username` the HTML displayed will be `Username is required<br />Username must be between 4-30 characters<br />`.
> The `<br />` string used as the second param is the decorator.

All form field errors can be fetched using `null` in place of the field name:
```html+php
<?php echo $form->getErrors(null, '<br />'); // display all form field errors ?>
```

### Accessing Form Data
Form field data is accessed using the `getData()` method:
```php
$this->form
	->text('username')
	->text('first_name')
	->password('pwd');

if($this->form->isSubmitted())
{
	$username = $this->form->getData('username');
	$fname = $this->form->getData('first_name');
	$password = $this->form->getData('password');
}
```
Or the shorthand version can be used:
```php
if($this->form->isSubmitted())
{
	$username = $this->form->username;
	$fname = $this->form->first_name;
	$password = $this->form->password;
}
```
Or the data can be returned as an object:
```php
	// stdClass Object(['username' => x, 'first_name' => y, 'pwd' => z])
	$data = $this->form->getData();
```
Or the data can be returned as array:
```php
	$data = $this->form->getData(null, false); // ['username' => x, 'first_name' => y, 'pwd' => z]
```
Or the data can be returned for specific fields:
```php
	// stdClass Object(['username' => x, 'pwd' => y])
	$data = $this->form->getData(['username', 'pwd']);
```
Or the data for fields can be mapped to different keys:
```php
	$data = $this->form->getData([
		'username' => 'uid',
		'pwd' => 'u_pwd'
	]); // stdClass Object(['uid' => x, 'u_pwd' => y])
```
The `hasData()` method can be used to test if field value exists, for example:
```php
	if($this->form->hasData('username')) ...
```
Or the shorthand version:
```php
	if($this->form->username !== false) ...
```
> Form field data can be set using the `setValue($id, $value)` method

### CSRF Tokens
CSRF tokens are used in forms when enabled in configuration settings. There is also a configuration setting to use single-use tokens. If a form token fails, the `isSubmitted()` method (and `isValid()` method) will return `false`; other than that the form submission will fail silently. You can use a token fail callback to warn the end-user, example:
```php
// set global callback in bootstrap
\Eco\Form::$token_fail_callback = function(\Eco\Form $form) {
	// $form_id = $form->getId(); // access form object example
	// warn user
	eco::flash()->set('error', 'CSRF token error, please refresh the page and try again');
};
```