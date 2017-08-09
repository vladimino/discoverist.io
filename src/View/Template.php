<?php

namespace Vladimino\Discoverist\View;

use Vladimino\Discoverist\Core\Config;

/**
 * Class Template renders the view files.
 */
class Template
{
    const TYPE_TWIG = 'twig';

    /**
     * @var array
     */
    protected $config;

    /**
     * @var \Vladimino\Discoverist\View\TemplateInterface
     */
    protected $engine;

    /**
     * Template constructor.
     *
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function __construct()
    {
        $this->config = Config::get('templates');

        if (!isset($this->config['type'], $this->config['params'])) {
            throw new \UnexpectedValueException('Templates configuration is invalid');
        }

        /** @noinspection DegradedSwitchInspection */
        switch ($this->config['type']) {
            case self::TYPE_TWIG:
                $this->engine = new TwigTemplate();
                break;

            default:
                throw new \InvalidArgumentException(\sprintf("Unknown template type '%s'", $this->config['type']));
        }

        $this->engine->init($this->config['params']);
    }

    /**
     * @param string $template
     * @param array $data
     *
     * @return string
     */
    public function render($template, ?array $data): string
    {
        return $this->engine->render($template, $data ?? []);
    }
}
