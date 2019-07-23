<?php

namespace DCKAP\Hubspot\Model;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

class Deal extends \Magento\Framework\Model\AbstractModel
{
    
    protected $helperData;
    protected $client;
    
    const  BASE_DEAL_ENDPOINT = "deals/v1/deal";
    const  GROUP_DEAL_ENDPOINT = "deals/v1/batch-async";
    const  DEAL_PIPELINE_ID = "c9a6b863-f0c1-4a10-a43f-9dd9bd730aa2";
    const  DEAL_STAGE_1 = "449305";
    const  DEAL_STAGE_2 = "e75bcbe9-6fbf-4262-8bc4-8fa29b3c7798";
    const  DEAL_STAGE_3 = "98b6cb53-961f-4227-b222-dbc266d9f930";
    const  DEAL_STAGE_4 = "58c8142b-6906-4024-a230-eb884cdf0a2c";
    
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
    
    public function createDeal(array $data){
        
        $endpoint = self::BASE_DEAL_ENDPOINT;
        return $this->writeRequest($endpoint,$data);
    }
    
    public function updateDeal($dealId, array $data){
        $endpoint = self::BASE_DEAL_ENDPOINT . "/" . $dealId;
        return $this->putRequest($endpoint,$data);
    }

    public function deleteDeal($dealId){        
        $endpoint = self::BASE_DEAL_ENDPOINT . "/" . $dealId;
        return $this->deleteRequest($endpoint);
    }
    
   /* public function associateDealContact($dealId,$vid){
        $endpoint = self::BASE_DEAL_ENDPOINT . "/" . $dealId . "/associations/CONTACT";
        $data  = array('id' => $vid);
        return $this->putRequest($endpoint,$data);
    }*/

    public function getAssociateDeal($vid) {        
       
        $endpoint = self::BASE_DEAL_ENDPOINT . "/associated/contact/" . $vid . "/paged";
        
        /* $data  = array('includeAssociations' => true,
                        'limit' => 100,
                        'properties' => 'dealname' );*/
        $data = array('properties' => 'dealname,dealstage');
        return $this->readRequest($endpoint,$data);        
    }
    
    public function updateGroupDeal(array $data){
        
        $endpoint = self::GROUP_DEAL_ENDPOINT . "/update";
        return $this->writeRequest($endpoint,$data);
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
    
    protected function readRequest($endpoint,array $data){
        if($this->helperData->getEnabled()){
            try{
                $result = $this->client->getRequest($endpoint,$data);
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

    protected function deleteRequest($endpoint){
        if($this->helperData->getEnabled()){
            try{
                $result = $this->client->deleteRequest($endpoint);
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
    
    protected function putRequest($endpoint, array $data){

                
        if($this->helperData->getEnabled()){
            try{
                $result = $this->client->putRequest($endpoint,$data);
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
    
}