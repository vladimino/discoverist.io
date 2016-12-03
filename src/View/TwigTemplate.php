<?php

namespace Vladimino\Discoverist\View;

use Twig_Autoloader;
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
    public function init(array $params)
    {
        Twig_Autoloader::register();

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
     * @param array  $data
     *
     * @return string
     */
    public function render($template, $data = [])
    {
        return $this->twig->render($template.self::TEMPLATE_EXTENSION, $data);
    }
}
