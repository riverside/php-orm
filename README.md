# php-orm
PHP micro-ORM and query builder.

| Build | GitHub pages | Stable | License |
| ----- | ------------ | ------ | ------- |
| [![CI][x1]][y1] | [![pages-build-deployment][x4]][y4] | [![Latest Stable Version][x2]][y2] | [![License][x3]][y3] |

### Requirements
- PHP >= 7.1
- PHP extensions:
  - PDO (`ext-pdo`)

### Installation
If Composer is not installed on your system yet, you may go ahead and install it using this command line:
```
$ curl -sS https://getcomposer.org/installer | php
```
Next, add the following require entry to the <code>composer.json</code> file in the root of your project.
```json
{
    "require" : {
        "riverside/php-orm" : "^2.0"
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

Configure database credentials by setting up the following environment variables: 
`USERNAME`, `PASSWORD`, `DATABASE`, `HOST`, `PORT`, `DRIVER`, `CHARSET`, `COLLATION`. They must be 
prefixed with `$connection` property value, part of your model, in capital letters and underscore. The default value of `$connection` 
property is 'default'. For example:
```php
<?php
putenv("DEFAULT_USERNAME=your_username");
putenv("DEFAULT_PASSWORD=your_password");
putenv("DEFAULT_DATABASE=your_database");
putenv("DEFAULT_HOST=localhost");
putenv("DEFAULT_PORT=3306");
putenv("DEFAULT_DRIVER=mysql");
putenv("DEFAULT_CHARSET=utf8mb4");
putenv("DEFAULT_COLLATION=utf8mb4_general_ci");
```
### Drivers
The following drivers are supported: `mysql`, `oci`, `firebird`, `pgsql`, `sqlite`, `sybase`, `mssql`, `dblib`, `cubrid`, `4D`.

### Database
Table: **users**

| Name | Type | Collation | Attributes | Null| Extra |
| --- | --- | --- | --- | --- | --- |
| id | int(10) | | UNSIGNED | No | AUTO_INCREMENT |
| name | varchar(255) | utf8mb4_general_ci | | Yes | |
| email | varchar(255) | utf8mb4_general_ci | | Yes | |

### Models
Define your own models:
```php
<?php
use Riverside\Orm\DB;

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
 The following is example of simple CRUD:
```php
$id = User::factory()->insert(['name' => 'Chris', 'email' => 'chris@aol.com']);
$user = User::factory()->where('id', $id)->get();
$affected_rows = User::factory()->where('id', $id)->update(['name' => 'Chris Johnson']);
$affected_rows = User::factory()->where('id', $id)->delete();
```

- without model: create a new instance of `Riverside\Orm\DB` class
```php
$db = new \Riverside\Orm\DB();
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