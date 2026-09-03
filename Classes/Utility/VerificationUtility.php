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

namespace Portrino\Impersonate\Utility;

class VerificationUtility
{
    /**
     * @param int $timeout
     * @param string $siteIdentifier
     * @param int $user
     * @return string
     */
    public static function buildVerificationHash(int $timeout, string $siteIdentifier, int $user): string
    {
        if (BackendUserUtility::hasCurrentBackendUserImpersonationAccess()) {
            return hash(
                'sha256',
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']
                . $GLOBALS['BE_USER']->getSession()->getIdentifier()
                . $timeout
                . $siteIdentifier
                . $user
            );
        }
        return '';
    }

    /**
     * @param array<string, int|string> $impersonateData
     * @return bool
     */
    public static function verifyImpersonateData(array $impersonateData): bool
    {
        if (
            isset($impersonateData['timeout'], $impersonateData['user'], $impersonateData['site'], $impersonateData['verification'])
            && $impersonateData['timeout'] > time()
            && BackendUserUtility::hasCurrentBackendUserImpersonationAccess()
        ) {
            return $impersonateData['verification'] === hash(
                'sha256',
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']
                . $GLOBALS['BE_USER']->getSession()->getIdentifier()
                . $impersonateData['timeout']
                . $impersonateData['site']
                . $impersonateData['user']
            );
        }
        return false;
    }
}
