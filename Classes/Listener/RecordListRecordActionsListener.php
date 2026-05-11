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
use Psr\Http\Message\ServerRequestInterface;
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
use TYPO3\CMS\Core\Routing\BackendEntryPointResolver;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Event listener for DatabaseRecordList, implementing the icons for impersonating a frontend user
 */
final readonly class RecordListRecordActionsListener
{
    public function __construct(
        protected BackendEntryPointResolver $backendEntryPointResolver,
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
        // get the hostname of the current backend user login session for checks below
        $beSessionUri = $this->backendEntryPointResolver->getUriFromRequest($this->getRequest());
        $beSessionHost = $beSessionUri->getHost();

        try {
            $site = $this->siteFinder->getSiteByPageId((int)$userRow['pid']);
            if ($site->getBase()->getHost() !== $beSessionHost) {
                $this->renderFlashMessage('warning.backend_user_host_site_mismatch');
            }
            $siteIdentifier = $site->getIdentifier();
        } catch (SiteNotFoundException) {
            $sites = $this->siteFinder->getAllSites();
            $siteFallback = null;
            foreach ($sites as $site) {
                if ($site->getBase()->getHost() === $beSessionHost) {
                    $siteFallback = $site;
                    break;
                }
            }
            if ($siteFallback === null) {
                throw new SiteNotFoundException('No matching site found', 1778246174);
            }
            $siteIdentifier = $siteFallback->getIdentifier();
        } catch (\Exception) {
            $this->renderFlashMessage('error.no_site_found', ContextualFeedbackSeverity::ERROR);
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

    protected function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }

    protected function renderFlashMessage(string $locallangKey, ContextualFeedbackSeverity $severity = ContextualFeedbackSeverity::WARNING): void
    {
        $messageQueue = $this->flashMessageService->getMessageQueueByIdentifier();
        // "hacky way" to make sure the flash message is only shown once and not for every user record in the list module
        $messageQueue->clear($severity);
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            $this->translate($locallangKey),
            'Impersonate',
            $severity
        );
        $messageQueue->enqueue($flashMessage);
    }
}
