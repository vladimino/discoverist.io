<?php

namespace Vladimino\Discoverist\Controller;

use Pimple\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Vladimino\Discoverist\View\Template;

/**
 * Class BaseController.
 */
abstract class AbstractController
{
    const SECTION_F2F     = 'f2f';
    const SECTION_RESULTS = 'results';

    const PARAM_TOWN      = 'town';
    const PARAM_COUNTRY   = 'country';

    /**
     * @var \Pimple\Container
     */
    public $container;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session
     */
    protected $session;

    /**
     * BaseController constructor.
     *
     * @param \Pimple\Container $container
     * @param \Symfony\Component\HttpFoundation\Session\Session $session
     */
    public function __construct(Container $container, Session $session)
    {
        $this->container = $container;
        $this->session   = $session;
    }

    /**
     * @param string $template
     * @param array $data
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     */
    protected function render(string $template, ?array $data = null): Response
    {
        $response       = new Response();
        $templateEngine = new Template();
        $response->setContent($templateEngine->render($template, $data ?? []));

        return $response;
    }

    /**
     * @param string $url Url to redirect
     * @param  int $status
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \InvalidArgumentException
     */
    protected function redirect(string $url, ?int $status = null): RedirectResponse
    {
        return new RedirectResponse($url, $status ?? Response::HTTP_FOUND);
    }
}
