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
    'version' => '5.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
