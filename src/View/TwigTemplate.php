<?php

namespace Vladimino\Discoverist\View;

use Twig_Environment;
use Twig_Loader_Filesystem;

class TwigTemplate implements TemplateInterface
{
    public const TEMPLATE_EXTENSION = '.twig';

    /**
     * @var Twig_Environment
     */
    protected $twig;

    public function __construct(array $params)
    {
        $loader = new Twig_Loader_Filesystem($params['views_path']);

        $this->twig = new Twig_Environment(
            $loader,
            [
                'cache' => $params['compiled_path'],
                'debug' => $params['debug'],
            ]
        );

        /**
         * Configuring global $app variable
         */
        $app = [
            'request' => $_REQUEST
        ];

        $this->twig->addGlobal('app', $app);
    }

    public function render(string $template, ?array $data): string
    {
        return $this->twig->render($template . self::TEMPLATE_EXTENSION, $data ?? []);
    }
}
