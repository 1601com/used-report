<?php

namespace Agentur1601com\UsedReport\Resources\Contao\Dca;

use Agentur1601com\UsedReport\EventListener\CheckImageStatusListener;

// registering of the contao callback
$GLOBALS['TL_DCA']['tl_files']['list']['operations']['status'] =
    [
        'button_callback'     => [CheckImageStatusListener::class, 'tlFilesStatusListingOperation']
    ];

// global operation button for synchronizing the used report
$GLOBALS['TL_DCA']['tl_files']['list']['global_operations']['syncUsedReport'] =
    [
        'label'               => ['UseReport', 'Synchronize the use report'],
        'href'                => 'key=update',
        'class'               => 'ur_sync',
    ];