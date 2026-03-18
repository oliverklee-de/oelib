<?php

defined('TYPO3') || die('Access denied.');

return [
    'ctrl' => [
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'versioningWS' => false,
        'delete' => 'deleted',
        'hideTable' => true,
        'adminOnly' => true,
    ],
    'columns' => [
        'title' => [
            'config' => [
                'type' => 'none',
            ],
        ],
        'parent' => [
            'l10n_mode' => 'exclude',
            'exclude' => true,
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'tx_oelib_parent2' => [
            'l10n_mode' => 'exclude',
            'exclude' => true,
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'tx_oelib_parent3' => [
            'l10n_mode' => 'exclude',
            'exclude' => true,
            'config' => [
                'type' => 'passthrough',
            ],
        ],
    ],
    'types' => [
        ['showitem' => ''],
    ],
];
