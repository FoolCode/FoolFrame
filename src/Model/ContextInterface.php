<?php

namespace Foolz\FoolFrame\Model;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouteCollection;

interface ContextInterface {

    public function handleWeb(Request $request);

    public function handleConsole();

    public function loadRoutes(RouteCollection $route_collection);

}
