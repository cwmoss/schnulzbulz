<?php

namespace slow\util\template;


class php {

    public array $stack = [];
    public string $base;
    public string $suffix;

    public function __construct(string $base, string $suffix = '.phtml') {
        $this->base = $base;
        $this->suffix = $suffix;
    }

    public function render($name, $data = [], $context = []) {
        $this->clear_stack();
        $fname = "{$this->base}/{$name}{$this->suffix}";
        $layout = "";
        extract($data);
        ob_start();
        include($fname);
        $html = ob_get_clean();
        if ($layout) {
            $html = $this->render(
                '_layout_' . $layout,
                $data,
                array_merge($context, [
                    'from' => $name, 'content' => $html,
                    'stack' => $this->stack
                ])
            );
        }
        return $html;
    }

    public function render_partial($name, $data = []) {
        $fname = "{$this->base}/_{$name}{$this->suffix}";
        extract($data);
        ob_start();
        include($fname);
        $html = ob_get_clean();
        return $html;
    }

    public function clear_stack() {
        $this->stack = [];
    }

    public function push_stack(string $stackname, $thing) {
        if (!isset($this->stack[$stackname])) {
            $this->stack[$stackname] = [];
        }
        $this->stack[$stackname][] = $thing;
    }
}
