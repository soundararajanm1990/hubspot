<?php
namespace DCKAP\Hubspot\Block\Blog;
use DCKAP\Hubspot\Model\Client;
use DCKAP\Hubspot\Model\Blogs;
class Index extends \Magento\Framework\View\Element\Template
{

    protected $client;
    protected $blogs;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Client $client,
        blogs $blogs,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository
    )
    {
        $this->client = $client;
        $this->blogs = $blogs;
        $this->categoryRepository = $categoryRepository;
        parent::__construct($context);
    }
    public function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Blog'));
    }
    public function getAllBlogs(){
        $filters['limit'] = 10;
        $filters['state'] = 'DRAFT';
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
    public function getSubCategories()
    {
        $parent_category_id = 16;
        $categoryObj = $this->categoryRepository->get($parent_category_id);
        $subcategories = $categoryObj->getChildrenCategories();
        return $subcategories;
    }
}
