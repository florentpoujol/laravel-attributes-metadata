<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresetsTests;

use FlorentPoujol\LaravelAttributePresets\Definitions\Validation;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;
use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
    public function test_validation(): void
    {
        $arrayDefs = [
            'required',
            'min:5',
            'max' => 5,
            'not_in' => 'one,two',
            'unique' => ['the_table', 'the_field'],
            new Rule(),
            In::class => new In(['one', 'two']),
            'message' => 'the validation message',
        ];
        $defs = new Validation($arrayDefs);

        $this->assertSame('the validation message', $defs->getMessage());

        $expectedRules = [
            'required',
            'min:5',
            'max:5',
            'not_in:one,two',
            'unique:the_table,the_field',
            $arrayDefs[2],
            $arrayDefs[In::class],
        ];
        $this->assertSame($expectedRules, $defs->getRules());
    }

    public function test_fluent_validation(): void
    {
        $message = 'the validation message';
        $arrayDefs = [
            'required',
            'min:5',
            'max' => 5,
            'not_in' => 'one,two',
            'unique' => ['the_table', 'the_field'],
            'unique2' => ['the_table', 'the_field'],
            new Rule(),
            In::class => new In(['one', 'two']),
            'message' => $message,
        ];
        $defs = new Validation($arrayDefs);

        /** @noinspection PhpUndefinedMethodInspection */
        // @phpstan-ignore-next-line
        $fluentDefs = (new Validation())
            ->required()
            ->min(5)
            ->max(5)
            ->not_in('one,two')
            ->unique(['the_table', 'the_field'])
            ->unique2('the_table', 'the_field')
            ->add($arrayDefs[2])
            ->set(In::class, $arrayDefs[In::class])
            ->message($message);

        $this->assertSame($message, $defs->getMessage());
        $this->assertSame($defs->getMessage(), $fluentDefs->getMessage());

        $this->assertSame($defs->getRules(), $fluentDefs->getRules());
    }
}
