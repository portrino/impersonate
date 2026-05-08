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

use Portrino\Impersonate\Authentication\AuthService;
use Portrino\Impersonate\Utility\VerificationUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Logs in a frontend user without a password - use with care!
 */
class FrontendUserAuthenticator implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $impersonateData = $request->getQueryParams()['tx_impersonate'] ?? [];
        if (VerificationUtility::verifyImpersonateData($impersonateData)) {
            ExtensionManagementUtility::addService(
                'impersonate',
                'auth',
                AuthService::class,
                [
                    'title' => 'Temporary AuthService for impersonating a user',
                    'description' => 'Temporary AuthService for impersonating a user',
                    'subtype' => 'authUserFE,getUserFE',
                    'available' => true,
                    'priority' => 100,
                    'quality' => 70,
                    'os' => '',
                    'exec' => '',
                    'className' => AuthService::class,
                ]
            );

            $GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth'] = [
                'setup' => [
                    'FE_alwaysFetchUser' => true,
                ],
            ];
        }

        return $handler->handle($request);
    }
}
