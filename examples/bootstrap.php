<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PaperScissorsAndGlue\FormBuilder\FormBuilder;

// Create a new form
$form = new FormBuilder();

// Add fields including new types
$form->add('name', 'text', [
    'label' => 'Name',
    'attr' => ['class' => 'form-control']
])
    ->add('email', 'text', [
        'label' => 'Email',
        'attr' => ['class' => 'form-control']
    ])
    ->add('description', 'textarea', [
        'label' => 'Description',
        'attr' => ['class' => 'form-control']
    ])
    ->add('agree', 'checkbox', [
        'label' => 'I agree to the terms',
        'attr' => ['class' => 'form-check-input']
    ])
    ->add('gender', 'radio', [
        'label' => 'Gender',
        'attr' => ['class' => 'form-check-input'],
        'choices' => [
            'male' => 'Male',
            'female' => 'Female',
            'other' => 'Other'
        ]
    ])
    ->add('country', 'select', [
        'label' => 'Country',
        'attr' => ['class' => 'form-control'],
        'choices' => [
            'us' => 'United States',
            'ca' => 'Canada',
            'uk' => 'United Kingdom'
        ]
    ])
    ->method('POST')
    ->url('/submit')
    ->class('my-form')
    ->csrf('example_token');

// Render the form
echo $form->render();

// Or render individual fields
echo $form->name;
echo $form->email;
echo $form->description;
echo $form->agree;
echo $form->gender;
echo $form->country;
