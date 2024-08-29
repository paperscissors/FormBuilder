# FormBuilder

FormBuilder is a flexible and easy-to-use PHP library for creating HTML forms. It provides a simple API to generate form elements, handle form submissions, and integrate with your existing PHP projects.

## Features

- Simple and intuitive API for creating form elements
- Support for various input types including text, textarea, checkbox, radio, and select
- Easy integration with existing PHP projects
- Customizable form rendering
- CSRF protection
- Model binding for populating form fields

## Installation

You can install the FormBuilder package via Composer. Run the following command in your project directory:

```bash
composer require paperscissorsandglue/form-builder
```

## Usage

Here's a basic example of how to use FormBuilder:

```php
use PaperScissorsAndGlue\FormBuilder\FormBuilder;

$form = new FormBuilder();

$form->add('name', 'text', [
    'label' => 'Name',
    'attr' => ['class' => 'form-control']
])
->add('email', 'text', [
    'label' => 'Email',
    'attr' => ['class' => 'form-control', 'type' => 'email']
])
->add('message', 'textarea', [
    'label' => 'Message',
    'attr' => ['class' => 'form-control']
])
->add('subscribe', 'checkbox', [
    'label' => 'Subscribe to newsletter',
    'attr' => ['class' => 'form-check-input']
])
->method('POST')
->action('/submit')
->class('my-form');

// Render the entire form
echo $form->render();

// Or render individual fields
echo $form->name;
echo $form->email;
echo $form->message;
echo $form->subscribe;
```

## Advanced Usage

### Model Binding

You can bind a model (or any object) to the form to populate the fields:

```php
$user = new User(['name' => 'John Doe', 'email' => 'john@example.com']);
$form->model($user);
```

### CSRF Protection

To add CSRF protection to your form:

```php
$form->csrf('your_csrf_token_here');
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

If you encounter any problems or have any questions, please open an issue on the [GitHub repository](https://github.com/your-username/form-builder/issues).

## Acknowledgements

Thanks to all the contributors who have helped to build and improve FormBuilder.