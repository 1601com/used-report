<?php

/*
 * This file is part of the used-report-bundle.
 *
 * (c) Agentur1601com
 *
 * @license MIT
 */

namespace Agentur1601com\UsedReport\Service\CheckUsage\Database;

use Contao\StringUtil;
use Doctrine\DBAL\DBALException;

class CheckUsageImageDatabaseInsertTag extends AbstractCheckUsageImageDatabase
{
    /**
     * @var array
     */
    protected static $_searchableTableColumns = ['text', 'html'];

    /**
     * @throws DBALException
     */
    protected function _getEntriesUsingImage(string $uuid, array $searchableTableColumns): ?array
    {
        return array_filter($this->_searchEntriesUsingImageByTable(sprintf('%%%s%%', StringUtil::binToUuid($uuid)), $searchableTableColumns, 'SELECT * FROM `%s` WHERE `%s` LIKE ?'));
    }
}
