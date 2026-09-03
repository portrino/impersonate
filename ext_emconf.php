<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "impersonate"
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'Impersonate',
    'description' => 'Impersonate frontend users from inside the TYPO3 Backend.',
    'category' => 'misc',
    'author' => 'Christian Eßl, Axel Böswetter',
    'author_email' => 'indy.essl@gmail.com, boeswetter@portrino.de',
    'state' => 'stable',
    'version' => '6.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '14.3.0-14.3.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
