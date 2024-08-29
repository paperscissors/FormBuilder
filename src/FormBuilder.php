<?php

namespace PaperScissorsAndGlue\FormBuilder;

class FormBuilder
{
    private $fields = [];
    private $formAttributes = [
        'method' => 'POST',
        'url' => '',
        'class' => '',
    ];
    private $model = null;
    private $csrf = null;

    public function add($name, $type, $options = [])
    {
        $this->fields[$name] = [
            'type' => $type,
            'options' => $options
        ];
        return $this;
    }

    public function getField($name)
    {
        return $this->fields[$name] ?? null;
    }

    public function renderField($name)
    {
        $field = $this->getField($name);
        if (!$field) {
            return '';
        }

        $html = '';

        // Wrapper start
        if (isset($field['options']['wrapper'])) {
            $wrapperClass = $field['options']['wrapper']['class'] ?? '';
            $html .= "<div class=\"{$wrapperClass}\">\n";
        }

        // Label (not for hidden fields or when explicitly set to false)
        if ($field['type'] !== 'hidden' && isset($field['options']['label']) && $field['options']['label'] !== false) {
            $labelClass = $field['options']['label_attr']['class'] ?? '';
            $html .= "<label class=\"{$labelClass}\" for=\"{$name}\">{$field['options']['label']}</label>\n";
        }

        // Field
        $attributes = $field['options']['attr'] ?? [];
        if (isset($field['options']['placeholder'])) {
            $attributes['placeholder'] = $field['options']['placeholder'];
        }
        $attributeString = $this->buildAttributes($attributes);
        $value = $this->getFieldValue($name);

        switch ($field['type']) {
            case 'text':
            case 'submit':
            case 'number':
            case 'hidden':
                $html .= "<input type=\"{$field['type']}\" name=\"{$name}\" value=\"{$value}\" {$attributeString}>\n";
                break;
            case 'textarea':
                $html .= "<textarea name=\"{$name}\" {$attributeString}>{$value}</textarea>\n";
                break;
            case 'checkbox':
                $checked = $value ? 'checked' : '';
                $html .= "<input type=\"checkbox\" name=\"{$name}\" value=\"1\" {$checked} {$attributeString}>\n";
                break;
            case 'radio':
                foreach ($field['options']['choices'] as $optionValue => $label) {
                    $checked = $optionValue == $value ? 'checked' : '';
                    $html .= "<input type=\"radio\" name=\"{$name}\" value=\"{$optionValue}\" {$checked} {$attributeString}>\n";
                    $html .= "<label for=\"{$name}_{$optionValue}\">{$label}</label>\n";
                }
                break;
            case 'select':
                $html .= "<select name=\"{$name}\" {$attributeString}>\n";
                if (isset($field['options']['empty_value'])) {
                    $html .= "<option value=\"\">{$field['options']['empty_value']}</option>\n";
                }
                foreach ($field['options']['choices'] as $optionValue => $label) {
                    $selected = $optionValue == $value ? 'selected' : '';
                    $html .= "<option value=\"{$optionValue}\" {$selected}>{$label}</option>\n";
                }
                $html .= "</select>\n";
                break;
            case 'form':
                $html .= $this->renderEmbeddedForm($field['options']['form'], $field['options']['formOptions'] ?? []);
                break;
        }

        // Error messages
        if (isset($field['options']['errors'])) {
            $errorClass = $field['options']['errors']['class'] ?? '';
            $html .= "<div style=\"display: none\" class=\"{$errorClass}\"></div>\n";
        }

        // Wrapper end
        if (isset($field['options']['wrapper'])) {
            $html .= "</div>\n";
        }

        return $html;
    }

    private function buildAttributes(array $attributes): string
    {
        $html = '';
        foreach ($attributes as $key => $value) {
            $html .= $value === '' ? "{$key} " : "{$key}=\"{$value}\" ";
        }
        return trim($html);
    }

    private function renderEmbeddedForm(FormBuilder $form, array $formOptions = [])
    {
        // Apply any additional options to the embedded form
        foreach ($formOptions as $key => $value) {
            if (method_exists($form, $key)) {
                $form->$key($value);
            }
        }

        // Render the form content without the opening/closing tags and CSRF field
        $formContent = $form->render();
        $formContent = preg_replace('/<form[^>]*>|<\/form>/', '', $formContent);
        $formContent = preg_replace('/<input[^>]*name="_token"[^>]*>/', '', $formContent);
        // remove buttons or submit buttons
        $formContent = preg_replace('/<button[^>]*type="submit"[^>]*>/', '', $formContent);
        $formContent = preg_replace('/<button[^>]*type="button"[^>]*>/', '', $formContent);
        // remove inputs that have a type of submit
        $formContent = preg_replace('/<input[^>]*type="submit"[^>]*>/', '', $formContent);
        // remove csrf token field
        $formContent = preg_replace('/<input[^>]*name="_token"[^>]*>/', '', $formContent);

        return trim($formContent);
    }

    public function render()
    {
        $html = $this->renderFormOpen();
        if ($this->csrf) {
            $html .= $this->renderCsrfField();
        }
        foreach ($this->fields as $name => $field) {
            $html .= $this->renderField($name);
        }
        $html .= $this->renderFormClose();
        return $html;
    }

    public function method($method)
    {
        $this->formAttributes['method'] = $method;
        return $this;
    }

    public function url($url)
    {
        $this->formAttributes['url'] = $url;
        return $this;
    }

    public function class($class)
    {
        $this->formAttributes['class'] = $class;
        return $this;
    }

    public function model($model)
    {
        $this->model = $model;
        return $this;
    }

    public function csrf($token)
    {
        $this->csrf = $token;
        return $this;
    }

    public function renderFormOpen()
    {
        $method = strtoupper($this->formAttributes['method']);
        $url = $this->formAttributes['url'];
        $class = $this->formAttributes['class'];

        $html = "<form method=\"" . ($method === 'GET' ? 'GET' : 'POST') . "\" action=\"{$url}\" class=\"{$class}\">\n";

        if ($method !== 'GET' && $method !== 'POST') {
            $html .= "<input type=\"hidden\" name=\"_method\" value=\"{$method}\">\n";
        }

        return $html;
    }

    public function renderFormClose()
    {
        return "</form>\n";
    }

    public function renderCsrfField()
    {
        return "<input type=\"hidden\" name=\"_token\" value=\"{$this->csrf}\">\n";
    }

    private function getFieldValue($name)
    {
        if ($this->model && isset($this->model->$name)) {
            return $this->model->$name;
        }
        return $this->fields[$name]['options']['attr']['value'] ?? '';
    }

    public function __get($name)
    {
        return $this->renderField($name);
    }
}
