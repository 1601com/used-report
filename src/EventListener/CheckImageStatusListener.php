<?php

/*
 * This file is part of the used-report-bundle.
 *
 * (c) Agentur1601com
 *
 * @license MIT
 */

namespace Agentur1601com\UsedReport\EventListener;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class CheckImageStatusListener
{
    const ALLOWED_IMAGE_EXTENSIONS = ['png' => '', 'jpg' => '', 'jpeg' => '', 'svg' => '', 'svgz' => '', 'gif' => '', 'bmp' => '', 'ico' => ''];

    /**
     * @var RouterInterface
     */
    private $_router;

    /**
     * @var RequestStack
     */
    private $_requestStack;

    /**
     * CheckImageStatusListener constructor.
     */
    public function __construct(RouterInterface $_router, RequestStack $_requestStack)
    {
        $this->_router = $_router;
        $this->_requestStack = $_requestStack;
    }

    /**
     * @param $arrRow
     * @param $href
     * @param $label
     * @param $title
     * @param $icon
     * @param $attributes
     * @param $strTable
     * @param $arrRootIds
     * @param $arrChildRecordIds
     * @param $blnCircularReference
     * @param $strPrevious
     * @param $strNext
     *
     * @return string
     */
    public function tlFilesStatusListingOperation($arrRow, $href, $label, $title, $icon, $attributes, $strTable, $arrRootIds, $arrChildRecordIds, $blnCircularReference, $strPrevious, $strNext)
    {
        // only trigger the callback if it is not a popup-window
        if (!preg_match('/popup=1/', $this->_requestStack->getCurrentRequest()->getRequestUri()) && 'update' === $this->_requestStack->getCurrentRequest()->get('key')) {
            $fileId = $arrRow['id'];
            $ext = mb_strtolower(pathinfo($fileId)['extension']);

            // only trigger the ajax if it is not a folder or illegal file extension
            if ('folder' === $arrRow['type'] || !isset(self::ALLOWED_IMAGE_EXTENSIONS[$ext])) {
                return '';
            }

            $rand = uniqid('ur', false);

            $max_simultaneous_requests = 2;

            if ($GLOBALS['TL_CONFIG']['ur_simultaneous_loading']) {
                $max_simultaneous_requests = $GLOBALS['TL_CONFIG']['ur_simultaneous_loading'];
            }

            return '            
                <div id="'.$rand.'"></div>
                
                <script>                
                    var dataObj = {
                        elementId: "'.$rand.'",
                        data: '.json_encode($arrRow).',
                        url: "'.$this->_router->generate('agentur1601com_used_report_show_ajax_requests').'",
                        token: "'.\RequestToken::get().'",
                        maxRequest: '.$max_simultaneous_requests.',
                    }
                   
                    addRequest(dataObj);
                </script>';
        }
    }
}
