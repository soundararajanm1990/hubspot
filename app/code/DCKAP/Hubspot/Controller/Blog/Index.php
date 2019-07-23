<?php
namespace DCKAP\Hubspot\Controller\Blog;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use DCKAP\Hubspot\Model\Client;
use DCKAP\Hubspot\Model\Contacts;

class Index extends \Magento\Framework\App\Action\Action
{
  protected $_pageFactory;
  public function __construct(
      \Magento\Framework\App\Action\Context $context,
      \Magento\Framework\View\Result\PageFactory $pageFactory)
  {
      $this->_pageFactory = $pageFactory;
      return parent::__construct($context);
  }

  public function execute()
  {
      return $this->_pageFactory->create();
  }
    
}