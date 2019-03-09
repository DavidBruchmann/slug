<?php

declare(strict_types=1);

namespace GOCHILLA\Slug\Controller;

use GOCHILLA\Slug\Utility\HelperUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * Ajax class for slug module
 *
 * This class contains all functions to perform the ajax requests
 *
 * @category   Module
 * @package    Slug
 * @author     Simon Köhler <info@simon-koehler.com>
 * @copyright  2018-2019 GOCHILLA s.a.
 */

class AjaxController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {
    
    /**
     * @var HelperUtility
     */
    protected $helper;
    
    /**
     * function savePageSlug
     *
     * @return void
     */
    public function savePageSlug(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response)
    {
        $queryParams = $request->getQueryParams();
        $queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable('pages');
        $this->helper = GeneralUtility::makeInstance(HelperUtility::class);
        $slug = $this->helper->returnUniqueSlug('page', $queryParams['slug'], $queryParams['uid']);
        $statement = $queryBuilder
            ->update('pages')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($queryParams['uid'],\PDO::PARAM_INT))
            )
            ->set('slug',$slug) // Function "createNamedParameter" is NOT needed here!
            ->execute();
        $responseInfo['status'] = $statement;
        $responseInfo['slug'] = $slug;
        $response->getBody()->write(json_encode($responseInfo));
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
    
    /**
     * function saveNewsSlug
     *
     * @return void
     */
    public function saveNewsSlug(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response)
    {
        $queryParams = $request->getQueryParams();
        $queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable('tx_news_domain_model_news');
        $this->helper = GeneralUtility::makeInstance(HelperUtility::class);
        $slug = $this->helper->returnUniqueSlug('news', $queryParams['slug'], $queryParams['uid']);
        $statement = $queryBuilder
            ->update('tx_news_domain_model_news')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($queryParams['uid'],\PDO::PARAM_INT))
            )
            ->set('path_segment',$slug) // Function "createNamedParameter" is NOT needed here!
            ->execute();
        $responseInfo['status'] = $statement;
        $responseInfo['slug'] = $slug;
        $response->getBody()->write(json_encode($responseInfo));
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
    
    /**
     * function slugExists
     *
     * @return void
     */
    public function slugExists(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response)
    {
        $queryParams = $request->getQueryParams();
        $queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable('pages');
        $result = $queryBuilder
            ->count('slug')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('slug', $queryBuilder->createNamedParameter($queryParams['slug']))
            )
            ->execute()
            ->fetchColumn(0);
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }
    
    /**
     * function generatePageSlug
     *
     * @return void
     */
    public function generatePageSlug(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response)
    {
        $fieldConfig = $GLOBALS['TCA']['pages']['columns']['slug']['config'];
        $slugHelper = GeneralUtility::makeInstance(SlugHelper::class, 'pages', 'slug', $fieldConfig);
        $this->helper = GeneralUtility::makeInstance(HelperUtility::class);
        $queryParams = $request->getQueryParams();
        $queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable('pages');
        $statement = $queryBuilder
            ->select('*')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($queryParams['uid'],\PDO::PARAM_INT))
            )
            ->execute();
        while ($row = $statement->fetch()) {
            $slugGenerated = $slugHelper->generate($row, $row['pid']);
            break;
        }
        $slug = $this->helper->returnUniqueSlug('page', $slugGenerated, $row['uid']);
        $responseInfo['status'] = $statement;
        $responseInfo['slug'] = $slug;
        $response->getBody()->write(json_encode($responseInfo));
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
      
    }
    
    /**
     * function generateNewsSlug
     *
     * @return void
     */
    public function generateNewsSlug(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response)
    {
        $fieldConfig = $GLOBALS['TCA']['tx_news_domain_model_news']['columns']['path_segment']['config'];
        $slugHelper = GeneralUtility::makeInstance(SlugHelper::class, 'tx_news_domain_model_news', 'path_segment', $fieldConfig);
        $this->helper = GeneralUtility::makeInstance(HelperUtility::class);
        $queryParams = $request->getQueryParams();
        $queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable('pages');
        $statement = $queryBuilder
            ->select('*')
            ->from('tx_news_domain_model_news')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($queryParams['uid'],\PDO::PARAM_INT))
            )
            ->execute();
        while ($row = $statement->fetch()) {
            $slugGenerated = $slugHelper->generate($row, $row['pid']);
            break;
        }
        $slug = $this->helper->returnUniqueSlug('news', $slugGenerated, $row['uid']);
        $responseInfo['status'] = $statement;
        $responseInfo['slug'] = $slug;
        $response->getBody()->write(json_encode($responseInfo));
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
      
    }
    
    /**
     * function loadTreeItemSlugs
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param string $mode ['HTML' | 'TEMPLATE' | 'JSON' ]
     *               $mode = 'HTML' is deprecated to remove HTML from this class
     *
     * @return void
     */
    public function loadTreeItemSlugs(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $mode = 'HTML')
    {
        $queryParams = $request->getQueryParams();
        $this->helper = GeneralUtility::makeInstance(HelperUtility::class);
        $translations = $this->helper->getPageTranslationsByUid($queryParams['uid']);
        $root = BackendUtility::getRecord('pages',$queryParams['uid']);
        $parentPage = BackendUtility::getRecord('pages',$root['pid']);
        $languages = $this->helper->getLanguages();
        
        if ($mode == 'HTML') {
            $html .= '<!-- Main Language start -->';
            $html .= '<div class="well">';
            $html .= '<h2>'.$root['title'].' <small>'.$root['seo_title'].'</small></h2>';
            $html .= '<div class="input-group">'
                    . '<span class="input-group-addon"><i class="fa fa-globe"></i></span>'
                    . '<input type="text" data-uid="'.$root['uid'].'" value="'.$root['slug'].'" class="form-control slug-input page-'.$root['uid'].'">'
                    . '<span class="input-group-btn"><button data-uid="'.$root['uid'].'" id="savePageSlug-'.$root['uid'].'" class="btn btn-default savePageSlug" title="Save slug"><i class="fa fa-save"></i></button></span>'
                    . '</div>';
            $rootline = [];
            $html .=  '<div class="info-toggle"><span style="border:1px solid #888;">+</span> Details</div><div class="info">';
            $rootline[] = $this->getPageInfoHtml($root, $headline = 'This page');
            if($parentPage) {
                while ($parentPage['uid']){
                    $rootline[] = $this->getPageInfoHtml($parentPage);
                    $parentPage = BackendUtility::getRecord('pages',$parentPage['pid']);
                }
                $html .=  '<h3>Rootline</h3>' . implode('<hr>',$rootline);
            }
            $html .=  '</div>';
            $html .= '<!-- Main Language end -->';

            if (is_array($translations) && count($translations)) {
                $html .= '<!-- TRANSLATIONS start -->';
                $counts = [];
                foreach ($translations as $isoLanguageCode => $page) {
                    $counts[] = $isoLanguageCode;
                    $parentPage = $this->helper->getPageTranslationsByUid($page['pid'])[$isoLanguageCode];
                    $html .= '<!-- TRANSLATION '.$isoLanguageCode.' - ' . $page['sys_language_uid'] . ' start -->';
                    foreach ($languages as $value) {
                        if($value['uid'] === $page['sys_language_uid']){
                            $icon = $value['language_isocode'];
                        }
                    }
                    $html .= '<h3>'.$page['title'].' <small>'.$page['seo_title'].'</small></h3>';
                    $html .= '<div class="input-group">'
                        . '<span class="input-group-addon">'.$icon.'</span>'
                        . '<input type="text" data-uid="'.$page['uid'].'" value="'.$page['slug'].'" class="form-control slug-input page-'.$page['uid'].'">'
                        . '<span class="input-group-btn"><button data-uid="'.$page['uid'].'" id="savePageSlug-'.$page['uid'].'" class="btn btn-default savePageSlug" title="Save slug"><i class="fa fa-save"></i></button></span>'
                        . '</div>';
                    $html .=  '<div class="info-toggle"><span style="border:1px solid #888;">+</span> Details</div><div class="info">';
                    $rootline = [];
                    if ($page['sys_language_uid'] !== '0' && $isoLanguageCode === $this->helper->getIsoCodeByLanguageUid($page['sys_language_uid'])) {
                        $rootline[] = $this->getPageInfoHtml($page, $headline = 'This page');
                        if($parentPage) {
                            $n=0;
                            while ($parentPage['uid']){
                                # if ($parentPage['sys_language_uid'] == ) {
                                $rootline[] = $this->getPageInfoHtml($parentPage);
                                $parentPage = $this->helper->getPageTranslationsByUid($parentPage['pid'])[$isoLanguageCode];
                                $n++;
                            }
                     
                            $html .=  '<h3>Rootline</h3>' . implode('<hr>',$rootline);
                        }
                        $html .=  '</div>';
                        $html .= '<!-- TRANSLATION '.$isoLanguageCode.' end -->';
                    }
                }
                $html .= '<!-- TRANSLATIONS end -->';
            }
            $html .= '</div>$counts:'.implode(',',$counts);
            $response->getBody()->write($html);
            return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
        }
        elseif ($mode == "TEMPLATE") {
            // TODO: 
            // - load template and assign variables
            // - parse Template
            // - return parsed Template

            // return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
        }
        elseif ($mode == "JSON") {
            // TODO: 
            // create JSON-objects and arrays
            // return JSON

            // return $response->withHeader('Content-Type', 'text/json; charset=utf-8');
        }
        
    }
    
    protected function getPageInfoHtml(array $page, $headline = 'Next page up')
    {
        $html = '<h4>' . $headline . '</h4>'
            . '<table>'
            . '<tr><th>sys_language_uid</th><td>'.$page['sys_language_uid'].'</td></tr>'
            . '<tr><th>title</th><td>'.$page['title'].'</td></tr>'
            . '<tr><th>subtitle</th><td>'.$page['subtitle'].'</td></tr>'
            . '<tr><th>nav_title</th><td>'.$page['nav_title'].'</td></tr>'
            . '<tr><th>slug (currently)</th><td>'.$page['slug'].'</td></tr>'
            . '<tr><th>seo_title</th><td>'.$page['seo_title'].'</td></tr>'
            . '</table>';
        return $html;
    }
    
}