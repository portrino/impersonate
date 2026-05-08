<?php

/**
 * Definitions for routes provided by EXT:impersonate
 */
return [
    'impersonate_frontendlogin' => [
        'path' => '/impersonate/login',
        'target' => \Portrino\Impersonate\Controller\FrontendLoginController::class . '::loginAction',
    ],
];
