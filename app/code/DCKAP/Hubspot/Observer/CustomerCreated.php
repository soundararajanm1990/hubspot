<?php

namespace DCKAP\Hubspot\Observer;

use Magento\Framework\Event\ObserverInterface;
use DCKAP\Hubspot\Model\Contacts;
use DCKAP\Hubspot\Model\ManageCustomers;

class CustomerCreated implements ObserverInterface
{
    protected $contacts;
    protected $helperData;
    protected $customerManager;
    
    public function __construct(
       Contacts $contacts,
       \DCKAP\Hubspot\Helper\Data $helperData,
       ManageCustomers $customerManager,
       \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
    ) {
        $this->contacts = $contacts;
        $this->helperData = $helperData;
        $this->customerManager = $customerManager;
        $this->addressRepository = $addressRepository;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer) { 

        $customer = $observer->getEvent()->getCustomer();
        $email = $customer->getEmail();
        $hubspotContact = $this->contacts->getContactByEmail($email);
        
        if($hubspotContact) {        	
            $firstname = '';
            foreach ($hubspotContact["properties"] as $value) {        		
                if(!empty($value['firstname']['value'])){
                        $firstname = $value['firstname']['value'];	        		
                }
            }
            if(empty($firstname)){
                $customerData = [];                
                $customerData[] = array("property"=> "firstname","value"=> $customer->getFirstname());
                $customerData[] = array("property"=> "lastname","value"=> $customer->getLastname());
                $this->contacts->updateContactByEmail($email,array('properties'=>$customerData)); 
                
                $hubspotContact['properties'] = array_merge($hubspotContact['properties'],$customerData);
            }
            return $this->customerManager->updateCustomer($email,$hubspotContact);
        	
        }
        else {
                $customerData = [];
                $billingAddressId = $customer->getDefaultBilling();
                if (isset($billingAddressId) && !empty($billingAddressId)) {
                    $billingAddress = $this->addressRepository->getById($billingAddressId);
                    $telephone = $billingAddress->getTelephone(); 
                    $customerData[] = array("property"=> "phone","value"=> $telephone);  
                }
                
                $customerData[] = array("property"=> "email","value"=> $email);
                $customerData[] = array("property"=> "firstname","value"=> $customer->getFirstname());
                $customerData[] = array("property"=> "lastname","value"=> $customer->getLastname());                  
                $result = $this->contacts->createContact(array('properties'=>$customerData));
                if(!empty($result["vid"])){
                    $this->contacts->addContactToList(Contacts::STARSALES_CUSTOMER_LIST_ID,$email);
                    $this->customerManager->updateCustomer($email,$result);
                    return true;
                }
        }
    }
        
}