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

namespace ChristianEssl\Impersonate\Listener;

use ChristianEssl\Impersonate\Utility\BackendUserUtility;
use TYPO3\CMS\Backend\RecordList\Event\ModifyRecordListRecordActionsEvent;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Event listener for DatabaseRecordList, implementing the icons for impersonating a frontend user
 */
class RecordListRecordActionsListener
{
    /**
     * @param ModifyRecordListRecordActionsEvent $event
     * @throws RouteNotFoundException
     */
    public function __invoke(ModifyRecordListRecordActionsEvent $event): void
    {
        if ($event->getTable() === 'fe_users'
            && BackendUserUtility::hasCurrentBackendUserImpersonationAccess()
        ) {
            $event->setAction(
                $this->addImpersonateButton($event->getRecord()),
                'impersonate',
                'primary',
                '',
                'delete'
            );
        }
    }

    /**
     * @param array<string, mixed> $userRow
     * @return string
     * @throws RouteNotFoundException
     * @throws SiteNotFoundException
     */
    protected function addImpersonateButton(array $userRow): string
    {
        $siteIdentifier = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId((int)$userRow['pid'])->getIdentifier();
        $userUid = (int)$userRow['uid'];

        $uri = $this->buildFrontendLoginUri($siteIdentifier, $userUid);

        $buttonText = $this->translate('button.impersonate');
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $iconMarkup = $iconFactory->getIcon('actions-system-backend-user-switch', IconSize::SMALL)->render();

        return '
            <a class="btn btn-default t3-impersonate-button"
               href="' . $uri . '" target="newTYPO3frontendWindow"
               title="' . $buttonText . '">
	                ' . $iconMarkup . '
            </a>';
    }

    /**
     * @param string $siteIdentifier
     * @param int $userUid
     * @return string
     * @throws RouteNotFoundException
     */
    protected function buildFrontendLoginUri(string $siteIdentifier, int $userUid): string
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        return (string)$uriBuilder->buildUriFromRoute('impersonate_frontendlogin', ['site' => $siteIdentifier, 'user' => $userUid]);
    }

    /**
     * @param string $key
     *
     * @return string
     */
    protected function translate(string $key): string
    {
        return $GLOBALS['LANG']->sL('LLL:EXT:impersonate/Resources/Private/Language/locallang.xlf:' . $key);
    }
}
