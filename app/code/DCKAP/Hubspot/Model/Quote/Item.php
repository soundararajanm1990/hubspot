<?php

namespace DCKAP\Hubspot\Model\Quote;


class Item extends \Magento\Quote\Model\Quote\Item
{
    public function compareOptions($options1, $options2)
    {
        $this->_notRepresentOptions = ['info_buyRequest','additional_options'];
        return parent::compareOptions($options1, $options2);
    }
    
}
