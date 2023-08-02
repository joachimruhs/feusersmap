<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'FeUsersMap',
    'description' => 'OpenstreetMap for fe_users',
    'category' => 'plugin',
    'author' => 'Joachim Ruhs',
    'author_email' => 'postmaster@joachim-ruhs.de',
    'state' => 'beta',
    'clearCacheOnLoad' => 0,
    'version' => '1.4.1',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-12.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
