<?php

namespace Prettus\Validator\Tests;

use Prettus\Validator\Exceptions\ValidatorException;
use Prettus\Validator\LaravelValidator;

class LaravelValidatorTest extends TestCase
{
    private function makeValidator(): LaravelValidator
    {
        return $this->app->make(LaravelValidator::class);
    }

    public function test_passes_returns_true_for_valid_data(): void
    {
        $v = $this->makeValidator()
            ->setRules(['name' => 'required'])
            ->with(['name' => 'Alice']);

        $this->assertTrue($v->passes());
    }

    public function test_passes_returns_false_and_populates_errors_when_invalid(): void
    {
        $v = $this->makeValidator()
            ->setRules(['name' => 'required'])
            ->with([]);

        $this->assertFalse($v->passes());
        $this->assertNotEmpty($v->errors());
        $this->assertNotEmpty($v->errorsBag()->all());
    }

    public function test_passes_or_fail_throws_validator_exception_on_invalid_data(): void
    {
        $v = $this->makeValidator()
            ->setRules(['email' => 'required|email'])
            ->with(['email' => 'not-an-email']);

        $this->expectException(ValidatorException::class);
        $v->passesOrFail();
    }

    public function test_passes_or_fail_returns_true_on_valid_data(): void
    {
        $v = $this->makeValidator()
            ->setRules(['email' => 'required|email'])
            ->with(['email' => 'a@b.test']);

        $this->assertTrue($v->passesOrFail());
    }

    public function test_action_scoped_rules_are_selected_by_action_key(): void
    {
        $v = $this->makeValidator()
            ->setRules([
                'create' => ['name' => 'required'],
                'update' => ['name' => 'required|min:3'],
            ])
            ->with(['name' => 'Al']);

        $this->assertTrue($v->passes('create'));
        $this->assertFalse($v->passes('update'));
    }

    public function test_set_id_rewrites_unique_rule_third_parameter(): void
    {
        $v = $this->makeValidator()
            ->setId(42)
            ->setRules(['email' => 'unique:users']);

        $rules = $v->getRules();
        $this->assertSame(['unique:users,email,42'], $rules['email']);
    }

    public function test_custom_messages_and_attributes_are_applied(): void
    {
        $v = $this->makeValidator()
            ->setRules(['name' => 'required'])
            ->setMessages(['required' => ':attribute MUST be present'])
            ->setAttributes(['name' => 'FullName'])
            ->with([]);

        $this->assertFalse($v->passes());
        $this->assertStringContainsString('FullName MUST be present', $v->errors()[0]);
    }
}
