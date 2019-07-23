<?php

namespace DCKAP\Hubspot\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    
    protected $_storeManager;

	const XML_PATH_HUBSPOT_ENABLED = 'hubspot/general/enable';
    const XML_PATH_HUBSPOT_API_KEY = 'hubspot/general/apikey';
    
    public function __construct(
    \Magento\Framework\App\Helper\Context $context,
    \Magento\Store\Model\StoreManagerInterface $storeManager    
    ) {
    parent::__construct($context);
    $this->_storeManager = $storeManager;
    }
    
	protected function getConfigValue($field, $storeId = null)
	{
		return $this->scopeConfig->getValue(
			$field, ScopeInterface::SCOPE_STORE, $storeId
		);
	}

	public function getEnabled()
	{
		return $this->getConfigValue(self::XML_PATH_HUBSPOT_ENABLED , $this->getCurrentStoreId());
	}
    
    public function getApiKey(){
        
        return $this->getConfigValue(self::XML_PATH_HUBSPOT_API_KEY, $this->getCurrentStoreId());
    }
    
    public function getCurrentStoreId() {
        return $this->_storeManager->getStore()->getStoreId(); 
    }
    
    public function getCurrentWebsiteId() {
        return $this->_storeManager->getWebsite()->getWebsiteId();
    }
    
    public function getMapping($type){
        $fields = [];
        switch($type) {
            case 'customer':
                $fields =   [
                            "email"=>"email",
                            "firstname"=>"firstname",
                            "lastname"=>"lastname",
                            "dob"=>"date_of_birth",
                            "gender"=>"gender"
                            ];
                break;
            case 'customerAddress':
                $fields =   [
                            "firstname"=>"firstname",
                            "lastname"=>"lastname",
                            "company"=>"company",
                            "telephone"=>"phone",
                            "street"=>"address",
                            "city"=>"city",
                            "region"=>"state",
                            "postcode"=>"zip"
                            ];
                break;
            case 'customerAddressRequiredFields':
                $fields =   [
                            "firstname"=>"firstname",
                            "lastname"=>"lastname",
                            "telephone"=>"phone",
                            "street"=>"address",
                            "city"=>"city",
                            "postcode"=>"zip"
                            ];
                break;
            
        }
        return $fields;
    }
    
    public function errorLog() {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/hubspot_error.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        return $logger;
    }
}
