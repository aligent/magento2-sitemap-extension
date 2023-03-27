<?php
/**
 * Aligent Consulting
 * Copyright (c) Aligent Consulting (https://www.aligent.com.au)
 */

declare(strict_types=1);

namespace Aligent\Sitemap\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class Fields extends AbstractFieldArray
{
    /**
     * @var bool
     */
    protected $_addAfter = true;

    /**
     * @var string
     */
    protected $_addButtonLabel;

    /**
     * Construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare to render the columns
     *
     * @return void
     */
    protected function _prepareToRender(): void
    {
        $this->addColumn('url_key', ['label' => __('Url Key')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
