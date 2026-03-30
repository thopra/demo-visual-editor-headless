<?php

/*
 * This file is part of the "headless" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FriendsOfTYPO3\Headless\Middleware;

use FriendsOfTYPO3\Headless\Utility\HeadlessFrontendUrlInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Site\SiteFinder;


/*
 * Necessary headers to work with visual_editor. Just existing for convienience here,
 * should proably be a concern outside of headless extension and be moved to nginx.
 * The _assets folder needs CORS headers anyway. So I would not implement it this way
 */
class CorsMiddleware implements MiddlewareInterface
{
    private HeadlessFrontendUrlInterface $urlUtility;
    private SiteFinder $siteFinder;
    private LoggerInterface $logger;

    public function __construct(
        HeadlessFrontendUrlInterface $urlUtility,
        SiteFinder $siteFinder,
        LoggerInterface $logger
    ) {
        $this->urlUtility = $urlUtility;
        $this->siteFinder = $siteFinder;
        $this->logger = $logger;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        /** @var NormalizedParams $normalizedParams */
        $normalizedParams = $request->getAttribute('normalizedParams');
        $requestHost = $normalizedParams->getHttpHost();
        $origin = $request->getServerParams()['HTTP_ORIGIN'] ?? null;
        $setOrigin = null;
        $allSites = $this->siteFinder->getAllSites();

        if ($origin) {
            foreach ($allSites as $site) {
                $urlUtility = $this->urlUtility->withSite($site);
                $base = $urlUtility->resolveKey('frontendBase');

                if ($base && str_starts_with($origin, $base)) {
                    $setOrigin = $base;
                }
            }
        }

        // @todo: check if there is another way or how to improve this for the visual_editor frame to work

        $response = $handler->handle($request);
        if ($setOrigin) {
            return $response
                ->withAddedHeader('Access-Control-Allow-Origin', $setOrigin)
                ->withAddedHeader('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }
}
