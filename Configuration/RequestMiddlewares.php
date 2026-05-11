<?php

return [
    'frontend' => [
        'portrino/impersonate/authentication' => [
            'target' => \Portrino\Impersonate\Middleware\FrontendUserAuthenticator::class,
            'before' => [
                'typo3/cms-frontend/authentication',
            ],
        ],
        'portrino/impersonate/redirecthandler' => [
            'target' => \Portrino\Impersonate\Middleware\RedirectHandler::class,
            'after' => [
                'typo3/cms-frontend/authentication',
            ],
            'before' => [
                'typo3/cms-redirects/redirecthandler',
            ],
        ],
    ],
];
