<?php
namespace DCKAP\Hubspot\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use DCKAP\Hubspot\Model\Client;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    
    private $customersession;
    
    protected $client;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $session,
        Client $client
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->customersession = $session;
        parent::__construct($context);
        $this->client = $client;
    }

    /**
     * Customer order history
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        //$result = $this->client->getRequest("contacts/v1/contact/email/testingapis@hubspot.com/profile");
        
        $data = array (
  'properties' => 
  array (
    0 => 
    array (
      'value' => 'Test Deal',
      'name' => 'dealname',
    ),
    1 => 
    array (
      'value' => 'appointmentscheduled',
      'name' => 'dealstage',
    ),
    
    2 => 
    array (
      'value' => '60000',
      'name' => 'amount',
    ),
  ),
);
        $result = $this->client->postRequest("deals/v1/deal",$data);
        echo "<pre>";
        print_r($result);
        
        

    }
    
}