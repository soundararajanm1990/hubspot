<?php
namespace DCKAP\Hubspot\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use DCKAP\Hubspot\Model\Contacts;
use DCKAP\Hubspot\Model\Deal;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\AddressFactory;
use DCKAP\Hubspot\Model\ManageCustomers;
use Magento\Framework\Serialize\Serializer\Json;

class CheckoutSuccess implements ObserverInterface
{
    public $contacts;
    protected $_customerSession;
    protected $_orderFactory;    
    protected $customerFactory;
    protected $addressFactory;
    protected $customerManager;
    public $deal;
    protected $serializer;    
    protected $quoteFactory;
    
    public function __construct(
        Contacts $contacts,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        CustomerFactory $customerFactory,
        AddressFactory $addressFactory,
        \DCKAP\Hubspot\Helper\Data $helperData,
        ManageCustomers $customerManager,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        Deal $deal,
        Json $serializer = null
    ) {
        $this->contacts = $contacts;
        $this->_customerSession = $customerSession->create();
        $this->_orderFactory = $orderFactory;   
        $this->customerFactory  = $customerFactory;
        $this->addressFactory   = $addressFactory;
        $this->helperData = $helperData; 
        $this->customerManager = $customerManager;
        $this->deal = $deal;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */

    public function execute(\Magento\Framework\Event\Observer $observer) {  
        
      $orderIds = $observer->getEvent()->getOrderIds();
      if (!$this->_customerSession->isLoggedIn()) {       
        if (count($orderIds)) {
            $orderId = $orderIds[0];            
            $order = $this->_orderFactory->create()->load($orderId);
            $user_email = $order->getCustomerEmail();            
            $customer = $this->customerFactory->create();             
            $websiteId = $this->helperData->getCurrentWebsiteId();
            $customer->setWebsiteId($websiteId);

            if ($customer->loadByEmail($user_email)->getId()) {
              
                  return false;
            }    
            $shippingAddress = $order->getShippingAddress();
            $hubspotContact = $this->contacts->getContactByEmail($user_email);
            
            if($hubspotContact){
              
                $this->createCustomerFromOrder($order, $customer);
                $result = $this->customerManager->updateCustomer($user_email, $hubspotContact);  
                   
                 if($result){ 
                     $this->createDealsAfterCheckout($result,$orderId);
                 }
            }         
        }
      }
      else {        
          if (count($orderIds)) {
            $orderId = $orderIds[0];                                           
          }            
         $this->updateDealsAfterCheckout($orderId);          
      }
    }
    public function createCustomerFromOrder($order, $customer) {
      $data['email'] = $order->getCustomerEmail();
      $data['firstname'] = $order->getBillingAddress()->getFirstName();
      $data['lastname'] = $order->getBillingAddress()->getLastName();
      $customer->setData($data);
      try {
          $customer->save();
      } catch (\Exception $e) {
          return false;
      }
      $customerAddress = $this->addressFactory->create(); 
      $customerAddress->setCustomerId($customer->getId());
      $customerAddress->setFirstname($data['firstname']);
      $customerAddress->setLastname($data['lastname']);
      $customerAddress->setCompany($order->getShippingAddress()->getCompany());
      $customerAddress->setTelephone($order->getShippingAddress()->getTelephone());
      $customerAddress->setStreet($order->getShippingAddress()->getStreet());
      $customerAddress->setCity($order->getShippingAddress()->getCity());
      $customerAddress->setRegionId($order->getShippingAddress()->getRegionId());
      $customerAddress->setPostcode($order->getShippingAddress()->getPostcode());
      $customerAddress->setCountryId($order->getShippingAddress()->getCountryId());
      $customerAddress->setIsDefaultBilling('1')
                    ->setIsDefaultShipping('1')
                    ->setSaveInAddressBook('1');
      try {
         $customerAddress->save();
      } catch (\Exception $e) {
         return false;
      }      
      return true;
    }
    
    protected function updateDealsAfterCheckout($orderId) { 
        
        $itemCollection = array(); $TotalItemCount=0;      
        $order = $this->_orderFactory->create()->load($orderId);
        //$itemCollection = $order->getItemsCollection();  
        //$itemCollection = $order->getAllVisibleItems();
        $TotalItemCount = $order->getTotalItemCount();
        $user_email = $this->_customerSession->getCustomerData()->getEmail();
        $contact_vid = $this->customerManager->getVidByEmail($user_email);
        $quote = $this->quoteFactory->create()->load($order->getQuoteId());  
        $itemCollection = $quote->getAllVisibleItems();             
         
          foreach ($itemCollection as $item) {                  
                $additionalOptions = array();         
                if($additionalOption = $item->getOptionByCode('additional_options')) {                    
                    $additionalOptions = (array) $this->serializer->unserialize($additionalOption->getValue());
                }
                if(!empty($additionalOptions['deal_id']['value'])) {                    
                    $dealId = $additionalOptions['deal_id']['value'];                    
                    $data[]  = array(
                                  "objectId" => $dealId,
                                  'properties' => 
                                    array (
                                      0 => 
                                          array (
                                            'value' => Deal::DEAL_STAGE_3,
                                            'name' => 'dealstage',
                                          ),                                                      
                                    ),
                                );
                }else{                     
                    $data1 = array (
                        "associations"  => 
                            array (            
                            "associatedVids" => array (
                              0 =>  $contact_vid
                            )              
                        ),
                        'properties' => 
                            array (
                              0 => 
                                  array (
                                    'value' => $item->getName().'-'.$item->getProductId(),
                                    'name' => 'dealname',
                                  ),
                              1 => 
                                  array (
                                    'value' => Deal::DEAL_STAGE_3,
                                    'name' => 'dealstage',
                                  ),                
                              2 => 
                                  array (
                                    'value' => $item->getPrice()*$item->getQty(),
                                    'name' => 'amount',
                                  ),
                              3=>   
                                  array (
                                    'value' => Deal::DEAL_PIPELINE_ID,
                                    'name' => 'pipeline',
                                  ),  

                            ),
                      );        
                                                                 
                   $result1 = $this->deal->createDeal($data1);                    
                }
          }           
          if(!empty($data)){            
            $result = $this->deal->updateGroupDeal($data);
          }
          return true;          
    }
    
    protected function createDealsAfterCheckout($vid = '',$orderId){
     
      $order = $this->_orderFactory->create()->load($orderId);
      $itemCollection = $order->getItemsCollection();
                   
      if(!empty($vid) && !empty($itemCollection)){ 
        foreach ($itemCollection as $item) { 
            $data = array (
                    "associations"  => 
                        array (            
                        "associatedVids" => array (
                          0 =>  $vid
                        )              
                    ),
                    'properties' => 
                        array (
                          0 => 
                              array (
                                'value' => $item->getName().'-'.$item->getProductId(),
                                'name' => 'dealname',
                              ),
                          1 => 
                              array (
                                'value' => Deal::DEAL_STAGE_3,
                                'name' => 'dealstage',
                              ),                
                          2 => 
                              array (
                                'value' => $item->getPrice()*$item->getQtyOrdered(),
                                'name' => 'amount',
                              ),
                          3=>   
                              array (
                                'value' => Deal::DEAL_PIPELINE_ID,
                                'name' => 'pipeline',
                              ),  

                        ),
                  );

                  $result = $this->deal->createDeal($data);  
            }
          return true;
      }else{               
          return false;
      }              
    }
}