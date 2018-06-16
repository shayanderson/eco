# Using Services
Services can be used with classes to simplify accessibility.

### Basic Usage
First, classes used as services need to be "registered" in the *service registry* in the `app/com/service.php` file. Each class is added in the comment block above the `EcoServiceRegistry` class using the `@property <class> <property>` syntax, for example:
```php
/**
 * @property App\Service\Session $session
 * @property App\Service\User $user
 */
class EcoServiceRegistry extends \Eco\System\Registry\Service {}
```
Now each of these registered classes can be accessed using the `eco::service()` method or the helper function `service()` (used in examples below).
```php
// use session service (auto instantiation)
$session_id = service()->session->getId();

// service classes can also be manually instantiated
service()->user = new \App\Service\User($user_id);
service()->user->load();
$name = service()->user->getName();
```
