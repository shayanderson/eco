## Redirect
The redirect method can redirect a request, for example:
```php
if($valid_login)
{
    eco::redirect('/user/account');
}
else
{
    // error message
}
```
The `\Eco\System::redirect()` method can be overwritten:
```php
class eco extends \Eco\System
{
    public function redirect([...])
    {
        // do something different
    }
}
```