<?php
namespace GOCHILLA\Slug\Utility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Site\SiteFinder;

/* 
 * This file was created by Simon Köhler
 * at GOCHILLA s.a.
 * www.gochilla.com
 */

class HelperUtility {
    
    
    // Get Extension Manager configuration from the ext_emconf.php of any extension
    public function getEmConfiguration($extKey) {
        
        $fileName = 'EXT:'.$extKey.'/ext_emconf.php';
        $filePath = GeneralUtility::getFileAbsFileName($fileName);
        
        if(file_exists($filePath)){
            include $filePath;
            return $EM_CONF[$_EXTKEY];
        }
        else{
            return false;
        }
        
    }
    
    
    // Gets the correct flag icon for any given language uid
    public function getFlagIconByLanguageUid($sys_language_uid) {
        foreach ($this->getLanguages() as $value) {
            if($value['uid'] === $sys_language_uid){
                $output = $value['flag'];
                break;
            }
            else{
                $output = $value['flag'];
                break;
            }
        }
        return $output;
    }
    
    
    // Get all languages
    public function getLanguages(){        
        $queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable('sys_language');
        $statement = $queryBuilder
            ->select('*')
            ->from('sys_language')
            ->execute();
        $output = array();
        while ($row = $statement->fetch()) {
            array_push($output, $row);
        }
        return $output;
    }
    
    
    public function getIsoCodeByLanguageUid($sys_language_uid) {
        foreach ($this->getLanguages() as $value) {
            if($value['uid'] === $sys_language_uid){
                $output = $value['language_isocode'];
                break;
            }
            elseif($sys_language_uid === 0){
                $output = '';
                break;
            }
        }
        return $output;
    }
    
    
    public function getPageTranslationsByUid($uid){
        $queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable('pages');
        $statement = $queryBuilder
            ->select('*')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('l10n_parent', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT))
             )
            ->execute();
        $output = array();
        while ($row = $statement->fetch()) {
            array_push($output, $row);
        }
        return $output;
    }
    
    
    public function getLangKey($key) {
        return \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($key,'slug');
    }
    
}