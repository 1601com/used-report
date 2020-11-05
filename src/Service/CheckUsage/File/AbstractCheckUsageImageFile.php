<?php

/*
 * This file is part of the used-report-bundle.
 *
 * (c) Agentur1601com
 *
 * @license MIT
 */

namespace Agentur1601com\UsedReport\Service\CheckUsage\File;

use Agentur1601com\UsedReport\Service\CheckUsage\AbstractCheckUsage;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractCheckUsageImageFile extends AbstractCheckUsage
{
    const ENTRY_TYPE_FILE = 'file';
    const DATA_REQUEST_KEY_ID = 'id';

    /**
     * @var KernelInterface
     */
    protected $_kernelInterface;

    /**
     * AbstractCheckUsageImageFile constructor.
     */
    public function __construct(Connection $_connection, KernelInterface $_kernelInterface)
    {
        parent::__construct($_connection);
        $this->_kernelInterface = $_kernelInterface;
    }

    abstract protected function _getEntriesUsingImage(string $uuid): ?array;

    /**
     * @retMurn bool
     *
     * @throws DBALException
     *
     * @return bool
     */
    protected function _execute(array $data): ?bool
    {
        if (!($uuid = $this->_getUuid($data[self::DATA_REQUEST_KEY_ID]))) {
            trigger_error(sprintf('No uuid for image: %s', $data[self::DATA_REQUEST_KEY_ID]), E_USER_WARNING);

            return false;
        }

        if (!($entriesUsingImages = $this->_getEntriesUsingImage($uuid))) {
            // return true: there does not have to be an entry which uses the given image
            return true;
        }

        return true;
    }

    protected function _getTemplatesUsingInsertTagsByUuid(string $uuid): ?array
    {
        $rootDir = $this->_kernelInterface->getProjectDir();
        $templateDir = sprintf('%s/%s', $rootDir, 'templates');
        $templateDirectoryIterator = new \RecursiveDirectoryIterator($templateDir);

        foreach (new \RecursiveIteratorIterator($templateDirectoryIterator) as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $absolutePathToTemplateFile = sprintf('%s/%s', $file->getPath(), $file->getFilename());

            exec(sprintf('grep %s %s', escapeshellarg(StringUtil::binToUuid($uuid)), escapeshellarg($absolutePathToTemplateFile)), $output, $returnValue);

            // if return value is 1: grep did not find anything
            if ($returnValue && 1 !== $returnValue) {
                trigger_error(sprintf('could not grep insertTag for file %s; Return-Value: %d', $absolutePathToTemplateFile, $returnValue), E_USER_WARNING);

                return null;
            }

            if (1 === $returnValue) {
                continue;
            }

            $this->_result[] =
                [
                    'entryId' => $absolutePathToTemplateFile,
                    'tableName' => null,
                    'doElement' => null,
                    'entryType' => self::ENTRY_TYPE_FILE,
                    'elementType' => null,
                    'parentData' => [],
                    'insertTagTemplate' => strstr($absolutePathToTemplateFile, '/templates/'),
                ];
        }

        return $this->_result;
    }
}
