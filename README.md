# FormBuilder

FormBuilder is a flexible PHP library for creating and rendering HTML forms. It provides an intuitive API for defining form fields, setting attributes, and handling form submissions.

## Installation

You can install FormBuilder via Composer:

```bash
composer require paperscissorsandglue/formbuilder

Usage
Basic Usage
use PaperScissorsAndGlue\FormBuilder\FormBuilder;

$form = new FormBuilder();

$form->add('name', 'text', ['label' => 'Name'])
     ->add('email', 'text', ['label' => 'Email'])
     ->add('submit', 'submit', ['label' => 'Submit'])
     ->method('POST')
     ->url('/submit')
     ->class('my-form');

echo $form->render();

Field Types
FormBuilder supports the following field types:

text
textarea
checkbox
radio
select
number
hidden
submit


Adding Fields
Use the add method to add fields to your form:
$form->add('fieldName', 'fieldType', [
    'label' => 'Field Label',
    'attr' => ['class' => 'form-control'],
    // Other options...
]);

Field Options
Each field type supports various options:

label: The label for the field
attr: An array of HTML attributes for the field
placeholder: Placeholder text for text inputs
choices: An array of options for select, radio, and checkbox fields
empty_value: An initial empty option for select fields
wrapper: Wrap the field in a container (e.g., for Bootstrap)
label_attr: Attributes for the label element
errors: Configure error display for the field


Form Attributes
Set form attributes using method chaining:
$form->method('POST')
     ->url('/submit')
     ->class('my-form');

CSRF Protection
Add CSRF protection to your form:
$form->csrf('your_csrf_token_here');

Model Binding
Bind a model to pre-fill form values:
$user = new User(['name' => 'John Doe', 'email' => 'john@example.com']);
$form->model($user);

Embedded Forms
You can embed one form inside another:
$addressForm = new FormBuilder();
$addressForm->add('street', 'text', ['label' => 'Street'])
            ->add('city', 'text', ['label' => 'City']);

$mainForm = new FormBuilder();
$mainForm->add('name', 'text', ['label' => 'Name'])
         ->add('address', 'form', [
             'form' => $addressForm,
             'formOptions' => ['class' => 'embedded-form']
         ]);

Rendering
Render the entire form:
echo $form->render();

Or render individual fields:
echo $form->name;
echo $form->email;

Customization
FormBuilder is designed to be easily customizable. You can extend the FormBuilder class to add your own field types or modify existing ones.

Contributing
Contributions are welcome! Please feel free to submit a Pull Request.

License
This project is open-sourced software licensed under the MIT license.

This updated README.md file includes information about the new functionality you've added, such as:

1. Additional field types (number, hidden)
2. More detailed explanation of field options
3. Information about embedded forms
4. CSRF protection
5. Model binding
6. Rendering individual fields

The README now provides a comprehensive guide to using the FormBuilder class, covering all the main features and usage patterns.
