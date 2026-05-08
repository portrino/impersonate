<?php

namespace Portrino\Impersonate\Utility;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

class BackendUserUtility
{
    public static function hasCurrentBackendUserImpersonationAccess(): bool
    {
        return $GLOBALS['BE_USER'] instanceof BackendUserAuthentication
            && (
                $GLOBALS['BE_USER']->isAdmin()
                || filter_var($GLOBALS['BE_USER']->getTSConfig()['tx_impersonate.']['enable'], FILTER_VALIDATE_BOOLEAN)
            );
    }
}
