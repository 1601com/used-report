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

class CheckUsageImageDatabaseMulti extends AbstractCheckUsageImageDatabase
{
    /**
     * @var array
     */
    protected static $_searchableTableColumns = ['multiSRC'];

    /**
     * @throws DBALException
     */
    protected function _getEntriesUsingImage(string $uuid, array $searchableTableColumns): ?array
    {
        $entriesUsingImage = $this->_searchEntriesUsingImageByTable(sprintf('%%%s%%', $uuid), $searchableTableColumns, 'SELECT * FROM `%s` WHERE `%s` LIKE ?');

        return array_filter($entriesUsingImage);
    }
}
