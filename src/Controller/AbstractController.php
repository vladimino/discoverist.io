<?php

namespace Vladimino\Discoverist\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Vladimino\Discoverist\View\Template;

/**
 * Class BaseController.
 */
abstract class AbstractController
{

    /**
     */
    public $container;

    /**
     * BaseController constructor.
     */
    public function __construct()
    {
        // $this->container = ; todo: implement DI container
    }

    /**
     * @param string $template
     * @param array  $data
     *
     * @return Response
     */
    protected function render($template, array $data = [])
    {
        $response       = new Response();
        $templateEngine = new Template();
        $response->setContent($templateEngine->render($template, $data));

        return $response;
    }

    /**
     * @param string $url Url to redirect
     * @param  int   $status
     *
     * @return RedirectResponse
     */
    protected function redirect($url, $status = 302)
    {
        return new RedirectResponse($url, $status);
    }
}
