<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\core\services\request;

use ReflectionException;
use felicity\logging\Logger;
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

    /** @var Logger $logger */
    private $logger;

    /**
     * RoutingService constructor
     * @param Routing $routing
     * @param Logger $logger
     */
    public function __construct(
        Routing $routing,
        Logger $logger
    ) {
        $this->routing = $routing;
        $this->logger = $logger;
    }

    /**
     * Run URI
     * @param UriModel $uriModel
     * @return RoutingModel
     * @throws ReflectionException
     */
    public function runUri(UriModel $uriModel) : RoutingModel
    {
        $this->logger->addLog(
            "RoutingService running URI {$uriModel->requestMethod}: " .
                $uriModel->path,
            Logger::LEVEL_INFO,
            'felicityCore'
        );

        $routingMatches = $this->routing->getUriMatches(
            $uriModel->requestMethod,
            $uriModel->path
        );

        $routingModel = new RoutingModel();

        $hasMatchingRoutes = false;

        foreach ($routingMatches as $routingMatch) {
            /** @var MatchedRouteModel $routingMatch */

            $this->logger->addLog(
                "RoutingService running matched route: {$routingMatch->route}",
                Logger::LEVEL_INFO,
                'felicityCore'
            );

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
                $this->logger->addLog(
                    'RoutingService route callback has requested routing stop',
                    Logger::LEVEL_INFO,
                    'felicityCore'
                );

                break;
            }
        }

        if (! $hasMatchingRoutes) {
            $this->logger->addLog(
                'There were no matching routes, added 404 response code...',
                Logger::LEVEL_INFO,
                'felicityCore'
            );

            $routingModel->responseCode = 404;
        }

        return $routingModel;
    }
}
