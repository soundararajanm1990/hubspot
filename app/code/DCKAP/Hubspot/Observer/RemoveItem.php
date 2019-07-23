<?php
namespace DCKAP\Hubspot\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use DCKAP\Hubspot\Model\Deal;
use DCKAP\Hubspot\Model\Contacts;
use DCKAP\Hubspot\Model\ManageCustomers;
use Magento\Framework\Serialize\Serializer\Json;

class RemoveItem implements ObserverInterface
{
    public $deal;
    public $contacts;
    protected $_customerSession;
    protected $customerManager;

   
    public function __construct(
        Deal $deal,
        Contacts $contacts,
        ManageCustomers $customerManager,
        \Magento\Customer\Model\SessionFactory $customerSession,
        Json $serializer = null
    ) {
        $this->deal = $deal;
        $this->contacts = $contacts;
        $this->customerManager = $customerManager;
        $this->_customerSession = $customerSession->create();
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);

    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */

    public function execute(\Magento\Framework\Event\Observer $observer) {
        
        $product = $observer->getQuoteItem()->getProduct();
        $item = $observer->getQuoteItem();
        $additionalOptions = array();
        $additionalOptions = array();
        $dealId = null;
        if ($additionalOption = $item->getOptionByCode('additional_options')) {
            $additionalOptions = (array) $this->serializer->unserialize($additionalOption->getValue());
        }
        if(!empty($additionalOptions['deal_id']['value'])) {
            $dealId = $additionalOptions['deal_id']['value'];
        }
        
        if ($this->_customerSession->isLoggedIn()) {
           
            $user_email = $this->_customerSession->getCustomerData()->getEmail();
            $contact_vid = $this->customerManager->getVidByEmail($user_email);
            if($dealId){
                $data  = array(                          
                            'properties' => 
                              array (
                                0 => 
                                   array (
                                    'value' => Deal::DEAL_STAGE_4,
                                    'name' => 'dealstage',
                                  ),                                                      
                              ),
                          );

                $result = $this->deal->updateDeal($dealId,$data); 
            }
                                  
        }

    }

}