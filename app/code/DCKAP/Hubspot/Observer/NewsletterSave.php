<?php
namespace DCKAP\Hubspot\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use DCKAP\Hubspot\Model\Contacts;

class NewsletterSave implements ObserverInterface
{
    protected $_request;
    public $contacts;

    public function __construct(  
        \Magento\Framework\App\RequestInterface $request,
        Contacts $contacts  
    ) {        
        $this->_request = $request; 
        $this->contacts = $contacts; 
    }

    public function execute(\Magento\Framework\Event\Observer $observer) { 
        $post_values = $this->_request->getPost();
        $email = $post_values->email;
        if(!$this->contacts->isContactExist($email)) {
            $customerData = [];
            $customerData[] = array("property"=> "email","value"=> $email);
            $result = $this->contacts->createContact(array('properties'=>$customerData));
            
            if(!empty($result["vid"])){
                $this->contacts->addContactToList(Contacts::NEWSLETTER_GENERAL,$email);
                if($post_values->newsletter_option_both){
                    $this->contacts->addContactToList(Contacts::NEWSLETTER_PRODUCT,$email);
                    $this->contacts->addContactToList(Contacts::NEWSLETTER_OFFER,$email);
                }else{
                    if($post_values->newsletter_option_products){
                        $this->contacts->addContactToList(Contacts::NEWSLETTER_PRODUCT,$email);
                    }
                    if($post_values->newsletter_option_offers){
                        $this->contacts->addContactToList(Contacts::NEWSLETTER_OFFER,$email);
                    }
                }
            }
        }
        else {
            $this->contacts->addContactToList(Contacts::NEWSLETTER_GENERAL,$email);
            if($post_values->newsletter_option_both){
                    $this->contacts->addContactToList(Contacts::NEWSLETTER_PRODUCT,$email);
                    $this->contacts->addContactToList(Contacts::NEWSLETTER_OFFER,$email);
            }else{
                if($post_values->newsletter_option_products){
                    $this->contacts->addContactToList(Contacts::NEWSLETTER_PRODUCT,$email);
                }
                if($post_values->newsletter_option_offers){
                    $this->contacts->addContactToList(Contacts::NEWSLETTER_OFFER,$email);
                }
            }
        }
        
        return true;
        
    }
}