<?php

/*
 * This file is part of the used-report-bundle.
 *
 * (c) Agentur1601com
 *
 * @license MIT
 */

namespace Agentur1601com\UsedReport\Service\CheckUsage\File;

abstract class AbstractCheckUsageImageFileStyle extends AbstractCheckUsageImageFile
{
    const ALLOWED_FILE_EXTENSIONS = ['css' => '', 'scss' => '', 'less' => ''];

    protected function _execute(array $data): ?bool
    {
        if (!($styleSheetsUsingImages = $this->_getEntriesUsingImage($data['fileNameEncoded']))) {
            // return true: there does not have to be an entry which uses the given image
            return true;
        }

        return true;
    }

    protected function _getStyleFilesUsingImagesByImageName(string $fileName): ?array
    {
        $rootDir = $this->_kernelInterface->getProjectDir();
        $templateDir = sprintf('%s/%s', $rootDir, 'files');
        $templateDirectoryIterator = new \RecursiveDirectoryIterator($templateDir);

        foreach (new \RecursiveIteratorIterator($templateDirectoryIterator) as $file) {
            if (!$file->isFile() || !isset(self::ALLOWED_FILE_EXTENSIONS[$file->getExtension()])) {
                continue;
            }
            $absolutePathToTemplateFile = sprintf('%s/%s', $file->getPath(), $file->getFilename());

            exec(sprintf('grep %s %s', escapeshellarg($fileName), escapeshellarg($absolutePathToTemplateFile)), $output, $returnValue);

            // if return value is 1: grep did not find anything
            if ($returnValue && 1 !== $returnValue) {
                trigger_error(sprintf('could not grep StyleSheet for file %s; Return-Value: %d', $absolutePathToTemplateFile, $returnValue), E_USER_WARNING);

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
                    'entryType' => 'styleSheet',
                    'elementType' => null,
                    'parentData' => [],
                    'styleSheetUsingImage' => strstr($absolutePathToTemplateFile, '/files/'),
                ];
        }

        return $this->_result;
    }
}
