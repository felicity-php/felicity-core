<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\core\services\request;

use ReflectionException;
use felicity\routing\Routing;
use felicity\core\models\UriModel;
use felicity\core\models\RoutingModel;
use felicity\routing\models\MatchedRouteModel;

/**
 * Class RoutingService
 */
class RoutingService
{
    /** @var Routing $routing */
    private $routing;

    /**
     * RoutingService constructor
     * @param Routing $routing
     */
    public function __construct(Routing $routing)
    {
        $this->routing = $routing;
    }

    /**
     * Run URI
     * @param UriModel $uriModel
     * @return RoutingModel
     * @throws ReflectionException
     */
    public function runUri(UriModel $uriModel) : RoutingModel
    {
        $routingMatches = $this->routing->getUriMatches(
            $uriModel->requestMethod,
            $uriModel->path
        );

        $routingModel = new RoutingModel();

        $hasMatchingRoutes = false;

        foreach ($routingMatches as $routingMatch) {
            /** @var MatchedRouteModel $routingMatch */

            $varsArray = [
                $routingModel,
            ];

            foreach ($routingMatch->match as $match) {
                $varsArray[] = $match;
            }

            $response = \call_user_func_array(
                $routingMatch->callback,
                $varsArray
            );

            if (\is_array($response)) {
                $routingModel->responseData = $response;
            } elseif (\is_string($response)) {
                $routingModel->responseData = $response;
            }

            $hasMatchingRoutes = true;

            if ($routingModel->stopRouting) {
                break;
            }
        }

        if (! $hasMatchingRoutes) {
            $routingModel->responseCode = 404;
        }

        return $routingModel;
    }
}
