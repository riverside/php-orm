<?php
declare(strict_types=1);

namespace Riverside\Orm\Tests;

use PHPUnit\Framework\TestCase;
use Riverside\Orm\DB;
use Riverside\Orm\Expression;

class DBTest extends TestCase
{
    /**
     * @param DB $db
     * @param string $name
     * @return \ReflectionProperty
     * @throws \ReflectionException
     */
    protected static function getProperty(DB $db, string $name): \ReflectionProperty
    {
        $property = new \ReflectionProperty($db, $name);
        $property->setAccessible(true);

        return $property;
    }

    public function testAttributes()
    {
        $attributes = array(
            'attributes',
            'connection',
            'data',
            'dbh',
            'debug',
            'groupBy',
            'having',
            'join',
            'offset',
            'orderBy',
            'params',
            'pool',
            'rowCount',
            'select',
            'sth',
            'table',
            'where',
        );
        foreach ($attributes as $attribute) {
            $this->assertClassHasAttribute($attribute, DB::class);
        }
    }

    /**
     * @throws \Riverside\Orm\Exception
     */
    public function testConstruct()
    {
        $db = new DB();
        $this->assertInstanceOf(DB::class, $db);
    }

    public function testRaw()
    {
        $this->assertInstanceOf(Expression::class, DB::raw('test'));
    }

    /**
     * @throws \ReflectionException
     * @throws \Riverside\Orm\Exception
     */
    public function testGroupBy()
    {
        $db = new DB();
        $property = self::getProperty($db, 'groupBy');

        $this->assertEquals(null, $property->getValue($db));

        $db->groupBy('id');
        $this->assertEquals('id', $property->getValue($db));

        $property->setValue($db, 'email');
        $this->assertEquals('email', $property->getValue($db));
    }

    /**
     * @throws \ReflectionException
     * @throws \Riverside\Orm\Exception
     */
    public function testHaving()
    {
        $db = new DB();
        $property = self::getProperty($db, 'having');

        $this->assertEquals(null, $property->getValue($db));

        $db->having('id > 0');
        $this->assertEquals('id > 0', $property->getValue($db));

        $property->setValue($db, 'email IS NOT NULL');
        $this->assertEquals('email IS NOT NULL', $property->getValue($db));
    }

    /**
     * @throws \ReflectionException
     * @throws \Riverside\Orm\Exception
     */
    public function testOffset()
    {
        $db = new DB();
        $property = self::getProperty($db, 'offset');

        $this->assertEquals(null, $property->getValue($db));

        $db->limit(10, 10);
        $this->assertEquals(10, $property->getValue($db));

        $property->setValue($db, 20);
        $this->assertEquals(20, $property->getValue($db));
    }

    /**
     * @throws \ReflectionException
     * @throws \Riverside\Orm\Exception
     */
    public function testRowCount()
    {
        $db = new DB();
        $property = self::getProperty($db, 'rowCount');

        $this->assertEquals(null, $property->getValue($db));

        $db->limit(10, 10);
        $this->assertEquals(10, $property->getValue($db));

        $property->setValue($db, 20);
        $this->assertEquals(20, $property->getValue($db));
    }

    /**
     * @throws \ReflectionException
     * @throws \Riverside\Orm\Exception
     */
    public function testOrderBy()
    {
        $db = new DB();
        $property = self::getProperty($db, 'orderBy');

        $this->assertEquals(null, $property->getValue($db));

        $db->orderBy('id ASC');
        $this->assertEquals('id ASC', $property->getValue($db));

        $property->setValue($db, 'id DESC');
        $this->assertEquals('id DESC', $property->getValue($db));
    }

    /**
     * @throws \ReflectionException
     * @throws \Riverside\Orm\Exception
     */
    public function testSelect()
    {
        $db = new DB();
        $property = self::getProperty($db, 'select');

        $this->assertEquals(null, $property->getValue($db));

        $db->select('id');
        $this->assertEquals('id', $property->getValue($db));

        $property->setValue($db, 'email');
        $this->assertEquals('email', $property->getValue($db));
    }

    /**
     * @throws \ReflectionException
     * @throws \Riverside\Orm\Exception
     */
    public function testTable()
    {
        $db = new DB();
        $property = self::getProperty($db, 'table');

        $this->assertEquals(null, $property->getValue($db));

        $db->table('users');
        $this->assertEquals('users', $property->getValue($db));

        $property->setValue($db, 'clients');
        $this->assertEquals('clients', $property->getValue($db));
    }

    /**
     * @throws \ReflectionException
     * @throws \Riverside\Orm\Exception
     */
    public function testJoin()
    {
        $db = new DB();
        $property = self::getProperty($db, 'join');

        $this->assertIsArray($property->getValue($db));
        $this->assertEquals([], $property->getValue($db));

        $table = 'users';
        $conditions = 'users.id=salaries.user_id';
        $join = [
            'type' => 'INNER',
            'table' => $table,
            'conditions' => $conditions
        ];
        $expected = [$join];

        $db->join($table, $conditions);
        $this->assertEquals($expected, $property->getValue($db));

        $join['type'] = 'LEFT';
        $expected[] = $join;
        $db->leftJoin($table, $conditions);
        $this->assertEquals($expected, $property->getValue($db));

        $join['type'] = 'RIGHT';
        $expected[] = $join;
        $db->rightJoin($table, $conditions);
        $this->assertEquals($expected, $property->getValue($db));
    }

    /**
     * @throws \ReflectionException
     * @throws \Riverside\Orm\Exception
     */
    public function testWhere()
    {
        $db = new DB();
        $property = self::getProperty($db, 'where');

        $this->assertIsArray($property->getValue($db));
        $this->assertEquals([], $property->getValue($db));

        $expected = [[
            'column' => 'id',
            'operator' => '=',
            'value' => 1
        ]];

        $db->where('id', 1);
        $this->assertEquals($expected, $property->getValue($db));

        $property->setValue($db, $expected);
        $this->assertEquals($expected, $property->getValue($db));
    }

    /**
     * @throws \ReflectionException
     * @throws \Riverside\Orm\Exception
     */
    public function testDebug()
    {
        $db = new DB();
        $property = self::getProperty($db, 'debug');

        $this->assertFalse($property->getValue($db));

        $db->debug(true);
        $this->assertTrue($property->getValue($db));

        $property->setValue($db, false);
        $this->assertFalse($property->getValue($db));
    }

    /**
     * @throws \ReflectionException
     * @throws \Riverside\Orm\Exception
     */
    public function testReset()
    {
        $db = new DB();
        $db->debug(true);

        $property = self::getProperty($db, 'debug');
        $this->assertTrue($property->getValue($db));

        $this->assertInstanceOf(DB::class, $db->reset());

        $property = self::getProperty($db, 'debug');
        $this->assertFalse($property->getValue($db));
    }

    /**
     * @throws \ReflectionException
     * @throws \Riverside\Orm\Exception
     */
    public function testParam()
    {
        $db = new DB();
        $property = self::getProperty($db, 'params');

        $this->assertIsArray($property->getValue($db));
        $this->assertSame([], $property->getValue($db));

        $db->param('foo', 'bar');
        $this->assertEquals(['foo' => 'bar'], $property->getValue($db));

        $property->setValue($db, ['boo' => 'woo']);
        $this->assertEquals(['boo' => 'woo'], $property->getValue($db));
    }
}
