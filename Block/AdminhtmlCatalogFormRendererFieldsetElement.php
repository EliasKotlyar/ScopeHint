<?php
/**
 * *
 *  * Copyright © Elias Kotlyar - All rights reserved.
 *  * See LICENSE.md bundled with this module for license details.
 *  
 */


namespace FireGento\ScopeHint\Block;

use \Magento\Backend\Block\Template\Context;
class AdminhtmlCatalogFormRendererFieldsetElement
    extends  \Magento\Catalog\Block\Adminhtml\Form\Renderer\Fieldset\Element
{
    protected $registry;
    public function __construct(Context $context,\Magento\Framework\Registry $registry,  array $data = [])
    {
        parent::__construct($context, $data);
        $this->registry = $registry;
    }

    /**
     * Retrieve label of attribute scope
     *
     * GLOBAL | WEBSITE | STORE
     *
     * @return string
     */
    public function getScopeLabel()
    {
        $html = parent::getScopeLabel();

        $html .= '<div class="scopehint" style="padding: 6px 6px 0 6px; display: inline-block;">';
        $html .= $this->_getScopeHintHtml($this->getElement());
        $html .= '</div>';


        return $html;
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getScopeHintHtml($element)
    {
        if ($this->registry->registry('current_category')) {
            $type = 'category';
        } else {
            $type = 'product';
        }
        return $this->getLayout()
            ->createBlock('FireGento\ScopeHint\Block\Hint', 'scopehint_'.$this->getElement()->getId())
            ->setElement($element)
            ->setType($type)
            ->toHtml();
    }
}