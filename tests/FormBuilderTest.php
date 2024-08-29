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

    public function testCsrfToken()
    {
        $this->formBuilder->csrf('test_token');
        $output = $this->formBuilder->render();
        $this->assertStringContainsString('name="_token" value="test_token"', $output);
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
