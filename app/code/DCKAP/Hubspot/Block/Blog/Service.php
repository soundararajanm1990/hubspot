<?php
namespace DCKAP\Hubspot\Block\Blog;
use DCKAP\Hubspot\Model\Client;
use DCKAP\Hubspot\Model\Blogs;
class Service extends \Magento\Framework\View\Element\Template
{

    protected $client;
    protected $blogs;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Client $client,
        blogs $blogs
    )
    {
        $this->client = $client;
        $this->blogs = $blogs;
        parent::__construct($context);
    }
    public function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Blog'));
    }
    public function getAllBlogs($topic_id){
        $filters['state'] = 'DRAFT';
        $filters['topic_id'] = $topic_id;
        $filters['offset'] = ($this->getRequest()->getParam('page')) ? $this->getRequest()->getParam('page') : 0;       
        $blogs = $this->blogs->getBlogContentsOnly($filters);
        $blogs['total_count']['page'] = ($this->getRequest()->getParam('page')) ? $this->getRequest()->getParam('page') : 1;       
        return $blogs;
    }

    /**
     * @return string
     */
    public function getBackUrl($id)
    {
        return $this->getUrl('hubspot/blog/view/',array('id' => $id));
    }
   
    /**
     * @return string
     */
    public function getSiteUrl()
    {
        return $this->getUrl('blog');
    }
}
