<?php


namespace LTDBeget\Yiiic\Tests;


use LTDBeget\Yiiic\InputResolver;

class InputResolverTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \ReflectionClass
     */
    protected static $class;

    /**
     * @var InputResolver
     */
    protected static $object;

    public static function setUpBeforeClass()
    {
        self::$class = new \ReflectionClass(InputResolver::class);
        self::$object = self::$class->newInstance(['c', 'q', 'h'], '/');
    }


    public function testGetRegex()
    {
        $method = self::$class->getMethod('getRegex');
        $method->setAccessible(true);
        $result = $method->invoke(self::$object);

        $this->assertEquals('/^(\/?(c|q|h))$/', $result);
    }

    /**
     * @dataProvider explodeProvider
     *
     * @param string $input
     * @param array $expected
     */
    public function testExplode(string $input, array $expected)
    {
        $method = self::$class->getMethod('explode');
        $method->setAccessible(true);
        $result = $method->invoke(self::$object, $input);

        $this->assertEquals($expected, $result);
    }

    public function explodeProvider()
    {
        return [
            'default' => ['comment add "some text" --author "Danila Stivrinsh"', ["comment", "add", "some text", "--author", "Danila Stivrinsh"]]
        ];
    }

}