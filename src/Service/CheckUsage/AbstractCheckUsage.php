<?php

/*
 * This file is part of the used-report-bundle.
 *
 * (c) Agentur1601com
 *
 * @license MIT
 */

namespace Agentur1601com\UsedReport\Service\CheckUsage;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;

abstract class AbstractCheckUsage
{
    /**
     * @var array
     */
    protected $_result = [];

    /**
     * @var Connection
     */
    protected $_connection;

    /**
     * AbstractCheckUsage constructor.
     */
    public function __construct(Connection $_connection)
    {
        $this->_connection = $_connection;
    }

    /**
     * @return bool
     */
    public function execute(array $data): ?bool
    {
        if (!$this->_execute($data)) {
            trigger_error('Can not check usage', E_USER_WARNING);

            return false;
        }

        return true;
    }

    public function getResult(): array
    {
        return $this->_result;
    }

    /**
     * @return bool
     */
    abstract protected function _execute(array $data): ?bool;

    /**
     * @throws DBALException
     *
     * @return array|null
     */
    protected function _getUuid(string $imageId): ?string
    {
        if (!($uuid = $this->_connection->executeQuery('SELECT `uuid` FROM `tl_files` WHERE `path` = ?', [$imageId])->fetchAll())) {
            trigger_error(sprintf('No result found for imageId: %s', $imageId), E_USER_WARNING);

            return null;
        }

        return $uuid[0]['uuid'];
    }
}
