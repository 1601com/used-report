<?php

/*
 * This file is part of the used-report-bundle.
 *
 * (c) Agentur1601com
 *
 * @license MIT
 */

namespace Agentur1601com\UsedReport\Service\CheckUsage\File;

class CheckUsageImageFileStyleSheet extends AbstractCheckUsageImageFileStyle
{
    protected function _getEntriesUsingImage(string $fileName): ?array
    {
        return $this->_getStyleFilesUsingImagesByImageName($fileName);
    }
}
