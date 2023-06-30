<?php

namespace LTDBeget\Yiiic\Tests;

use LTDBeget\Yiiic\Dot;
use PHPUnit_Framework_TestCase;

class DotTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var ReflectionClass
     */
    protected static $inst;

    /**
     * @var Dot
     */
    protected $dot;

    /**
     * @var array
     */
    protected $data;

    public static function setUpBeforeClass()
    {
        self::$inst = new ReflectionClass('\LTDBeget\Yiiic\Dot');
    }

    protected function setUp()
    {
        $this->data = [
            'users' => [
                'admin' => [
                    'user_1',
                    'user_2',
                    'root' => [
                        'root_1',
                        'root_2'
                    ]
                ]
            ],
            [
                ['Sergey', 'Alexander'],
                ['Natalya', 'Katerina']
            ]
        ];

        $this->dot = new Dot($this->data);
    }

    public function testGetAssocKey()
    {
        $actual = $this->dot['users.admin.root'];
        $this->assertEquals($this->data['users']['admin']['root'], $actual);
    }

    public function testGetIndexKey()
    {
        $actual = $this->dot['0.1.1'];
        $this->assertEquals($this->data[0][1][1], $actual);
    }

    public function testGetAssocIndexKey()
    {
        $actual = $this->dot['users.admin.1'];
        $this->assertEquals($this->data['users']['admin'][1], $actual);
    }

    public function testNotExists()
    {
        $actual = $this->dot['users.some_not_exists'];
        $this->assertEquals(NULL, $actual);
    }

    public function testSetAssocKey()
    {
        $expected                           = $this->data;
        $this->dot['users.admin.root']      = 'root_upd';
        $expected['users']['admin']['root'] = 'root_upd';
        $this->assertEqualsDot($expected);
    }

    public function testSetIndexKey()
    {
        $expected           = $this->data;
        $this->dot['0.1.1'] = 'Katerina_upd';
        $expected[0][1][1]  = 'Katerina_upd';
        $this->assertEqualsDot($expected);
    }

    public function testSetAssocIndexKey()
    {
        $expected                              = $this->data;
        $this->dot['users.admin.root.1']       = 'root_upd';
        $expected['users']['admin']['root'][1] = 'root_upd';
        $this->assertEqualsDot($expected);
    }

    public function testSetIfKeyNotExists()
    {
        $expected                                  = $this->data;
        $this->dot['users.moderator.male.2']       = 'moder';
        $expected['users']['moderator']            = [];
        $expected['users']['moderator']['male']    = [];
        $expected['users']['moderator']['male'][2] = 'moder';
        $this->assertEqualsDot($expected);
    }

    public function testSetIfKeyNotExistsForEmpty()
    {
        $expected                       = $actual = [];
        $dot                            = new Dot($actual);
        $dot['users.moderator']         = 'moder';
        $expected['users']              = [];
        $expected['users']['moderator'] = 'moder';
        $this->assertEqualsDot($expected, $dot);
    }

    public function testRemoveAssocKey()
    {
        $expected = $this->data;
        unset($this->dot['users.admin.root']);
        unset($expected['users']['admin']['root']);
        $this->assertEqualsDot($expected);
    }

    public function testRemoveIndexKey()
    {
        $expected = $this->data;
        unset($this->dot['0.1.1']);
        unset($expected[0][1][1]);
        $this->assertEqualsDot($expected);
    }

    public function testRemoveAssocIndexKey()
    {
        $expected = $this->data;
        unset($this->dot['users.admin.root.1']);
        unset($expected['users']['admin']['root'][1]);
        $this->assertEqualsDot($expected);
    }

    public function testRemoveNotExistsKey()
    {
        $expected = $this->data;
        unset($this->dot['users.some_not_exists']);
        unset($expected['users']['some_not_exists']);
        $this->assertEqualsDot($expected);
    }

    /**
     * @param $array
     * @param $key
     * @param $expected
     *
     * @dataProvider keyExistsProvider
     */
    public function testKeyExists($array, $key, $expected)
    {
        $method = self::$inst->getMethod('keyExists');
        $method->setAccessible(true);
        $actual = $method->invoke(self::$inst->newInstanceWithoutConstructor(), $key, $array);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @param $offset
     * @param $expected
     *
     * @dataProvider parseOffsetProvider
     */
    public function testParseOffset($offset, $expected)
    {
        $method = self::$inst->getMethod('parseOffset');
        $method->setAccessible(true);
        $actual = $method->invoke(NULL, $offset);
        $this->assertEquals($expected, $actual);
    }

    public function keyExistsProvider()
    {
        return [
            'scalar'                => ['scalar', 'node1', false],
            'array_with_exists'     => [['package' => 'Smarrt'], 'package', true],
            'array_with_not_exists' => [['package' => 'Smarrt'], 'version', false]
        ];
    }

    public function parseOffsetProvider()
    {
        return [
            'empty'      => ['', []],
            'one'        => ['users', ['users']],
            'few'        => ['users.admin.root', ['users', 'admin', 'root']],
            'with_index' => ['users.0.admin.0.1', ['users', '0', 'admin', '0', '1']]
        ];
    }

    protected function assertEqualsDot($expected, $dot = NULL)
    {
        if ($dot === NULL) {
            $dot = $this->dot;
        }

        $this->assertEquals($expected, $this->getDotValue($dot));
    }

    protected function getDotValue(Dot $dot)
    {
        $prop = self::$inst->getProperty('data');
        $prop->setAccessible(true);

        return $prop->getValue($dot);
    }

}