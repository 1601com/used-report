<?php

$GLOBALS['TL_DCA']['tl_used_report'] = [
    'config' => [
        'dataContainer'               => 'File',
        'closed'                      => true
    ],
    'palettes' => [
        'default'                     => '{ur_settings},ur_simultaneous_loading;'
    ],
    'fields' => [
        'ur_simultaneous_loading' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_used_report']['ur_simultaneous_loading'],
            'inputType'               => 'text',
            'eval'                    => array('tl_class'=>'w50', 'rgxp'=>'natural')
        ],
    ]
];
