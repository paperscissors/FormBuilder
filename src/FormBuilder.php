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

        // Label
        if (isset($field['options']['label'])) {
            $labelClass = $field['options']['label_attr']['class'] ?? '';
            $html .= "<label class=\"{$labelClass}\" for=\"{$name}\">{$field['options']['label']}</label>\n";
        }

        // Field
        $fieldClass = $field['options']['attr']['class'] ?? '';
        $required = isset($field['options']['attr']['required']) && $field['options']['attr']['required'] ? 'required' : '';
        $value = $this->getFieldValue($name);

        switch ($field['type']) {
            case "hidden":
                $html .= "<input type=\"hidden\" id=\"{$name}\" name=\"{$name}\" value=\"{$value}\">\n";
                break;
            case 'password':
            case 'text':
            case 'submit':
                $html .= "<input type=\"{$field['type']}\" id=\"{$name}\" name=\"{$name}\" class=\"{$fieldClass}\" {$required} value=\"{$value}\">\n";
                break;
            case 'textarea':
                $html .= "<textarea id=\"{$name}\" name=\"{$name}\" class=\"{$fieldClass}\" {$required}>{$value}</textarea>\n";
                break;
            case 'checkbox':
                $checked = $value ? 'checked' : '';
                $html .= "<input type=\"checkbox\" id=\"{$name}\" name=\"{$name}\" class=\"{$fieldClass}\" {$required} {$checked} value=\"1\">\n";
                break;
            case 'radio':
                foreach ($field['options']['choices'] as $optionValue => $label) {
                    $checked = $optionValue == $value ? 'checked' : '';
                    $html .= "<input type=\"radio\" id=\"{$name}_{$optionValue}\" name=\"{$name}\" class=\"{$fieldClass}\" {$required} value=\"{$optionValue}\" {$checked}>\n";
                    $html .= "<label for=\"{$name}_{$optionValue}\">{$label}</label>\n";
                }
                break;
            case 'select':
                $html .= "<select id=\"{$name}\" name=\"{$name}\" class=\"{$fieldClass}\" {$required}>\n";
                if (isset($field['options']['empty_value'])) {
                    $html .= "<option value=\"\">{$field['options']['empty_value']}</option>\n";
                }
                foreach ($field['options']['choices'] as $optionValue => $label) {
                    $selected = $optionValue == $value ? 'selected' : '';
                    $html .= "<option value=\"{$optionValue}\" {$selected}>{$label}</option>\n";
                }
                $html .= "</select>\n";
                break;
        }

        // Error messages
        if (isset($field['options']['errors'])) {
            $errorClass = $field['options']['errors']['class'] ?? '';
            $html .= "<div style='display: none;' class=\"{$errorClass}\"></div>\n";
        }

        // Wrapper end
        if (isset($field['options']['wrapper'])) {
            $html .= "</div>\n";
        }

        return $html;
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
