<?php

namespace Vladimino\Discoverist;

use Pimple\Container;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

class App
{
    private $container;
    private $configDir;

    public function __construct(Container $container, string $configDir)
    {
        $this->container = $container;
        $this->configDir = $configDir;
    }

    public function run(): void
    {
        try {
            $response = $this->retrieveResponse();
        } catch (ResourceNotFoundException $exception) {
            $response = new Response(
                'Not Found',
                Response::HTTP_NOT_FOUND
            );
        } catch (\Exception $exception) {
            $response = new Response(
                'An error occurred: ' . $exception->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        $response->send();
    }

    /**
     * @psalm-suppress MixedInferredReturnType
     * @psalm-suppress MixedReturnStatement
     * @psalm-suppress MixedMethodCall
     */
    private function retrieveResponse(): Response
    {
        $locator = new FileLocator($this->configDir);
        $loader  = new YamlFileLoader($locator);
        $routes  = $loader->load('routes.yml');

        $request = Request::createFromGlobals();
        $context = new RequestContext();
        $context->fromRequest($request);
        $matcher = new UrlMatcher($routes, $context);
        $request->attributes->add($matcher->match($request->getPathInfo()));
        $controller       = $request->attributes->get('_controller');
        $action           = $request->attributes->get('action', 'indexAction');
        $controllerObject = new $controller($this->container, new Session());

        return $controllerObject->$action($request);
    }
}
