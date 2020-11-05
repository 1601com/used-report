<?php

/*
 * This file is part of the used-report-bundle.
 *
 * (c) Agentur1601com
 *
 * @license MIT
 */

namespace Agentur1601com\UsedReport\Service\CheckUsage\Database;

use Doctrine\DBAL\DBALException;

class CheckUsageImageDatabaseSingle extends AbstractCheckUsageImageDatabase
{
    /**
     * @var array
     */
    protected static $_searchableTableColumns = ['singleSRC'];

    /**
     * @throws DBALException
     */
    protected function _getEntriesUsingImage(string $uuid, array $searchableTableColumns): ?array
    {
        return array_filter($this->_searchEntriesUsingImageByTable($uuid, $searchableTableColumns, 'SELECT * FROM `%s` WHERE `%s` = ?'));
    }
}
