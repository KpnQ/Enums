<?php

namespace Jac\Tests\Enums;

use IncludedEnum;
use Jac\Enums\EnumJsonFormat;
use Jac\Enums\InvalidEnumException;
use PHPUnit\Framework\TestCase;

class EnumTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // Setup the cache
        EnumFixture::enum1();
        EnumFixture::enum2();
        EnumFixture::enumSecond();
        EnumFixtureDiff::enum1();
    }

    public function testInEmumWhenValidValueUsed()
    {
        $this->assertTrue(EnumFixture::inEnum('first'));
        $this->assertTrue(EnumFixture::inEnum('second'));
        $this->assertTrue(EnumFixture::inEnum(10));
    }

    public function testInEmumWhenInvalidValueUsed()
    {
        $this->assertFalse(EnumFixture::inEnum('fst'));
        $this->assertFalse(EnumFixture::inEnum('10'));
        $this->assertFalse(EnumFixture::inEnum(0));
        $this->assertFalse(EnumFixture::inEnum(null));
        $this->assertFalse(EnumFixture::inEnum(false));
        $this->assertFalse(EnumFixture::inEnum(true));
    }

    public function testInEmumWhenValidKeyUsed()
    {
        $this->assertTrue(EnumFixture::inEnum('ENUM_1'));
        $this->assertTrue(EnumFixture::inEnum('ENUM_2'));
        $this->assertTrue(EnumFixture::inEnum('ENUM_INT'));
    }

    public function testToArray()
    {
        $expected = array(
            'ENUM_1' => 'first',
            'ENUM_2' => 'second',
            'ENUM_SECOND' => 'second',
            'ENUM_INT' => 10,
        );
        $this->assertEquals(
            $expected,
            EnumFixture::toArray(),
            "Should containt constants keys"
        );
    }

    public function testEnumNotSame()
    {
        $this->assertEquals(
            EnumFixture::enum1()->getValue(),
            EnumFixtureDiff::enum1()->getValue(),
            "Value are the same"
        );
        $this->assertEquals(
            EnumFixture::enum1()->getKey(),
            EnumFixtureDiff::enum1()->getKey(),
            "Key are the same"
        );
        $this->assertNotEquals(
            EnumFixture::enum1(),
            EnumFixtureDiff::enum1(),
            "..but thet are not equals"
        );
    }
    public function testValidStaticCall()
    {
        $this->assertSame(
            EnumFixture::enum1(),
            EnumFixture::ENUM_1(),
            "Static call using const as function as the same result"
        );
    }

    public function testToString()
    {
        $this->expectOutputString('Jac\Tests\Enums\EnumFixture::ENUM_1::first');
        echo EnumFixture::enum1();
    }

    public function testInvalidStaticCall()
    {
        $call = 'INVALID';
        $this->expectException(InvalidEnumException::class);
        $this->expectExceptionMessage(
            "The constant '$call' doesn't exists in the enum " . EnumFixture::class
        );
        EnumFixture::{$call}();
    }

    public function testInvalidEnumCallValueError()
    {
        $call = 'ENUM_1';
        $value = 'invalid data';
        $this->expectException(InvalidEnumException::class);
        $this->expectExceptionMessage(
            "The couple '$call' '$value' doesn't exists in the enum " . EnumFixture::class
        );
        EnumFixture::enum($call, $value);
    }

    public function testInvalidEnumCallKeyError()
    {
        $call = 'INVALID';
        $value = 'first';
        $this->expectException(InvalidEnumException::class);
        $this->expectExceptionMessage(
            "The couple '$call' '$value' doesn't exists in the enum " . EnumFixture::class
        );
        EnumFixture::enum($call, $value);
    }

    public function testInvalidEnumCallKeyAndValueError()
    {
        $call = 'INVALID';
        $value = 'INVALID';
        $this->expectException(InvalidEnumException::class);
        $this->expectExceptionMessage(
            "The couple '$call' '$value' doesn't exists in the enum " . EnumFixture::class
        );
        EnumFixture::enum($call, $value);
    }

    public function testValidEnumCall()
    {
        $call = 'ENUM_1';
        $value = 'first';
        $this->assertSame(
            EnumFixture::enum1(),
            EnumFixture::enum($call, $value)
        );
    }


    public function testClone()
    {
        $this->expectErrorMessage("Call to private Jac\Enums\AbstractEnum::__clone() from context 'Jac\Tests\Enums\EnumTest'");
        $enum = clone EnumFixture::enum1();
    }

    public function testConstruct()
    {
        $this->expectErrorMessage("Call to private Jac\Enums\AbstractEnum::__construct() from context 'Jac\Tests\Enums\EnumTest'");
        new EnumFixture('test', 'test');
    }

    public function testMagic()
    {
        $enum = EnumFixture::enum1();
        $enum->value = 'test';
        $this->assertEquals('first', $enum->value);
        $this->assertEmpty($enum->notExists);
    }

    public function testSetState()
    {
        $enum = EnumFixture::enum1();
        $export = var_export($enum, true);
        eval('$rebuilt = ' . $export . ';');
        $this->assertSame($enum, $rebuilt);
    }

    public function testSetStateError()
    {
        $this->expectException(InvalidEnumException::class);
        $this->expectExceptionMessage("Unable to set state from 'faked'");
        $enum = EnumFixture::enum1();
        $export = var_export($enum, true);
        eval('$rebuilt = ' . str_replace('ENUM_1', 'faked', $export) . ';');
    }

    public function testJson()
    {
        $this->assertJson(json_encode(EnumFixture::enum1()));
        $this->assertEquals('"first"', json_encode(EnumFixture::enum1()));
    }

    public function testJsonWithFormat()
    {
        $this->assertEquals(
            '"first"',
            json_encode(EnumJsonFormat::asString()->format(EnumFixture::enum1()))
        );

        $this->assertEquals(
            json_encode(array('ENUM_1' => 'first')),
            json_encode(EnumJsonFormat::asKeyValue()->format(EnumFixture::enum1()))
        );

        $this->assertEquals(
            json_encode(array(
                "Jac\Tests\Enums\EnumFixture" => array(
                    'key' => 'ENUM_1',
                    'value' => 'first'
                )
            )),
            json_encode(EnumJsonFormat::keyAndValueAsValues()->format(EnumFixture::enum1()))
        );

        $this->assertEquals(
            "null",
            json_encode(EnumJsonFormat::asString())
        );
    }

    public function testEquals()
    {
        $this->assertTrue(EnumFixture::enum1() == EnumFixture::enum1(), "Test simple");
        $this->assertTrue(EnumFixture::enum1() === EnumFixture::enum1(), "Test with type");
        $this->assertFalse(EnumFixture::enum1() === EnumFixtureDiff::enum1(), "Test typed equals, not same enum class");
        $this->assertFalse(EnumFixture::enum1() == EnumFixtureDiff::enum1(), "Test simple equals");
    }
}
