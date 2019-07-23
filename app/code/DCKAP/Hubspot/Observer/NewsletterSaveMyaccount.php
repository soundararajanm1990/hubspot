<?php
namespace DCKAP\Hubspot\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use DCKAP\Hubspot\Model\Contacts;
use Magento\Customer\Model\Session;

class NewsletterSaveMyaccount implements ObserverInterface
{
    protected $_request;
    protected $contacts;
    protected $customersession;
    
    public function __construct(  
        \Magento\Framework\App\RequestInterface $request,
        Session $session,
        Contacts $contacts  
    ) {        
        $this->_request = $request; 
        $this->contacts = $contacts; 
        $this->customersession = $session;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) { 
        if(!$this->customersession->isLoggedIn()) {
            return;
        }
        
        $email = $this->customersession->getCustomer()->getEmail();
        $post_values = $this->_request->getPost(); 
        $contactLists = null;
        $lists = [];
        $contact = $this->contacts->getContactByEmail($email);
        if(!empty($contact['list-memberships'])){
            $contactLists = $contact['list-memberships'];
        }
        if($contactLists){
            foreach($contactLists as $list){
              $lists[] =   $list['static-list-id'];
            }
        }
        
        if($post_values->is_subscribed){
            $this->contacts->addContactToList(Contacts::NEWSLETTER_GENERAL,$email);
        }
        elseif(in_array(Contacts::NEWSLETTER_GENERAL,$lists)) {
            $this->contacts->removeContactFromList(Contacts::NEWSLETTER_GENERAL,$contact['vid']);
        }
        
        if($post_values->newsletter_option_products){
            $this->contacts->addContactToList(Contacts::NEWSLETTER_PRODUCT,$email);
        }
        elseif(in_array(Contacts::NEWSLETTER_PRODUCT,$lists)) {
            $this->contacts->removeContactFromList(Contacts::NEWSLETTER_PRODUCT,$contact['vid']);
        }
        
        if($post_values->newsletter_option_offers){
            $this->contacts->addContactToList(Contacts::NEWSLETTER_OFFER,$email);
        }
        elseif(in_array(Contacts::NEWSLETTER_OFFER,$lists)) {
            $this->contacts->removeContactFromList(Contacts::NEWSLETTER_OFFER,$contact['vid']);
        }
        
        return true;
        
    }
}