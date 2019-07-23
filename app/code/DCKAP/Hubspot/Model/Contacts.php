<?php

namespace DCKAP\Hubspot\Model;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

class Contacts extends \Magento\Framework\Model\AbstractModel
{
    
    protected $helperData;
    protected $client;
    
    const  BASE_CONTACT_ENDPOINT = "contacts/v1/contact";
    const  STARSALES_CUSTOMER_LIST_ID = 5;
    const  NEWSLETTER_GENERAL = 9;
    const  NEWSLETTER_PRODUCT = 8;
    const  NEWSLETTER_OFFER = 10;
    
    public function __construct(
        Context $context,
        Registry $registry,
        \DCKAP\Hubspot\Helper\Data $helperData,
        \DCKAP\Hubspot\Model\Client $client
    ) {
        $this->helperData = $helperData;
        $this->client = $client;
        parent::__construct($context,$registry);
    }
    
    public function createContact(array $data){
        
        $endpoint = self::BASE_CONTACT_ENDPOINT;
        return $this->writeRequest($endpoint,$data);
    }
    
    public function updateContactByEmail($emailId, array $data){
        $endpoint = self::BASE_CONTACT_ENDPOINT . "/email/" . $emailId . "/profile";
        return $this->writeRequest($endpoint,$data);
    }
    
    public function updateContactByVid($vid, array $data){
        $endpoint = self::BASE_CONTACT_ENDPOINT . "/vid/" . $vid . "/profile";
        return $this->writeRequest($endpoint,$data);
    }
    
    public function getContactByEmail($emailId,array $filters = []) {
        
        $filters["propertyMode"] = "value_only";
        $endpoint = self::BASE_CONTACT_ENDPOINT . "/email/" . $emailId . "/profile";
        return $this->readRequest($endpoint,$filters);
        
    }
    
    public function isContactExist($emailId){
        $filters = array("property"=>"email");
        $result = $this->getContactByEmail($emailId,$filters);

        if($result && isset($result["properties"]["email"]["value"]) ){
            if(!empty($result["status"])){
                if($result["status"] != "error"){
                    return $emailId == $result["properties"]["email"]["value"];
                }else{
                    return false;
                }
            }
            return $emailId == $result["properties"]["email"]["value"];
        }
        else {
            return false;   
        }
    }
    
    public function addContactToList($listId,$email=null,$vid=null){
        $emailArr = explode(' ',$email);
        $vidArr = explode(' ',$vid);
        $data = [];
        if($vid && !empty($vidArr)){
            $data["vids"] =  $vidArr;
        }
        
        if($email && !empty($emailArr)){
            $data["emails"] =  $emailArr;
        }
       
        if(!empty($data)){
            $endpoint = "contacts/v1/lists/" . $listId . "/add";
            return $this->writeRequest($endpoint,$data);
        }
    }
    
    public function removeContactFromList($listId, $vid){
        $vidArr = explode(' ',$vid);
        $data = [];
        if($vid && !empty($vidArr)){
            $data["vids"] =  $vidArr;
            $endpoint = "contacts/v1/lists/" . $listId . "/remove";
            return $this->writeRequest($endpoint,$data);
        }
        
    }
    
    
    protected function writeRequest($endpoint, array $data){
        
        if($this->helperData->getEnabled()){
            try{
                $result = $this->client->postRequest($endpoint,$data);
                if(isset($result["status"]) && $result["status"] == 'error'){
                    return false;
                }
                else if(is_array($result)){
                    return $result;
                }
                else if(!$this->client->getError()){
                        return true;
                }
            }
            catch(\Exception $e){
                return false;
            }
        }
        else {
            
            return false;
        }
        
        return false;
    }
    
    
    protected function readRequest($endpoint,array $filters = []){
        if($this->helperData->getEnabled()){
            try{
                $result = $this->client->getRequest($endpoint,$filters);
                if(!$result || (isset($result["status"]) && $result["status"] == 'error')){
                    return false;
                }
                else if(is_array($result)){
                    return $result;
                }
            }
            catch(\Exception $e){
                return false;
            }
        }
        else {
            return false;   
        }
    }
    
}