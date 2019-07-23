<?php
namespace DCKAP\Hubspot\Controller\Blog;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use DCKAP\Hubspot\Model\Client;
use DCKAP\Hubspot\Model\Contacts;

class View extends \Magento\Framework\App\Action\Action
{
  protected $_pageFactory;
  public function __construct(
      \Magento\Framework\App\Action\Context $context,
      \Magento\Framework\View\Result\PageFactory $pageFactory,
      \Magento\Framework\Registry $coreRegistry)
  {
      $this->_pageFactory = $pageFactory;
      $this->_coreRegistry = $coreRegistry;
      return parent::__construct($context);
  }

  public function execute()
  {
      $id = $this->getRequest()->getParam('id');
      if (!empty($id)) {
        $this->_coreRegistry->register('id', $id);
        return $this->_pageFactory->create();
      } else {
        return;
      }
  }
    
}