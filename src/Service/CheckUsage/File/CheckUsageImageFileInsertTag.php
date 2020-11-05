<?php

/*
 * This file is part of the used-report-bundle.
 *
 * (c) Agentur1601com
 *
 * @license MIT
 */

namespace Agentur1601com\UsedReport\Service\CheckUsage\File;

class CheckUsageImageFileInsertTag extends AbstractCheckUsageImageFile
{
    protected function _getEntriesUsingImage(string $uuid): ?array
    {
        return $this->_getTemplatesUsingInsertTagsByUuid($uuid);
    }
}
