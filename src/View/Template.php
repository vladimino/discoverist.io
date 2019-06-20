<?php

namespace Vladimino\Discoverist\View;

use Vladimino\Discoverist\Core\Config;

class Template
{
    public const TYPE_TWIG = 'twig';

    /**
     * @var array
     */
    protected $config;

    /**
     * @var TemplateInterface
     */
    protected $engine;

    public function __construct()
    {
        $this->config = Config::get('templates');

        if (!isset($this->config['type'], $this->config['params'])) {
            throw new \UnexpectedValueException('Templates configuration is invalid');
        }

        /** @noinspection DegradedSwitchInspection */
        switch ($this->config['type']) {
            case self::TYPE_TWIG:
                $this->engine = new TwigTemplate($this->config['params']);
                break;

            default:
                throw new \InvalidArgumentException(\sprintf("Unknown template type '%s'", $this->config['type']));
        }
    }

    public function render(string $template, ?array $data): string
    {
        return $this->engine->render($template, $data ?? []);
    }
}
