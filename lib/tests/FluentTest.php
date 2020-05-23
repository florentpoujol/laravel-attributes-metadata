<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresetsTests;

use FlorentPoujol\LaravelAttributePresets\Definitions\Fluent;
use PHPUnit\Framework\TestCase;

class FluentTest extends TestCase
{
    public function test_fluent_arguments(): void
    {
        $fluent = new Fluent();
        $this->assertEmpty($fluent->toArray());

        $fluent->fill([
            'noArg',
            'oneArg' => 'value1',
            'oneArgAsArray' => ['value1'],
            'twoArgs' => ['value1', 2],
            'twoArgs2' => [['value1', 'value2' => 2], 'value3'],
            'oneArrayArg' => [['value1']],
            'oneArrayArg2' => [['value1', 2]],
        ]);

        $fluent2 = (new Fluent())
            ->noArg()
            ->oneArg('value1')
            ->oneArgAsArray('value1')
            ->twoArgs('value1', 2)
            ->twoArgs2(['value1', 'value2' => 2], 'value3')
            ->oneArrayArg(['value1'])
            ->oneArrayArg2(['value1', 2])
        ;

        $expectedStorage = [
            'noArg' => null,
            'oneArg' => 'value1',
            'oneArgAsArray' => 'value1',
            'twoArgs' => ['value1', 2],
            'twoArgs2' => [['value1', 'value2' => 2], 'value3'],
            'oneArrayArg' => [['value1']],
            'oneArrayArg2' => [['value1', 2]],
        ];

        $this->assertNotSame($fluent, $fluent2);
        $this->assertSame($fluent->toArray(), $fluent2->toArray());

        $this->assertSame($expectedStorage, $fluent->toArray());
        $this->assertSame($expectedStorage, $fluent2->toArray());

        $fluent3 = new Fluent();
        $fluent->applyTo($fluent3);
        $this->assertSame($fluent3->toArray(), $fluent->toArray());
        $this->assertSame($expectedStorage, $fluent3->toArray());

        $fluent4 = new Fluent();
        $fluent2->applyTo($fluent4);
        $this->assertSame($fluent4->toArray(), $fluent2->toArray());
        $this->assertSame($expectedStorage, $fluent4->toArray());
    }

    /** @noinspection PhpUndefinedMethodInspection */
    public function test_magic_call(): void
    {
        $fluent = new Fluent();
        $fluent
            ->key1()
            ->key2(2)
            ->key3(false)
            ->key4('array1', 'array2')
            ->key5(['array1', 'array2']);

        $this->assertSame(null, $fluent->getKey1());
        $this->assertSame(2, $fluent->getKey2());
        $this->assertSame(false, $fluent->getKey3());
        $this->assertSame(['array1', 'array2'], $fluent->getKey4());
        $this->assertSame([['array1', 'array2']], $fluent->getKey5());
        $this->assertSame(null, $fluent->getNonExistentKey());

        $this->assertTrue($fluent->hasKey1());
        $this->assertTrue($fluent->hasKey2());
        $this->assertTrue($fluent->hasKey3());
        $this->assertTrue($fluent->hasKey4());
        $this->assertTrue($fluent->hasKey5());
        $this->assertFalse($fluent->hasNonExistentKey());

        $this->assertTrue($fluent->isKey1());
        $this->assertTrue($fluent->isKey2());
        $this->assertFalse($fluent->isKey3());
        $this->assertTrue($fluent->isKey4());
        $this->assertTrue($fluent->isKey5());
        $this->assertFalse($fluent->isNonExistentKey());
    }

    public function test_tap_with_closure(): void
    {
        $fluent = new Fluent();
        $this->assertEmpty($fluent->toArray());

        $fluent->tap(function ($fluent2) use ($fluent) {
            $this->assertInstanceOf(Fluent::class, $fluent2);
            $this->assertSame($fluent, $fluent2);

            $fluent2->fill(['key1']);
        });

        $this->assertSame(['key1' => null], $fluent->toArray());
    }

    public function test_tap_with_callable(): void
    {
        $fluent = new Fluent();
        $this->assertEmpty($fluent->toArray());

        $fluent->tap([$this, 'tapCallable']);

        $this->assertSame(['key1' => null], $fluent->toArray());
    }

    public function tapCallable(Fluent $fluent2): void
    {
        $fluent2->fill(['key1']);
    }

    public function test_set(): void
    {
        $fluent = new Fluent();
        $this->assertEmpty($fluent->toArray());

        $fluent->set('key1');
        $fluent->set('key2', 'value1');
        $fluent->set('key3', ['subkey1', 'subkey2' => 2]);

        $expected = [
            'key1' => null,
            'key2' => 'value1',
            'key3' => [
                'subkey1',
                'subkey2' => 2,
            ],
        ];
        $this->assertSame($expected, $fluent->toArray());
    }

    public function test_add(): void
    {
        $fluent = new Fluent();
        $this->assertEmpty($fluent->toArray());

        $fluent->add('key1');
        $fluent->add('key2:value1');
        // @phpstan-ignore-next-line
        $fluent->add('key3', ['subkey1', 'subkey2' => 2]); // argument 2 ignored

        $expected = [
            'key1' => null,
            'key2' => 'value1',
            'key3' => null,
        ];
        $this->assertSame($expected, $fluent->toArray());
    }

    public function test_get_has_is_remove_clear(): void
    {
        $fluent = new Fluent([
            'key1',
            'key2' => 2,
            'key3' => false,
            'key4' => true,
            'key5' => [],
        ]);

        $this->assertSame(null, $fluent->get('key1'));
        $this->assertSame(2, $fluent->get('key2'));
        $this->assertSame(false, $fluent->get('key3', 'default'));
        $this->assertSame(true, $fluent->get('key4'));
        $this->assertSame(null, $fluent->get('key5'));
        $this->assertSame(null, $fluent->get('key5', []));
        $this->assertSame(null, $fluent->get('non-existent-key'));
        $this->assertSame('default', $fluent->get('non-existent-key', 'default'));

        $this->assertTrue($fluent->has('key1'));
        $this->assertTrue($fluent->has('key2'));
        $this->assertTrue($fluent->has('key3'));
        $this->assertTrue($fluent->has('key4'));
        $this->assertTrue($fluent->has('key5'));
        $this->assertFalse($fluent->has('non-existent-key'));

        $this->assertTrue($fluent->is('key1'));
        $this->assertTrue($fluent->is('key2'));
        $this->assertFalse($fluent->is('key3'));
        $this->assertTrue($fluent->is('key4'));
        $this->assertTrue($fluent->is('key5'));
        $this->assertFalse($fluent->is('non-existent-key'));

        $fluent
            ->remove('key1')
            ->remove('non-existent-key');

        $this->assertFalse($fluent->has('key1'));
        $this->assertTrue($fluent->has('key2'));
        $this->assertTrue($fluent->has('key3'));
        $this->assertTrue($fluent->has('key4'));
        $this->assertTrue($fluent->has('key5'));
        $this->assertFalse($fluent->has('non-existent-key'));

        $fluent->clear(['key2', 'key3', 'other-non-existent-key']);

        $this->assertFalse($fluent->has('key1'));
        $this->assertFalse($fluent->has('key2'));
        $this->assertFalse($fluent->has('key3'));
        $this->assertTrue($fluent->has('key4'));
        $this->assertTrue($fluent->has('key5'));
        $this->assertFalse($fluent->has('non-existent-key'));

        $fluent->clear();
        $this->assertEmpty($fluent->toArray());
    }

    public function test_offset_set(): void
    {
        $fluent = new Fluent();

        $fluent->fill(['min:5']);
        $fluent->add('whatever:text');
        $fluent->set('whatever:text,text2');

        $expected = [
            'min' => '5',
            'whatever' => 'text,text2',
        ];
        $this->assertSame($expected, $fluent->toArray());

        $fluent->fill(['-whatever', '-max']);
        $this->assertSame(['min' => '5'], $fluent->toArray());
    }
}
