<?php

namespace Vladimino\Discoverist\View;

use Vladimino\Discoverist\Core\Config;

/**
 * Class Template renders the view files.
 */
class Template
{
    const TYPE_TWIG = 'twig';

    /** @var array */
    protected $config;

    /** @var  \SearchSystem\View\TemplateInterface */
    protected $engine;

    /**
     * Template constructor.
     */
    public function __construct()
    {
        $this->config = Config::get('templates');

        if (!isset($this->config['type']) || !isset($this->config['params'])) {
            throw new \RuntimeException('Templates configuration is invalid');
        }

        switch ($this->config['type']) {
            case self::TYPE_TWIG:
                $this->engine = new TwigTemplate();
                break;

            default:
                throw new \RuntimeException(sprintf("Unknown template type '%s'", $this->config['type']));
        }

        $this->engine->init($this->config['params']);
    }

    /**
     * @param string $template
     * @param array  $data
     *
     * @return string
     */
    public function render($template, $data = [])
    {
        return $this->engine->render($template, $data);
    }
}
