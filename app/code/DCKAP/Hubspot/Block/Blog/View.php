<?php
namespace DCKAP\Hubspot\Block\Blog;
use DCKAP\Hubspot\Model\Client;
use DCKAP\Hubspot\Model\Blogs;
class View extends \Magento\Framework\View\Element\Template
{

    protected $client;
    protected $blogs;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Client $client,
        blogs $blogs,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository
    )
    {
        $this->client = $client;
        $this->blogs = $blogs;
        $this->_coreRegistry = $coreRegistry;
        $this->categoryRepository = $categoryRepository;
        parent::__construct($context);
    }
    public function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Blog'));
    }
    public function getDetaillog(){
        $id = $this->_coreRegistry->registry('id');
        $blogs = $this->blogs->getBlogById($id);
        return $blogs;
    }
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
