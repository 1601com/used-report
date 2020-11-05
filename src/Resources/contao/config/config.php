<?php

use Contao\CoreBundle\Util\PackageUtil;

const BUNDLE_NAME = 'usedreport';

$stylesheet = 'status.css';

if(!class_exists(PackageUtil::class, true))
{
    $stylesheet = 'status_c44.css';
}

// javascript and stylesheet
$GLOBALS['BE_MOD']['system']['files']['stylesheet'] = [sprintf('bundles/%s/style/%s', BUNDLE_NAME, $stylesheet)];
$GLOBALS['BE_MOD']['system']['files']['javascript'] = [sprintf('bundles/%s/javascript/useReport.js', BUNDLE_NAME)];

// used-report backend menu entry
$GLOBALS['BE_MOD']['agentur1601com']['used_report'] = [
    'tables' => ['tl_used_report'],
];

// language output for backend entry
$GLOBALS['TL_LANG']['MOD']['used_report'] = ['Used-Report', 'Used-Report-Bundle'];