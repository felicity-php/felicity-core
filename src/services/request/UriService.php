<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\core\services\request;

use ReflectionException;
use voku\helper\AntiXSS;
use felicity\config\Config;
use felicity\core\models\UriModel;

/**
 * Class UriService
 */
class UriService
{
    /** @var Config $config */
    private $config;

    /** @var AntiXSS $antiXSS */
    private $antiXSS;

    /**
     * Uri constructor
     * @param Config $config
     * @param AntiXSS $antiXSS
     */
    public function __construct(Config $config, AntiXSS $antiXSS)
    {
        $this->config = $config;
        $this->antiXSS = $antiXSS;
    }

    /**
     * Gets a URI model for the submitted URI
     * @param string $uri
     * @param string $requestMethod
     * @return UriModel
     */
    public function getUriModel(string $uri, string $requestMethod = 'get') : UriModel
    {
        // Get config

        $processPagination = $this->config->getItem(
            'uri.pagination.process',
            true
        );

        $paginationTrigger = $this->config->getItem(
            'uri.pagination.trigger',
            'page'
        );

        // Set request method
        $requestMethod = strtolower($requestMethod);

        // Get the uri parts
        $uriParts = parse_url($uri);

        // Get the uri segments
        $uriSegments = $this->stripUpToPhp(
            explode('/', ltrim($uriParts['path'], '/'))
        );

        // Make sure the segments are clean
        foreach ($uriSegments as $key => $segment) {
            $uriSegments[$key] = $this->antiXSS->xss_clean($segment);
        }

        // Get the segment count
        $segCount = \count($uriSegments);

        // Set default page
        $page = 1;

        // Process pagination
        if ($processPagination === true &&
            \count($uriSegments) > 1 &&
            \ctype_digit($uriSegments[$segCount - 1]) &&
            (int) $uriSegments[$segCount - 1] > 1 &&
            $uriSegments[$segCount - 2] === $paginationTrigger
        ) {
            $page = (int) $uriSegments[$segCount - 1];
            unset(
                $uriSegments[$segCount - 1],
                $uriSegments[$segCount - 2]
            );
        }

        // Prepare query
        $queryRaw = $uriParts['query'] ?? '';
        $queryRaw = $this->antiXSS->xss_clean($queryRaw);
        $query = [];
        parse_str($queryRaw, $query);

        // Return the URI model
        return new UriModel([
            'raw' => $this->antiXSS->xss_clean($uriParts['path']),
            'segments' => array_values($uriSegments),
            'path' => implode('/', $uriSegments),
            'queryRaw' => $queryRaw,
            'query' => $query,
            'page' => $page,
            'requestMethod' => $requestMethod,
        ]);
    }

    /**
     * Strips path up to PHP
     * @param array $uriSegments
     * @return array
     */
    private function stripUpToPhp(array $uriSegments) : array
    {
        $startingSegment = false;

        // Loop through to find a .php segment
        foreach ($uriSegments as $key => $val) {
            if (strpos($val, '.php')) {
                $startingSegment = $key;
                break;
            }
        }

        // Remove any segment before and up to $startingSegment
        if ($startingSegment !== false) {
            foreach ($uriSegments as $key => $val) {
                unset($uriSegments[$key]);
                if ($key === $startingSegment) {
                    break;
                }
            }
        }

        // Remove empty segments
        foreach ($uriSegments as $key => $segment) {
            if (empty($segment)) {
                unset($uriSegments[$key]);
            }
        }

        // Reset the segments array
        return array_values($uriSegments);
    }
}
