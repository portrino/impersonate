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

namespace Portrino\Impersonate\Listener;

use Portrino\Impersonate\Utility\BackendUserUtility;
use TYPO3\CMS\Backend\RecordList\Event\ModifyRecordListRecordActionsEvent;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Event listener for DatabaseRecordList, implementing the icons for impersonating a frontend user
 */
final readonly class RecordListRecordActionsListener
{
    public function __construct(
        protected FlashMessageService $flashMessageService,
        protected IconFactory $iconFactory,
        protected SiteFinder $siteFinder,
        protected UriBuilder $uriBuilder
    ) {}

    /**
     * @param ModifyRecordListRecordActionsEvent $event
     * @throws Exception
     */
    #[AsEventListener(identifier: 'tx-impersonate-modify-record-list-record-actions', event: ModifyRecordListRecordActionsEvent::class)]
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
     * @throws Exception
     */
    protected function addImpersonateButton(array $userRow): string
    {
        try {
            $siteIdentifier = $this->siteFinder->getSiteByPageId((int)$userRow['pid'])->getIdentifier();
        } catch (SiteNotFoundException) {
            $firstSite = current(array_slice($this->siteFinder->getAllSites(), 0, 1));
            if (!$firstSite instanceof Site) {
                throw new SiteNotFoundException('No sites found', 1778246174);
            }
            $siteIdentifier = $firstSite->getIdentifier();
        } catch (\Exception) {
            $messageQueue = $this->flashMessageService->getMessageQueueByIdentifier();
            // "hacky way" to make sure the flash message is only shown once and not for every user record in the list module
            $messageQueue->clear(ContextualFeedbackSeverity::ERROR);
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                $this->translate('error.no_site_found'),
                'Impersonate',
                ContextualFeedbackSeverity::ERROR
            );
            $messageQueue->enqueue($flashMessage);
            return '';
        }
        $userUid = (int)$userRow['uid'];

        $uri = $this->buildFrontendLoginUri($siteIdentifier, $userUid);

        $buttonText = $this->translate('button.impersonate');
        $iconMarkup = $this->iconFactory->getIcon('actions-system-backend-user-switch', IconSize::SMALL)->render();

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
        return (string)$this->uriBuilder->buildUriFromRoute('impersonate_frontendlogin', ['site' => $siteIdentifier, 'user' => $userUid]);
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
