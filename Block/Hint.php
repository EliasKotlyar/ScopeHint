<?php
namespace FireGento\ScopeHint\Block;
use Magento\Framework\View\Element\Template\Context;
class Hint   extends \Magento\Framework\View\Element\Template 
{
    /** @var array */
    protected $_fullStoreNames = array();
    protected $registry;
    protected $product;
    protected $category;


    public function __construct(Context $context,\Magento\Framework\Registry $registry,\Magento\Catalog\Model\Product $product,\Magento\Catalog\Model\Category $category,array $data = [])
    {
        parent::__construct($context, $data);
        $this->product = $product;
        $this->category = $category;
        $this->registry = $registry;


    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $changedScopes = array();

        if ($this->_isStoreScope()) return '';

        if ($this->_isWebsiteScope()) {

            $website = $this->getWebsite();
            $changedScopes = $this->_getChangedStoresForWebsite($website);
        }

        if ($this->_isGlobalScope()) {

            $changedScopes = $this->_getChangedScopesForGlobal();
        }

        if (empty($changedScopes)) return '';

        return $this->_getHintHtml($changedScopes);
    }

    /**
     * @return string
     */
    protected function _getConfigCode()
    {
        $configCode = preg_replace('#\[value\](\[\])?$#', '', $this->getElement()->getName());
        $configCode = str_replace('[fields]', '', $configCode);
        $configCode = str_replace('groups[', '[', $configCode);
        $configCode = str_replace('][', '/', $configCode);
        $configCode = str_replace(']', '', $configCode);
        $configCode = str_replace('[', '', $configCode);
        $configCode = $this->_request->getParam('section') . '/' . $configCode;
        return $configCode;
    }

    /**
     * @param Mage_Core_Model_Website $website
     * @return array
     */
    protected function _getChangedStoresForWebsite($website)
    {
        $changedStores = array();

        foreach ($website->getStores() as $store) {

            /** @var Mage_Core_Model_Store $store */
            if ($this->_isValueChanged($store, $website)) {

                $changedStores[__('Store View: %s', $this->_getFullStoreName($store))] = $this->_getReadableConfigValue($store);
            }
        }
        return $changedStores;
    }

    /**
     * @return array
     */
    protected function _getChangedScopesForGlobal()
    {
        $changedScopes = array();

        switch ($this->getType()) {
            case 'config':

                foreach ($this->_storeManager->getWebsites() as $website) {

                    /** @var Mage_Core_Model_Website $website */
                    if ($this->_isValueChanged($website)) {

                        $changedScopes[(string)__('Website: %1', $website->getName())] =  $this->_getReadableConfigValue($website);
                    }

                    foreach ($website->getStores() as $store) {

                        /** @var Mage_Core_Model_Store $store */
                        if ($this->_isValueChanged($store, $website)) {

                            $changedScopes[(string)__('Store View: %1', $this->_getFullStoreName($store))] = $this->_getReadableConfigValue($store);
                        }
                    }
                }
                break;

            case 'product':
            case 'category':

                foreach ($this->_storeManager->getStores() as $store) {

                    /** @var Mage_Core_Model_Store $store */
                    if ($this->_isValueChanged($store)) {

                        $changedScopes[(string)__('Store View: %s', $this->_getFullStoreName($store))] = $this->_getReadableConfigValue($store);
                    }
                }
                break;
        }

        return $changedScopes;
    }

    /**
     * @param Mage_Core_Model_Store|Mage_Core_Model_Website $scope1
     * @param Mage_Core_Model_Website|null $scope2
     * @return bool
     */
    protected function _isValueChanged($scope1, $scope2 = null)
    {
        if ($this->getType() != 'config' && $scope1 instanceof \Magento\Store\Api\Data\WebsiteInterface) {
            // products and categories don't have a website scope
            return false;
        }
        $scope1ConfigValue = $this->_getValue($scope1);
        $scope2ConfigValue = $this->_getValue($scope2);

        return ($scope1ConfigValue != $scope2ConfigValue);
    }

    /**
     * @param Mage_Core_Model_Store|Mage_Core_Model_Website|null $scope
     * @return string
     */
    protected function _getValue($scope)
    {
        switch ($this->getType()) {

            case 'config':
                $configCode = $this->_getConfigCode();

                if (is_null($scope)) {
                    return $this->_scopeConfig->getValue(
                        $configCode,
                        'default'
                    );

                } else if ($scope instanceof \Magento\Store\Api\Data\StoreInterface) {

                    return $this->_scopeConfig->getValue(
                        $configCode,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $scope->getCode()
                    );
                } else if ($scope instanceof \Magento\Store\Api\Data\WebsiteInterface) {
                    return $this->_scopeConfig->getValue(
                        $configCode,
                        \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                        $scope->getCode()
                    );
                }
                break;

            case 'product':
                $attributeName = $this->getElement()->getData('name');
                if (is_null($scope)) {
                    $value = $this->_getProduct()->getData($attributeName);
                    if (is_array($value)) {
                        if (is_array($value[0])) {
                            return '';
                        }
                        return implode(',', $value);
                    }
                    return $value;
                } else if ($scope instanceof \Magento\Store\Api\Data\StoreInterface) {
                    $value = $this->_getProduct($scope)->getData($attributeName);
                    if (is_array($value)) {
                        if (is_array($value[0])) {
                            return '';
                        }
                        return implode(',', $value);
                    }
                    return $value;
                }
                break;

            case 'category':
                $attributeName = $this->getElement()->getData('name');
                if (is_null($scope)) {
                    $value = $this->_getCategory()->getData($attributeName);
                    if (is_array($value)) {
                        return implode(',', $value);
                    }
                    return $value;
                } else if ($scope instanceof \Magento\Store\Api\Data\StoreInterface) {
                    $value = $this->_getCategory($scope)->getData($attributeName);
                    if (is_array($value)) {
                        return implode(',', $value);
                    }
                    return $value;
                }
                break;
        }
    }

    /**
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @return \Magento\Catalog\Model\Product
     */
    protected function _getProduct(\Magento\Store\Api\Data\StoreInterface $store = null)
    {
        if (is_null($store)) {
            $storeId = 0;
        } else {
            $storeId = $store->getId();
        }

        if (is_null($this->registry->registry('product_' . $storeId))) {
            /** @var $product \Magento\Catalog\Model\Product */
            $product = $this->product;
            $product->setStoreId($storeId);
            $this->registry->register('product_' . $storeId, $product->load($this->getEntityId()));
        }

        return $this->registry->registry('product_' . $storeId);
    }

    /**
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @return \Magento\Catalog\Model\Category
     */
    protected function _getCategory(\Magento\Store\Api\Data\StoreInterface $store = null)
    {
        if (is_null($store)) {
            $storeId = 0;
        } else {
            $storeId = $store->getId();
        }

        if (is_null($this->registry->registry('category_' . $storeId))) {
            /** @var $category \Magento\Catalog\Model\Category */
            $category = $this->category;
            $category->setStoreId($storeId);
            $this->registry->register('category_' . $storeId, $category->load($this->getEntityId()));
        }

        return $this->registry->registry('category_' . $storeId);
    }

    /**
     * @param \Magento\Store\Api\Data\StoreInterface|Mage_Core_Model_Website|null $scope
     * @return string
     */
    protected function _getReadableConfigValue($scope)
    {
        $rawValue = $this->_getValue($scope);
        $values = $this->getElement()->getValues();
        if ($this->getElement()->getType() == 'select') {

            if ($this->getElement()->getExtType() == 'multiple') {

                $readableValues = array();
                $rawValues = explode(',', $rawValue);
                foreach($values as $value) {
                    if (in_array($value['value'], $rawValues)) {
                        $readableValues[] = $value['label'];
                    }
                }
                return implode(', ', $readableValues);
            } else {
                foreach($values as $value) {
                    if (isset($value['value']) && $value['value'] == $rawValue) {
                        return $value['label'];
                    }
                }
            }
        }
        return $rawValue;
    }

    /**
     * @param array $changedScopes
     * @return string
     */
    protected function _getHintHtml($changedScopes)
    {
        $text = __('Changes in:') . '<br />';

        foreach($changedScopes as $scope => $scopeValue) {

            $text .= $this->escapeHtml($scope). ':'
                . '<br />'
                . nl2br(wordwrap($this->escapeHtml($scopeValue)))
                . '<br />';
        }

        //$iconurl = $this->_storeManager->getStore()->getBaseUrl('skin') . 'adminhtml/default/default/images/note_msg_icon.gif';
        $iconurl = $this->getViewFileUrl('images/note_msg_icon.gif');
        $html = '<img class="scopehint-icon" src="' . $iconurl . '" title="' . $text . '" alt="' . $text . '"/>';

        return $html;
    }

    /**
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @return string
     */
    protected function _getFullStoreName($store)
    {
        if (!isset($this->_fullStoreNames[$store->getId()])) {

            $fullStoreName = $store->getWebsite()->getName()
                . ' / ' . $store->getGroup()->getName()
                . ' / ' . $store->getName();
            $this->_fullStoreNames[$store->getId()] = $fullStoreName;
        }
        return $this->_fullStoreNames[$store->getId()];
    }

    /**
     * @return bool
     */
    protected function _isGlobalScope()
    {
        return (!$this->_isWebsiteScope() && !$this->_isStoreScope());
    }

    /**
     * @return bool
     */
    protected function _isWebsiteScope()
    {
        return ($this->_request->getParam('website') && !$this->_isStoreScope());
    }

    /**
     * @return bool
     */
    protected function _isStoreScope()
    {
        return ((bool)$this->_request->getParam('store'));
    }

    /**
     * @return Mage_Core_Model_Website
     */
    protected function getWebsite()
    {
        $websiteCode = $this->_request->getParam('website');
        return $this->_storeManager->getWebsite($websiteCode);
    }

    /**
     * @return int
     */
    protected function getEntityId()
    {
        if ($this->getType() == 'product') {
            return intval($this->_request->getParam('id'));
        } else {
            return $this->registry->registry('current_category')->getId();
        }
    }

}
