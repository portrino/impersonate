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

namespace ChristianEssl\Impersonate\Controller;

use ChristianEssl\Impersonate\Utility\VerificationUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Backend\Routing\PreviewUriBuilder;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Site\SiteFinder;

/**
 * Handles logging in a frontend user with the given uid
 */
#[Autoconfigure(public: true)]
class FrontendLoginController
{
    public function __construct(protected SiteFinder $siteFinder) {}

    /**
     * @param ServerRequestInterface $request
     *
     * @return RedirectResponse
     * @throws SiteNotFoundException
     */
    public function loginAction(ServerRequestInterface $request): ResponseInterface
    {
        $siteIdentifier = (string)$request->getQueryParams()['site'];
        $userUid = (int)$request->getQueryParams()['user'];

        if ($siteIdentifier === '' || $userUid === 0) {
            throw new \RuntimeException('Site identifier or user uid missing.', 1738245688);
        }

        // redirect to site root should always be safe for login purposes
        // -> "login redirect" happens in RedirectHandler middleware
        $pageUid = $this->siteFinder->getSiteByIdentifier($siteIdentifier)->getRootPageId();
        $additionalGetVars = [
            'tx_impersonate' => [
                'site' => $siteIdentifier,
                'timeout' => $timeout = time() + 60,
                'user' => $userUid,
                'verification' => VerificationUtility::buildVerificationHash(
                    $timeout,
                    $siteIdentifier,
                    $userUid
                ),
            ],
        ];
        $previewUrl = (string)PreviewUriBuilder::create($pageUid)
                                               ->withAdditionalQueryParameters($additionalGetVars)
                                               ->buildUri();

        return new RedirectResponse($previewUrl);
    }
}
