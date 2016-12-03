<?php

namespace Vladimino\Discoverist\View;

/**
 * Interface TemplateInterface
 */
interface TemplateInterface
{
    /**
     * @param array $params
     *
     * @return void
     */
    public function init(array $params);

    /**
     * @param string $template
     * @param array  $data
     *
     * @return mixed
     */
    public function render($template, $data = []);

}
