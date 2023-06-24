# php-orm
PHP micro-ORM and query builder.

| Build | GitHub pages | Stable | License |
| ----- | ------------ | ------ | ------- |
| [![CI][x1]][y1] | [![pages-build-deployment][x4]][y4] | [![Latest Stable Version][x2]][y2] | [![License][x3]][y3] |

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
Include autoload in your project: 
```php
require __DIR__ . '/vendor/autoload.php';
```

Define path to configuration file:
```php
DB::config('config/database.php');
```

config/database.php
```php
<?php
return array(
    'default' => array(
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'port'      => 3306,
        'username'  => 'root',
        'password'  => 'secret',
        'database'  => 'test',
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_general_ci',
    ),
);
```

### Database
Table: users

| Name | Type | Collation | Attributes | Null| Extra |
| --- | --- | --- | --- | --- | --- |
| id | int(10) | | UNSIGNED | No | AUTO_INCREMENT |
| name | varchar(255) | utf8mb4_general_ci | | Yes | |
| email | varchar(255) | utf8mb4_general_ci | | Yes | |

### Models
Define your own models:
```php
<?php
use PhpOrm\DB;

class User extends DB
{
    protected $table = 'users';
    
    protected $attributes = ['id', 'name', 'email'];
    
    // protected $connection = 'backup';
    
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
- [Configuration][3]
- [Connection][4]

[1]: https://riverside.github.io/php-orm/api.html#db
[2]: https://riverside.github.io/php-orm/api.html#expr
[3]: https://riverside.github.io/php-orm/api.html#cfg
[4]: https://riverside.github.io/php-orm/api.html#con
[x1]: https://github.com/riverside/php-orm/actions/workflows/test.yml/badge.svg
[y1]: https://github.com/riverside/php-orm/actions/workflows/test.yml
[x2]: https://poser.pugx.org/riverside/php-orm/v/stable
[y2]: https://packagist.org/packages/riverside/php-orm
[x3]: https://poser.pugx.org/riverside/php-orm/license
[y3]: https://packagist.org/packages/riverside/php-orm
[x4]: https://github.com/riverside/php-orm/actions/workflows/pages/pages-build-deployment/badge.svg
[y4]: https://github.com/riverside/php-orm/actions/workflows/pages/pages-build-deployment