<?php

/*
 * This file is part of the used-report-bundle.
 *
 * (c) Agentur1601com
 *
 * @license MIT
 */

namespace Agentur1601com\UsedReport\Controller;

use Agentur1601com\UsedReport\Service\CheckUsage\Database\CheckUsageImageDatabaseInsertTag;
use Agentur1601com\UsedReport\Service\CheckUsage\Database\CheckUsageImageDatabaseMulti;
use Agentur1601com\UsedReport\Service\CheckUsage\Database\CheckUsageImageDatabaseSingle;
use Agentur1601com\UsedReport\Service\CheckUsage\File\CheckUsageImageFileInsertTag;
use Agentur1601com\UsedReport\Service\CheckUsage\File\CheckUsageImageFileStyleSheet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment as TwigEnvironment;

/**
 * Handles back end routes.
 */
class ShowAjaxController extends AbstractController
{
    const JSON_KEY_ID = 'id';
    const JSON_KEY_DATA = 'data';

    /**
     * @var RequestStack
     */
    private $_requestStack;

    /**
     * @var CheckUsageImageDatabaseSingle
     */
    private $_dbSingle;

    /**
     * @var CheckUsageImageDatabaseMulti
     */
    private $_dbMulti;

    /**
     * @var CheckUsageImageDatabaseInsertTag
     */
    private $_dbInsertTag;

    /**
     * @var CheckUsageImageFileInsertTag
     */
    private $_fileInsertTag;

    /**
     * @var CheckUsageImageFileStyleSheet
     */
    private $_fileStyleSheet;

    /**
     * @var TwigEnvironment
     */
    private $_twigEnvironment;

    /**
     * @var RouterInterface
     */
    private $_routerInterface;
    /** @noinspection PhpUndefinedClassInspection */

    /**
     * ShowAjaxController constructor.
     */
    public function __construct(
        RequestStack $_requestStack, CheckUsageImageDatabaseSingle $_dbSingle, CheckUsageImageDatabaseMulti $_dbMulti, TwigEnvironment $_twigEnvironment, RouterInterface $_routerInterface, CheckUsageImageDatabaseInsertTag $_dbInsertTag, CheckUsageImageFileInsertTag $_fileInsertTag, CheckUsageImageFileStyleSheet $_fileStyleSheet
    ) {
        $this->_requestStack = $_requestStack;
        $this->_dbSingle = $_dbSingle;
        $this->_dbMulti = $_dbMulti;
        $this->_dbInsertTag = $_dbInsertTag;
        $this->_fileInsertTag = $_fileInsertTag;
        $this->_fileStyleSheet = $_fileStyleSheet;
        $this->_twigEnvironment = $_twigEnvironment;
        $this->_routerInterface = $_routerInterface;
    }

    /**
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function ajaxRequest(): Response
    {
        if (!($json = json_decode($this->_requestStack->getCurrentRequest()->request->get(self::JSON_KEY_DATA), true))) {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        if (!isset($json[self::JSON_KEY_ID])) {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        $domainUrl = $this->_requestStack->getCurrentRequest()->server->get('HTTP_ORIGIN');
        $requestToken = $this->_requestStack->getCurrentRequest()->get('REQUEST_TOKEN');
        $previewRoute = $this->_routerInterface->generate('contao_backend_preview');

        $this->_dbSingle->execute($json);
        $this->_dbMulti->execute($json);
        $this->_dbInsertTag->execute($json);
        $this->_fileInsertTag->execute($json);
        $this->_fileStyleSheet->execute($json);

        return new Response($this->_twigEnvironment->render(
            '@UsedReport/status.html.twig',
            [
                'result' => array_merge($this->_dbMulti->getResult(), $this->_dbSingle->getResult(), $this->_dbInsertTag->getResult(), $this->_fileInsertTag->getResult(), $this->_fileStyleSheet->getResult()),
                'domainUrl' => $domainUrl,
                'requestToken' => $requestToken,
                'previewRoute' => $previewRoute,
            ]
        ));
    }
}
