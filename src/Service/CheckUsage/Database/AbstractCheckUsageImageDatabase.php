<?php

/*
 * This file is part of the used-report-bundle.
 *
 * (c) Agentur1601com
 *
 * @license MIT
 */

namespace Agentur1601com\UsedReport\Service\CheckUsage\Database;

use Agentur1601com\UsedReport\Service\CheckUsage\AbstractCheckUsage;
use Contao\ArticleModel;
use Contao\CalendarEventsModel;
use Contao\CalendarModel;
use Contao\ContentModel;
use Contao\NewsModel;
use Contao\PageModel;
use Doctrine\DBAL\DBALException;

abstract class AbstractCheckUsageImageDatabase extends AbstractCheckUsage
{
    const CONFIG_KEY_ELEMENT_PROCESSING_TYPE = 'processingType';
    const CONFIG_KEY_ELEMENT_PROCESSING_TYPE_VALUE_CLASS = 'class';
    const CONFIG_KEY_ELEMENT_CLASS = 'class';
    const CONFIG_KEY_ELEMENT_CALLBACK = 'callback';
    const CONFIG_KEY_ELEMENT_PREVIEW_DATA_CALLBACK = 'callback';
    const CONFIG_KEY_ELEMENT_ENTRY_ELEMENT_KEY = 'entryElementIdKey';
    const CONFIG_KEY_ELEMENT_PARENT_ELEMENT_KEY = 'parentElementIdKey';
    const CONFIG_KEY_ELEMENT_ENTRY_PUBLISHED = 'published';

    const ENTRY_TYPE_DATABASE = 'database';

    /**
     * @var array
     */
    protected static $_searchableTableColumns = [];

    private $_tableElementConfig = [
        'tl_content' => [
            'news' => [
                self::CONFIG_KEY_ELEMENT_PROCESSING_TYPE => self::CONFIG_KEY_ELEMENT_PROCESSING_TYPE_VALUE_CLASS,
                self::CONFIG_KEY_ELEMENT_CLASS => ContentModel::class,
                self::CONFIG_KEY_ELEMENT_ENTRY_ELEMENT_KEY => 'id',
                self::CONFIG_KEY_ELEMENT_PARENT_ELEMENT_KEY => 'pid',
                self::CONFIG_KEY_ELEMENT_ENTRY_PUBLISHED => 'invisible',
            ],
            'calendar' => [
                self::CONFIG_KEY_ELEMENT_PROCESSING_TYPE => self::CONFIG_KEY_ELEMENT_PROCESSING_TYPE_VALUE_CLASS,
                self::CONFIG_KEY_ELEMENT_CLASS => ContentModel::class,
                self::CONFIG_KEY_ELEMENT_ENTRY_ELEMENT_KEY => 'id',
                self::CONFIG_KEY_ELEMENT_PARENT_ELEMENT_KEY => 'pid',
                self::CONFIG_KEY_ELEMENT_ENTRY_PUBLISHED => 'invisible',
            ],
            'article' => [
                self::CONFIG_KEY_ELEMENT_PROCESSING_TYPE => self::CONFIG_KEY_ELEMENT_CALLBACK,
                self::CONFIG_KEY_ELEMENT_PREVIEW_DATA_CALLBACK => '_callbackGetContentModel',
            ],
        ],
        'tl_news' => [
            'news' => [
                self::CONFIG_KEY_ELEMENT_PROCESSING_TYPE => self::CONFIG_KEY_ELEMENT_PROCESSING_TYPE_VALUE_CLASS,
                self::CONFIG_KEY_ELEMENT_CLASS => NewsModel::class,
                self::CONFIG_KEY_ELEMENT_ENTRY_ELEMENT_KEY => 'id',
                self::CONFIG_KEY_ELEMENT_PARENT_ELEMENT_KEY => 'id',
                self::CONFIG_KEY_ELEMENT_ENTRY_PUBLISHED => 'published',
            ],
        ],
        'tl_calendar' => [
            'calendar' => [
                self::CONFIG_KEY_ELEMENT_PROCESSING_TYPE => self::CONFIG_KEY_ELEMENT_PROCESSING_TYPE_VALUE_CLASS,
                self::CONFIG_KEY_ELEMENT_CLASS => CalendarModel::class,
                self::CONFIG_KEY_ELEMENT_ENTRY_ELEMENT_KEY => 'id',
                self::CONFIG_KEY_ELEMENT_PARENT_ELEMENT_KEY => 'id',
                self::CONFIG_KEY_ELEMENT_ENTRY_PUBLISHED => 'invisible',
            ],
        ],
        'tl_calendar_events' => [
            'calendar' => [
                self::CONFIG_KEY_ELEMENT_PROCESSING_TYPE => self::CONFIG_KEY_ELEMENT_PROCESSING_TYPE_VALUE_CLASS,
                self::CONFIG_KEY_ELEMENT_CLASS => CalendarEventsModel::class,
                self::CONFIG_KEY_ELEMENT_ENTRY_ELEMENT_KEY => 'id',
                self::CONFIG_KEY_ELEMENT_PARENT_ELEMENT_KEY => 'id',
                self::CONFIG_KEY_ELEMENT_ENTRY_PUBLISHED => 'published',
            ],
        ],
        'tl_module' => [
            'themes' => [
                self::CONFIG_KEY_ELEMENT_PROCESSING_TYPE => self::CONFIG_KEY_ELEMENT_CALLBACK,
                self::CONFIG_KEY_ELEMENT_PREVIEW_DATA_CALLBACK => '_callbackGetContentModel',
            ],
        ],
    ];

    abstract protected function _getEntriesUsingImage(string $uuid, array $searchableTableColumns): ?array;

    /**
     * @retMurn bool
     *
     * @throws DBALException
     *
     * @return bool
     */
    protected function _execute(array $data): ?bool
    {
        if (!($uuid = $this->_getUuid($data['id']))) {
            trigger_error(sprintf('No uuid for image: %s', $data['id']), E_USER_WARNING);

            return false;
        }

        if (!($searchableTableColumns = $this->_getSearchableTableColumns())) {
            trigger_error('No searchable table columns found', E_USER_WARNING);

            return false;
        }

        if (!($entriesUsingImages = $this->_getEntriesUsingImage($uuid, $searchableTableColumns))) {
            // return true: there does not have to be an entry which uses the given image
            return true;
        }

        $this->_prepareImageDataResults($entriesUsingImages);

        return true;
    }

    protected function _getSearchableTableColumns(): array
    {
        $class = static::class;
        $query = $this->_connection->createQueryBuilder();
        $query = $query->select('a.COLUMN_NAME')
            ->addSelect('a.TABLE_NAME')
            ->from('INFORMATION_SCHEMA.COLUMNS', 'a')
            ->where('a.COLUMN_NAME IN (:requestedColumnName)')
            ->setParameter('requestedColumnName', $class::$_searchableTableColumns, $this->_connection::PARAM_STR_ARRAY);

        return $query->execute()->fetchAll();
    }

    /**
     * @throws DBALException
     */
    protected function _searchEntriesUsingImageByTable(string $uuid, array $searchableTableColumns, string $queryString): ?array
    {
        $entriesUsingImage = [];

        foreach ($searchableTableColumns as $tableColumn) {
            if (!$result = $this->_connection->executeQuery(sprintf($queryString, $tableColumn['TABLE_NAME'], $tableColumn['COLUMN_NAME']), [$uuid])->fetchAll()) {
                continue;
            }
            $entriesUsingImage[$tableColumn['TABLE_NAME']][$tableColumn['COLUMN_NAME']] = $result;
        }

        return $entriesUsingImage;
    }

    protected function _prepareImageDataResults(array $entriesUsingImage): ?array
    {
        foreach ($entriesUsingImage as $tableName => $tableData) {
            foreach ($tableData as $columnName => $value) {
                if (!empty($value)) {
                    foreach ($value as $entry) {
                        $doElement = ($entry['ptable']) ? str_replace('tl_', '', $entry['ptable']) : str_replace('tl_', '', $tableName);
                        if ('calendar_events' === $doElement) {
                            $doElement = 'calendar';
                        }
                        if ('module' === $doElement) {
                            $doElement = 'themes';
                        }

                        $this->_result[] = array_merge(
                            [
                                'entryId' => $entry['id'],
                                'tableName' => $tableName,
                                'doElement' => $doElement,
                                'entryType' => self::ENTRY_TYPE_DATABASE,
                                'elementType' => $entry['type'],
                                'parentData' => $this->_getPreviewData($entry, $tableName, $doElement),
                            ]);
                    }
                }
            }
        }

        return $this->_result;
    }

    private function _getPreviewData(array $entry, string $tableName, string $doElement): ?array
    {
        $previewData = [
            'previewUrlFragment' => $this->_getPreviewUrlFragmentByDoElement($doElement),
            'previewId' => '',
            'previewPublished' => null,
            'isModule' => false,
        ];

        if (!isset($this->_tableElementConfig[$tableName])) {
            trigger_error(sprintf('No config found for table name %s', $tableName), E_USER_WARNING);

            return null;
        }
        if (!isset($this->_tableElementConfig[$tableName][$doElement])) {
            trigger_error(sprintf('No config found for do element %s', $doElement), E_USER_WARNING);

            return null;
        }

        $config = $this->_tableElementConfig[$tableName][$doElement];
        switch ($config[self::CONFIG_KEY_ELEMENT_PROCESSING_TYPE]) {
            case self::CONFIG_KEY_ELEMENT_CLASS:
                $previewData['previewId'] = $config[self::CONFIG_KEY_ELEMENT_CLASS]::findById($entry[$config[self::CONFIG_KEY_ELEMENT_ENTRY_ELEMENT_KEY]])->{$config[self::CONFIG_KEY_ELEMENT_PARENT_ELEMENT_KEY]};

                $previewData['previewPublished'] = (bool) ($config[self::CONFIG_KEY_ELEMENT_CLASS]::findById($entry[$config[self::CONFIG_KEY_ELEMENT_ENTRY_ELEMENT_KEY]])->{$config[self::CONFIG_KEY_ELEMENT_ENTRY_PUBLISHED]});
                if ('invisible' === $config[self::CONFIG_KEY_ELEMENT_ENTRY_PUBLISHED]) {
                    $previewData['previewPublished'] = !$previewData['previewPublished'];
                }
                break;
            case self::CONFIG_KEY_ELEMENT_PREVIEW_DATA_CALLBACK:
                $callback = [$this, $config[self::CONFIG_KEY_ELEMENT_PREVIEW_DATA_CALLBACK]];
                if (!\is_callable($callback)) {
                    trigger_error(sprintf('%s is not callable', $config[self::CONFIG_KEY_ELEMENT_PREVIEW_DATA_CALLBACK]), E_USER_WARNING);

                    return null;
                }

                $previewData = array_merge($previewData, $callback($entry));
                break;
            default:
                trigger_error(sprintf('no processing type %s', $config[self::CONFIG_KEY_ELEMENT_PROCESSING_TYPE]), E_USER_WARNING);

                return null;
        }

        return $previewData;
    }

    private function _getPreviewUrlFragmentByDoElement(string $doElement): string
    {
        $previewElement = explode('_', $doElement)[0];
        $supportedBaseElements = [
            'calendar' => '',
            'news' => '',
        ];
        if (!isset($supportedBaseElements[$previewElement])) {
            return 'page';
        }

        return $previewElement;
    }

    private function _callbackGetContentModel(array $entry): ?array
    {
        $previewData = [];

        if ('html' === $entry['type'] || 'randomImage' === $entry['type']) {
            $previewData['isModule'] = true;
        }

        $contentModel = ContentModel::findById($entry['id']);
        $previewData['previewId'] = PageModel::findById(ArticleModel::findById($contentModel->pid)->pid)->id;

        if (PageModel::findById(ArticleModel::findById($contentModel->pid)->pid)->published) {
            if (ArticleModel::findById($entry['pid'])->published) {
                $previewData['previewPublished'] = !ContentModel::findById($entry['id'])->invisible;
            }
        }

        return $previewData;
    }
}
