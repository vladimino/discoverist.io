<?php

namespace Vladimino\Discoverist\View;

/**
 * Interface TemplateInterface
 */
interface TemplateInterface
{
    /**
     * @param string $template
     * @param array $data
     *
     * @return string
     */
    public function render(string $template, ?array $data): string;
}
