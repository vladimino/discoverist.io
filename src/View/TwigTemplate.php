<?php

namespace Vladimino\Discoverist\View;

use Twig_Environment;
use Twig_Loader_Filesystem;

/**
 * Class TwigTemplate
 */
class TwigTemplate implements TemplateInterface
{
    const TEMPLATE_EXTENSION = '.twig';

    /**
     * @var Twig_Environment
     */
    protected $twig;

    /**
     * @param array $params
     *
     * @return void
     */
    public function init(array $params): void
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
        $app['request'] = $_REQUEST;

        $this->twig->addGlobal('app', $app);
    }

    /**
     * @param string $template
     * @param array $data
     *
     * @return string
     * @throws \Twig_Error_Syntax
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Loader
     */
    public function render(string $template, ?array $data): string
    {
        return $this->twig->render($template . self::TEMPLATE_EXTENSION, $data ?? []);
    }
}
