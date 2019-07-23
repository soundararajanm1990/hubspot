<?php
namespace DCKAP\Hubspot\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use DCKAP\Hubspot\Model\Deal;
use DCKAP\Hubspot\Model\Contacts;
use Magento\Framework\Serialize\Serializer\Json;
use DCKAP\Hubspot\Model\ManageCustomers;

class CartUpdateItem implements ObserverInterface
{
        
    public $deal;
    public $cart;
    public $contacts;
    protected $_customerSession;
    protected $serializer;
   
    public function __construct(
        Deal $deal,
        Contacts $contacts,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Checkout\Model\Cart $cart,
        ManageCustomers $customerManager,
        Json $serializer = null
    ) {
        $this->deal = $deal;
        $this->contacts = $contacts;
        $this->_customerSession = $customerSession->create();
        $this->cart = $cart;
        $this->customerManager = $customerManager;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */

    public function execute(\Magento\Framework\Event\Observer $observer) {
        
      $all_products = $this->cart->getQuote()->getAllVisibleItems();
       if ($this->_customerSession->isLoggedIn()) {           
            $user_email = $this->_customerSession->getCustomerData()->getEmail(); 
            $contact_vid = $this->customerManager->getVidByEmail($user_email); 

            foreach ($all_products as $item) {                 
                  if ($additionalOption = $item->getOptionByCode('additional_options')) {
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
                                            'value' => $item->getPrice() * $item->getQty(),
                                            'name' => 'amount',
                                          ),
                                      1 => 
                                          array (
                                            'value' => Deal::DEAL_STAGE_2,
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
                                          'value' => Deal::DEAL_STAGE_2,
                                          'name' => 'dealstage',
                                        ),                
                                    2 => 
                                        array (
                                          'value' => $item->getPrice() * $item->getQty(),
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
                      if(!empty($result1)){
                          $additionalOptions = [];
                          $additionalOptions['deal_id'] = array(
                              'label' => "Hubspot Deal ID",
                              'value' => $result1["dealId"],
                          );
                          $item->addOption([
                              'code' => 'additional_options',
                              'value' => $this->serializer->serialize($additionalOptions),
                              'product_id' => $item->getProductId()
                          ]);
                      }
                   } 
               }
            if(!empty($data)){     
                $result = $this->deal->updateGroupDeal($data);
            }
        }      
    }
}