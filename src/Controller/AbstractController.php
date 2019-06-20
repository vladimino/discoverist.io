<?php

namespace Vladimino\Discoverist\Controller;

use Pimple\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Vladimino\Discoverist\View\Template;

abstract class AbstractController
{
    protected const SECTION_F2F     = 'f2f';
    protected const SECTION_RESULTS = 'results';

    protected const PARAM_TOWN            = 'town';
    protected const PARAM_COUNTRY         = 'country';
    protected const PARAM_SEASON          = 'season';
    protected const PARAM_DEFAULT_COUNTRY = 'defaultCountry';

    public $container;

    protected $session;

    public function __construct(Container $container, Session $session)
    {
        $this->container = $container;
        $this->session   = $session;
    }

    protected function render(string $template, ?array $data = null): Response
    {
        $response       = new Response();
        $templateEngine = new Template();
        $response->setContent($templateEngine->render($template, $data ?? []));

        return $response;
    }

    protected function redirect(string $url, ?int $status = null): RedirectResponse
    {
        return new RedirectResponse($url, $status ?? Response::HTTP_FOUND);
    }
}
