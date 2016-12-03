<?php

namespace Vladimino\Discoverist;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

/**
 * Class App
 * @package Vladimino\Discoverist
 */
class App
{
    /**
     * Runs an engine
     */
    public function run()
    {
        $locator = new FileLocator(CONFIG_DIR);
        $loader  = new YamlFileLoader($locator);
        $routes  = $loader->load('routes.yml');

        $request = Request::createFromGlobals();
        $context = new RequestContext();
        $context->fromRequest($request);
        $matcher = new UrlMatcher($routes, $context);

        try {
            $request->attributes->add($matcher->match($request->getPathInfo()));
            $controller       = $request->attributes->get('_controller');
            $action           = $request->attributes->get('action');
            $controllerObject = new $controller();
            $response         = $controllerObject->$action($request);
        } catch (ResourceNotFoundException $e) {
            $response = new Response('Not Found', 404);
        } catch (\Exception $e) {
            $response = new Response('An error occurred: '.$e->getMessage(), 500);
        }

        $response->send();
    }
}
