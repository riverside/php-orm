# php-orm
PHP micro-ORM and query builder.

| Build | Stable | License |
| ----- | ------ | ------- |
| [![Build Status][x1]][y1] | [![Latest Stable Version][x2]][y2] | [![License][x3]][y3] |

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
$_ENV['PHP_ORM_DSN']  = 'mysql:host=localhost;dbname=mydb';
$_ENV['PHP_ORM_USER'] = 'myuser';
$_ENV['PHP_ORM_PSWD'] = 'mypswd';
```
Include autoload in your project: 
```php
<?php
require __DIR__ . '/vendor/autoload.php';
```

### Models
Define your own models:
```php
<?php
use PhpOrm\DB;

class User extends DB
{
    protected $table = 'users';
    
    protected $attributes = ['id', 'name', 'email'];
    
    public static function factory()
    {
        return new self();
    }
}
```

### Query Builder
- with model:
 create an instance of a model using the `factory` method. Then chain multiple methods.
```php
User::factory()->get();
```

- without model: create a new instance of `PhpOrm\DB` class
```php
$db = new \PhpOrm\DB();
$db->table('users')->get();
```

### API
- [DB][1]
- [Expression][2]

[1]: https://riverside.github.io/php-orm/api.html#db
[2]: https://riverside.github.io/php-orm/api.html#expr
[x1]: https://api.travis-ci.org/riverside/php-orm.svg
[y1]: https://travis-ci.org/riverside/php-orm
[x2]: https://poser.pugx.org/riverside/php-orm/v/stable
[y2]: https://packagist.org/packages/riverside/php-orm
[x3]: https://poser.pugx.org/riverside/php-orm/license
[y3]: https://packagist.org/packages/riverside/php-orm