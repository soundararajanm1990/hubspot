<?php

namespace DCKAP\Hubspot\Model;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\AddressFactory;

class ManageCustomers extends \Magento\Framework\Model\AbstractModel
{
    
    protected $helperData;
    protected $contacts;
    protected $customerRepository;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
 
    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $addressFactory;
    
    
 
    
    public function __construct(
        Context $context,
        Registry $registry,
        CustomerFactory $customerFactory,
        AddressFactory $addressFactory,
        \DCKAP\Hubspot\Helper\Data $helperData,
        \DCKAP\Hubspot\Model\Contacts $contacts,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->helperData = $helperData;
        $this->contacts = $contacts;
        $this->customerFactory  = $customerFactory;
        $this->addressFactory   = $addressFactory;
        $this->customerRepository = $customerRepository;
        parent::__construct($context,$registry);
    }

    
    
    public function updateCustomer($email,$contact) {
        $websiteId = $this->helperData->getCurrentWebsiteId();
        $customer = $this->customerRepository->get($email,$websiteId);
        $data = $this->prepareCustomerData($contact["properties"]);            
        if(!isset($data["email"])){
            unset($data["email"]);
        }
        
        foreach($data as $attr=>$value) {
            $customer->setData($attr,$value);
        }
        $customer->setCustomAttribute('vid', $contact["vid"]);
        try {
           $this->customerRepository->save($customer);
           $customer = $this->customerRepository->get($email,$websiteId);
           return $customer->getCustomAttribute("vid")->getValue();
        } catch (Exception $e) {
            return false;
        }
        
        // $customerAddress = $this->addressFactory->create(); 
        // $addressData = $this->prepareCustomerAddressData($contact["properties"]);
        // if(empty($addressData)){
            // return false;
        // }
        // $addressData["parent_id"] = $customer->getId();
        // if(empty($addressData["firstname"])){
            // $addressData["firstname"] = $customer->getFirstname();
        // }
        // if(empty($addressData["lastname"])){
            // $addressData["lastname"] = $customer->getLastname();
        // }
        // $requiredAddressFields = $this->helperData->getMapping('customerAddressRequiredFields');
        // $diffArry =  array_diff_key($requiredAddressFields,$addressData);
        
        // if(!empty($diffArry)){
            // return true;
        // }
        
        // $customerAddress->setData($addressData);
        // $customerAddress->setCountryId('US')
                        // ->setIsDefaultBilling('1')
                        // ->setIsDefaultShipping('1')
                        // ->setSaveInAddressBook('1');
        
        // try {
            // $customerAddress->save();
            // return true;
        // } catch (Exception $e) {
            // $logger = $this->helperData->errorLog();
            // $logger->info('Customer Address Import Failed');
            // $logger->info(print_r($addressData, true));
            // return false;
        // }
        
        return false;
    }
    
    public function getVidByEmail($email){
        $websiteId = $this->helperData->getCurrentWebsiteId();
        try{
            $customer = $this->customerRepository->get($email,$websiteId);
            return $customer->getCustomAttribute("vid")->getValue();
        }
        catch(\Exception $e){   
            return false;
        }
        
    }
    
    protected function prepareCustomerData($contact){
        $fields = $this->helperData->getMapping('customer');
        $data = [];
        foreach($fields as $field=>$value){
            if(!empty($contact[$value]["value"])){
                $data[$field] = $contact[$value]["value"];
                if($field == 'gender'){
                    $gender = strtolower($contact[$value]["value"]);
                    $gendersArr = array("male"=>1,"female"=>2);
                    $data[$field] = $gendersArr[$gender];
                }
            }
        } 
        return $data;
    }
    
    protected function prepareCustomerAddressData($contact){
        $fields = $this->helperData->getMapping('customerAddress');
        $data = [];
        foreach($fields as $field=>$value){
            if(!empty($contact[$value]["value"])){
                if($field == 'street'){
                    $data[$field] = array('0'=>$contact[$value]["value"]);
                }
                else {
                    $data[$field] = $contact[$value]["value"];
                }
            }
        }
        return $data;
    }
    
}