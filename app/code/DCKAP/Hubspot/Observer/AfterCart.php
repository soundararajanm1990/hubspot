<?php
namespace DCKAP\Hubspot\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use DCKAP\Hubspot\Model\Deal;
use DCKAP\Hubspot\Model\Contacts;
use Magento\Framework\Serialize\Serializer\Json;
use DCKAP\Hubspot\Model\ManageCustomers;

class AfterCart implements ObserverInterface
{
    /**
     * @var \Ced\HubIntegration\Model\HubItemFactory
     */
    
    public $deal;
    public $contacts;
    public $cart;
    protected $_customerSession;
    protected $serializer;


    /**
     * @param \Ced\HubIntegration\Model\HubItemFactory $hubItemFactory
     */
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
        
        $product = $observer->getEvent()->getProduct();
        $item = $observer->getQuoteItem();
        $additionalOptions = array();
        $dealId = null;
        if ($additionalOption = $item->getOptionByCode('additional_options')) {
            $additionalOptions = (array) $this->serializer->unserialize($additionalOption->getValue());
        }
        if(!empty($additionalOptions['deal_id']['value'])) {
            $dealId = $additionalOptions['deal_id']['value'];
        }
        
         $all_products = $this->cart->getQuote()->getAllVisibleItems();

         $update_item = false;

         foreach ($all_products as $item) { 

            if( ($item->getName() == $product->getName()) && ($item->getQty() != $product->getQty()) ){

              $update_item_name = $product->getName().'-'.$product->getId();
              $update_item_price = $item->getPrice()*$item->getQty();
              $update_item = true;
            }            
         }

        if ($this->_customerSession->isLoggedIn()) {
           
            $user_email = $this->_customerSession->getCustomerData()->getEmail();
            $contactVid = $this->customerManager->getVidByEmail($user_email);
            $contact_exist = $this->contacts->isContactExist($user_email);

            if(!($contactVid)){

                $contact_data = array (
                  'properties' => 
                      array (
                        0 => 
                            array (
                              "property" => "email",
                              "value"    => $user_email,
                            ),
                      ),
                );
                $contact_exist = $this->contacts->isContactExist($user_email);
                if(!($contact_exist)){
                    $create_contact = $this->contacts->createContact($contact_data);
                    $contact_vid = $create_contact["vid"];
                }
                else{
                    $hubspotContact = $this->contacts->getContactByEmail($user_email);
                    $result = $this->customerManager->updateCustomer($user_email, $hubspotContact);
                    $contact_vid = $result;
                }

            }else{
                
                $contact_vid = $contactVid;
            }          
           
            if($update_item && $dealId){
                
                $data  = array(
                            
                            'properties' => 
                              array (
                                0 => 
                                    array (
                                      'value' => $update_item_price,
                                      'name' => 'amount',
                                    ),  
                                1 => 
                                    array (
                                      'value' => Deal::DEAL_STAGE_2,
                                      'name' => 'dealstage',
                                    ),                                                      
                              ),
                          );
                  
                $result = $this->deal->updateDeal($dealId,$data);

            }
            else if($contact_vid) {

                $data = array (
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
                              'value' => $product->getName().'-'.$product->getId(),
                              'name' => 'dealname',
                            ),
                        1 => 
                            array (
                              'value' => Deal::DEAL_STAGE_1,
                              'name' => 'dealstage',
                            ),                
                        2 => 
                            array (
                              'value' => $product->getPrice()*$product->getQty(),
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
                if(!empty($result)){
                    $additionalOptions = [];
                    $additionalOptions['deal_id'] = array(
                        'label' => "Hubspot Deal ID",
                        'value' => $result["dealId"],
                    );
                    $item->addOption([
                        'code' => 'additional_options',
                        'value' => $this->serializer->serialize($additionalOptions),
                        'product_id' => $product->getId()
                    ]);
                }
            }

        }        
        
    }

}