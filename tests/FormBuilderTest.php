<?php

namespace PaperScissorsAndGlue\FormBuilder\Tests;

use PHPUnit\Framework\TestCase;
use PaperScissorsAndGlue\FormBuilder\FormBuilder;

class FormBuilderTest extends TestCase
{
    private $formBuilder;

    protected function setUp(): void
    {
        $this->formBuilder = new FormBuilder();
    }

    public function testAddField()
    {
        $this->formBuilder->add('name', 'text', [
            'label' => 'Name',
            'attr' => ['class' => 'form-control']
        ]);

        $output = $this->formBuilder->render();
        $this->assertStringContainsString('name="name"', $output);
        $this->assertStringContainsString('type="text"', $output);
        $this->assertStringContainsString('class="form-control"', $output);
    }

    public function testPlaceholder()
    {
        $this->formBuilder->add('name', 'text', [
            'label' => 'Name',
            'attr' => ['class' => 'form-control'],
            'placeholder' => 'Enter your name'
        ]);

        $output = $this->formBuilder->render();
        $this->assertStringContainsString('placeholder="Enter your name"', $output);
    }

    public function testFormAttributes()
    {
        $this->formBuilder
            ->method('POST')
            ->url('/submit')
            ->class('my-form');

        $output = $this->formBuilder->render();
        $this->assertStringContainsString('<form method="POST"', $output);
        $this->assertStringContainsString('action="/submit"', $output);
        $this->assertStringContainsString('class="my-form"', $output);
    }

    public function testAttributes()
    {
        $this->formBuilder->add('name', 'text', [
            'label' => 'Name',
            'attr' => ['class' => 'form-control', 'debug' => true]
        ]);

        $output = $this->formBuilder->render();
        $this->assertStringContainsString('class="form-control" debug="1"', $output);
    }

    public function testCsrfToken()
    {
        $this->formBuilder->csrf('test_token');
        $output = $this->formBuilder->render();
        $this->assertStringContainsString('name="_token" value="test_token"', $output);
    }

    public function testEmbeddedForm()
    {
        // Create an embedded address form
        $addressForm = new FormBuilder();
        $addressForm->add('street', 'text', [
            'label' => 'Street',
            'attr' => ['class' => 'form-control'],
            'placeholder' => 'Enter street address'
        ])
            ->add('city', 'text', [
                'label' => 'City',
                'attr' => ['class' => 'form-control']
            ])
            ->add('country', 'select', [
                'label' => 'Country',
                'choices' => ['us' => 'United States', 'ca' => 'Canada'],
                'attr' => ['class' => 'form-control'],
                'empty_value' => 'Select a country'
            ]);

        // Add the embedded form to the main form
        $this->formBuilder->add('billing_address', 'form', [
            'form' => $addressForm,
            'formOptions' => ['class' => 'embedded-form'],
            'label' => 'Billing Address',
            'wrapper' => ['class' => 'address uk-grid uk-grid-small'],
        ]);

        $renderedField = $this->formBuilder->renderField('billing_address');

        // Test wrapper
        $this->assertStringContainsString('<div class="address uk-grid uk-grid-small">', $renderedField);
        $this->assertStringContainsString('</div>', $renderedField);

        // Test label
        $this->assertStringContainsString('<label', $renderedField);
        $this->assertStringContainsString('Billing Address', $renderedField);

        // Test embedded form fields
        $this->assertStringContainsString('<input type="text" name="street"', $renderedField);
        $this->assertStringContainsString('placeholder="Enter street address"', $renderedField);
        $this->assertStringContainsString('<input type="text" name="city"', $renderedField);
        $this->assertStringContainsString('<select name="country"', $renderedField);
        $this->assertStringContainsString('<option value="">Select a country</option>', $renderedField);
        // $this->assertStringContainsString('<option value="us">United States</option>', $renderedField);
        // $this->assertStringContainsString('<option value="ca">Canada</option>', $renderedField);

        // Test that all embedded fields have the 'form-control' class
        $this->assertEquals(3, substr_count($renderedField, 'class="form-control"'));

        // Test that form tags are not present
        $this->assertStringNotContainsString('<form', $renderedField);
        $this->assertStringNotContainsString('</form>', $renderedField);

        // Test that CSRF token is not present
        $this->assertStringNotContainsString('name="_token"', $renderedField);

        // Test formOptions application
        // $this->assertStringContainsString('class="embedded-form"', $renderedField);
    }

    public function testEmbeddedFormWithoutLabel()
    {
        $innerForm = new FormBuilder();
        $innerForm->add('name', 'text', ['label' => 'Name']);

        $this->formBuilder->add('user_info', 'form', [
            'form' => $innerForm,
            'label' => false,
        ]);

        $renderedField = $this->formBuilder->renderField('user_info');

        // // Test that the outer label is not present
        // $this->assertStringNotContainsString('<label', $renderedField);

        // // But the inner label should still be there
        // $this->assertStringContainsString('<label', $renderedField);
        $this->assertStringContainsString('Name', $renderedField);
    }

    public function testNestedEmbeddedForms()
    {
        $addressForm = new FormBuilder();
        $addressForm->add('street', 'text', ['label' => 'Street']);

        $userForm = new FormBuilder();
        $userForm->add('name', 'text', ['label' => 'Name'])
            ->add('address', 'form', ['form' => $addressForm]);

        $this->formBuilder->add('user', 'form', [
            'form' => $userForm,
            'label' => 'User Information',
        ]);

        $renderedField = $this->formBuilder->renderField('user');

        // Test outer label
        $this->assertStringContainsString('User Information', $renderedField);

        // Test nested fields
        $this->assertStringContainsString('<input type="text" name="name"', $renderedField);
        $this->assertStringContainsString('<input type="text" name="street"', $renderedField);

        // Ensure no extra form tags
        $this->assertEquals(0, substr_count($renderedField, '<form'));
        $this->assertEquals(0, substr_count($renderedField, '</form>'));
    }

    public function testModelBinding()
    {
        $model = new class {
            public $name = 'John Doe';
        };

        $this->formBuilder
            ->model($model)
            ->add('name', 'text');

        $output = $this->formBuilder->render();
        $this->assertStringContainsString('value="John Doe"', $output);
    }

    public function testMethodSpoofing()
    {
        $this->formBuilder->method('PATCH');
        $output = $this->formBuilder->render();
        $this->assertStringContainsString('name="_method" value="PATCH"', $output);
    }
}
