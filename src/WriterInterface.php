<?php
namespace orq\php\yelly;

interface WriterInterface {
    /**
     * Write blog content into template
     * @param array $vars
     * @param string $template to use for assembling
     * @return string The whole HTML content
     */
    public function assemble($vars, $template);
}