# php-orm
 PHP micro-ORM library

### Requirements
- PHP >= 7.1
- PDO extension

### Installation
If Composer is not installed on your system yet, you may go ahead and install it using this command line:
```
$ curl -sS https://getcomposer.org/installer | php
```
Next, add the following require entry to the <code>composer.json</code> file in the root of your project.
```json
{
    "require" : {
        "riverside/php-orm" : "*"
    }
}
```
Finally, use Composer to install php-orm and its dependencies:
```
$ php composer.phar install 
```

### Configuration
php-orm expects the following environment variables at local level:
```php
<?php
putenv('PHP_ORM_DSN=mysql:host=localhost;dbname=mydb');
putenv('PHP_ORM_USER=myuser');
putenv('PHP_ORM_PSWD=mypswd');
```
Include autoload in your project: 
```php
<?php
include __DIR__ . '/vendor/autoload.php';
```

### Models
Define your own models:
```php
<?php
use PhpOrm\DB;

class User extends DB
{
    protected $table = 'users';
    
    public static function factory()
    {
        return new self();
    }
}
```

### Query using a model
First create an instance of a model using the `factory` method. Then chain multiple methods.
```php
User::factory()->get();
```

### Query without a model
If you prefer to use it directly:
```php
$db = new DB();
$db->table('users')->get();
```
