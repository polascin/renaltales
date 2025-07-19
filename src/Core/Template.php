<?php

declare(strict_types=1);

namespace RenalTales\Core;

use Exception;

class Template
{
    private $variables = [];
    private $viewsPath;

    public function __construct($viewsPath = null)
    {
        $this->viewsPath = $viewsPath ?: dirname(__DIR__, 2) . '/resources/views/';
    }

    public function set($name, $value)
    {
        $this->variables[$name] = $value;
        return $this;
    }

    public function setMultiple(array $variables)
    {
        $this->variables = array_merge($this->variables, $variables);
        return $this;
    }

    public function render($template, $data = null, $return = false)
    {
        // Support both old format and new direct format
        if (is_array($data)) {
            $this->setMultiple($data);
        } elseif (is_bool($data)) {
            $return = $data;
        }

        $content = $this->processTemplate($this->load($template));
        if ($return) {
            return $content;
        }
        echo $content;
    }

    public function include($template)
    {
        return $this->processTemplate($this->load($template));
    }

    private function load($template)
    {
        $path = $this->viewsPath . $template . '.php';
        if (!file_exists($path)) {
            throw new Exception("Template not found: $template");
        }
        return file_get_contents($path);
    }

    private function processTemplate($content)
    {
        // Handle PHP includes first
        $content = preg_replace_callback(
            '/<\?php echo \$this->include\(\'([^\']*)\'\); \?>/',
            function ($matches) { return $this->include($matches[1]); },
            $content
        );

        // Replace variables
        foreach ($this->variables as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value); // Convert arrays to JSON strings
            }
            $content = str_replace('{{ ' . $key . ' }}', $value, $content);
            $content = str_replace('<?= $' . $key . ' ?>', $value, $content);
        }
        return $content;
    }
}
