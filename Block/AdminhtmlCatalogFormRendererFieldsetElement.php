<?php
/**
 * *
 *  * Copyright © Elias Kotlyar - All rights reserved.
 *  * See LICENSE.md bundled with this module for license details.
 *  
 */

namespace Firegento\Scopehint\Block;
class AdminhtmlCatalogFormRendererFieldsetElement
    extends  \Magento\Catalog\Block\Adminhtml\Form\Renderer\Fieldset\Element
{
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
        if (Mage::registry('current_category')) {
            $type = 'category';
        } else {
            $type = 'product';
        }
        return $this->getLayout()
            ->createBlock('scopehint/hint', 'scopehint')
            ->setElement($element)
            ->setType($type)
            ->toHtml();
    }
}