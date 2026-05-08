<?php

declare(strict_types=1);

/*
 * This file is part of the "Impersonate" Extension for TYPO3 CMS.
 *
 * (c) 2019 Christian Eßl <indy.essl@gmail.com>, https://christianessl.at
 *     2022 Axel Böswetter <boeswetter@portrino.de>, https://www.portrino.de
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Portrino\Impersonate\Middleware;

use Portrino\Impersonate\Service\ConfigurationService;
use Portrino\Impersonate\Utility\VerificationUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class RedirectHandler implements MiddlewareInterface
{
    public function __construct(
        protected readonly Context $context,
        protected readonly ConfigurationService $configurationService,
        protected readonly SiteFinder $siteFinder
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var FrontendUserAuthentication $frontendUser */
        $frontendUser = $request->getAttribute('frontend.user');

        // Early return if
        // - no frontend user is available or frontend user is simulated via BE preview
        if ($frontendUser->getUserId() === null || $frontendUser->getUserId() === PHP_INT_MAX) {
            return $handler->handle($request);
        }

        $impersonateData = $request->getQueryParams()['tx_impersonate'] ?? [];
        if (VerificationUtility::verifyImpersonateData($impersonateData)) {
            $siteIdentifier = (string)$impersonateData['site'];
            $getRedirectPageUri = $this->configurationService->getRedirectPageUri($siteIdentifier);
            if ($getRedirectPageUri !== '' && $this->context->getAspect('frontend.user')->isLoggedIn()) {
                return new RedirectResponse($getRedirectPageUri, 307);
            }
        }

        return $handler->handle($request);
    }
}
