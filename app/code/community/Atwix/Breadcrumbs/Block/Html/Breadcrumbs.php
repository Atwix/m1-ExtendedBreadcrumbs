<?php
/**
 * Atwix
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.

 * @category    Atwix Mod
 * @package     Atwix_Breadcrumbs
 * @author      Atwix Core Team
 * @copyright   Copyright (c) 2014 Atwix (http://www.atwix.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Atwix_Breadcrumbs_Block_Html_Breadcrumbs  extends Mage_Page_Block_Html_Breadcrumbs
{
    /**
     * Array of breadcrumbs
     *
     * array(
     *  [$index] => array(
     *                  ['label']
     *                  ['title']
     *                  ['link']
     *                  ['first']
     *                  ['last']
     *              )
     * )
     *
     * @var array
     */
    protected $_crumbs = null;

    function _construct()
    {
        parent::_construct();
        $this->setTemplate('page/html/breadcrumbs.phtml');
    }

    function addCrumb($crumbName, $crumbInfo, $after = false)
    {
        if ($after) {
            $this->_prepareArray($crumbInfo, array('label', 'title', 'link', 'first', 'last', 'readonly'));
            if (!is_array($this->_crumbs)){
                $this->_crumbs = array();
            }

            if (isset($this->_crumbs[$after])) {
                $position = array_search($after, array_keys($this->_crumbs)) + 1;
                $this->_crumbs = array_slice($this->_crumbs, 0, $position, true) +
                    array($crumbName => $crumbInfo) +
                    array_slice($this->_crumbs, $position, count($this->_crumbs) - 1, true);
            } else {
                $this->_crumbs = array($crumbName => $crumbInfo) + $this->_crumbs;
            }
        } else {
            $this->_prepareArray($crumbInfo, array('label', 'title', 'link', 'first', 'last', 'readonly'));
            if ((!isset($this->_crumbs[$crumbName])) || (!$this->_crumbs[$crumbName]['readonly'])) {
                $this->_crumbs[$crumbName] = $crumbInfo;
            }
        }
        return $this;
    }

    protected function _toHtml()
    {
        $enabled = (bool) Mage::getStoreConfig('atwix_breadcrumbs/atwix_breadcrumbs/enabled');
        if(!$enabled) {
            return parent::_toHtml();
        }

        $this->prepareCrumbs();
        if (is_array($this->_crumbs)) {
            reset($this->_crumbs);
            $this->_crumbs[key($this->_crumbs)]['first'] = true;
            end($this->_crumbs);
            $this->_crumbs[key($this->_crumbs)]['last'] = true;
        }
        $this->assign('crumbs', $this->_crumbs);
        return parent::_toHtml();
    }

    /*
     * Check if product exists in the breadcrumbs
     * IF exists THEN get full path to product ELSE return default breadcrumbs
     *
     * @return array
     */
    protected function prepareCrumbs()
    {
        $crumbs = $this->_crumbs;
        $_helper = Mage::helper('atwix_breadcrumbs');
        if ($_helper->isItProductPage()  && !$_helper->hasCurrentCategory()) {
            $catPath = $_helper->getCategoryPath();
            foreach ($crumbs as $_crumbName => $_crumbInfo) {
                if (
                strstr($_crumbName, 'product')
                ) {
                    unset($crumbs[$_crumbName]);
                }
            }
            $crumbs += $catPath;
        }
        $this->_crumbs = $crumbs;
    }
}
