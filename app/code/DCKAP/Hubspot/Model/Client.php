<?php

namespace DCKAP\Hubspot\Model;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

class Client extends \Magento\Framework\Model\AbstractModel
{
     /**
     * @var \Magento\Framework\HTTP\Adapter\Curl
     */
    protected $_curl;
    protected $jsonHelper;
    protected $helperData;
    
    const HUBSPOT_BASE_URL = "https://api.hubapi.com/";
    /**
     * @param Context $context
     * @param \Magento\Framework\HTTP\Adapter\Curl $curl
     */
    public function __construct(
        Context $context,
        Registry $registry,
        \Magento\Framework\HTTP\Adapter\Curl $curl,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \DCKAP\Hubspot\Helper\Data $helperData
    ) {
        $this->_curl = $curl;
        $this->jsonHelper = $jsonHelper;
        $this->helperData = $helperData;
        parent::__construct($context,$registry);
    }
     
    public function getRequest($endpoint, array $data) {
        

        if(!$this->helperData->getEnabled()){
            return false;
        }
        
        $url = $this->createUrl($endpoint) . $this->buildQueryString($data);
        $this->_curl->setConfig(array("header"=>false));
        $this->_curl->write('GET', $url);
        $response = $this->_curl->read();

        if(!$this->_curl->getError() && $response){
            return $this->jsonHelper->jsonDecode($response);
        }
        else {
            $logger = $this->helperData->errorLog();
            $logger->info('URL:'.$url);
            $logger->info('Method: GET');
            $logger->info(print_r($data, true));
            throw new \Exception("Request failed");
        }
        return $this->jsonHelper->jsonDecode($response);
    }
    
    public function postRequest($endpoint,array $data=null) {
        
        if(!$this->helperData->getEnabled()){
            return false;
        }
        
        $body = json_encode($data);
        $url = $this->createUrl($endpoint);
        $headers = [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($body)
        ];
        $this->_curl->setConfig(array("header"=>false));
        $this->_curl->write('POST', $url, $http_ver = '1.1', $headers, $body);
        $response = $this->_curl->read();


        if(!$this->_curl->getError() && $response){
            return $this->jsonHelper->jsonDecode($response);
        }
        else {
            $logger = $this->helperData->errorLog();
            $logger->info('URL:'.$url);
            $logger->info('Method: POST');
            $logger->info(print_r($body, true));
            throw new \Exception("Request failed");
        }
    }


    public function deleteRequest($endpoint) {
            
        
        if(!$this->helperData->getEnabled()){
            return false;
        }
        
        $url = $this->createUrl($endpoint);
        $this->_curl->setConfig(array("header"=>false));
        $this->_curl->setOptions(array(CURLOPT_CUSTOMREQUEST=>\Zend_Http_Client::DELETE));
        $this->_curl->write('', $url);
        $response = $this->_curl->read();

        if(!$this->_curl->getError()){
            return true;
        }
        else {
            $logger = $this->helperData->errorLog();
            $logger->info('URL:'.$url);
            $logger->info('Method: DELETE');
            throw new \Exception("Request failed");
        }
    }
    
    public function putRequest($endpoint,array $data=null) {
        
        if(!$this->helperData->getEnabled()){
            return false;
        }
        
        $body = json_encode($data);
        $url = $this->createUrl($endpoint);
        $headers = [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($body)
        ];
        $this->_curl->setConfig(array("header"=>false));
        $this->_curl->write('PUT', $url, $http_ver = '1.1', $headers, $body);
        $response = $this->_curl->read();

       
        if(!$this->_curl->getError() && $response){
            return $this->jsonHelper->jsonDecode($response);
        }
        else {
            $logger->info('URL:'.$url);
            $logger->info('Method: PUT');
            $logger->info(print_r($body, true));
            throw new \Exception("Request failed");
        }
    }
   

    protected function createUrl($endpoint){
        $hapikey = $this->helperData->getApiKey();
        $hubspotBaseUrl = self::HUBSPOT_BASE_URL;
        return  $hubspotBaseUrl . $endpoint . "?hapikey=" . $hapikey;
    }
    
    public function getError(){
       return $this->_curl->getError();
    }
    
    protected function buildQueryString(array $data){
        $paramString = '';
        foreach($data as $param=>$value){
            foreach(explode(',',$value) as $subvalues){
                $paramString .= '&' . $param . "=" . $subvalues;
            }
        }
        return $paramString;
    }
    
}
