<?php

namespace DCKAP\Hubspot\Model;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

class Blogs extends \Magento\Framework\Model\AbstractModel
{
    protected $helperData;
    protected $client;
    
    const  BASE_BLOGS_ENDPOINT = "content/api/v2/blog-posts";
    public function __construct(
        Context $context,
        Registry $registry,
        \DCKAP\Hubspot\Helper\Data $helperData,
        \DCKAP\Hubspot\Model\Client $client
    ) {
        $this->helperData = $helperData;
        $this->client = $client;
        parent::__construct($context,$registry);
    }
    
    public function getBlogPosts(array $filters){
        $endpoint = self::BASE_BLOGS_ENDPOINT;
        return $this->readRequest($endpoint,$filters);
    }
    
    public function getBlogContentsOnly(array $filters) {
        $blogsPosts = $this->getBlogPosts($filters);
        $blogContents = [];
        $i = 0;
        if($blogsPosts && isset($blogsPosts["objects"])){
            foreach($blogsPosts["objects"] as $post){
                if(isset($post["post_body"])) {
                    $blogContents['blog'][$i]['Post_body'] = $post["post_body"];
                    $blogContents['blog'][$i]['title'] = $post["title"];
                    $blogContents['blog'][$i]['featured_image'] = $post["featured_image"];
                    $blogContents['blog'][$i]['created'] = $post["created"];
                    $blogContents['blog'][$i]['created_at'] = date("M m,Y", substr($post["created"], 0, 10));
                    $blogContents['blog'][$i]['post_summary'] = $post["post_summary"];
                    $blogContents['blog'][$i]['slug'] = ltrim($post["slug"], 'blog/');
                    $blogContents['blog'][$i]['id'] = $post["id"];
                }
                $blogContents['total_count']['offset'] = $blogsPosts['offset'];
                $blogContents['total_count']['total'] = $blogsPosts['total'];
                //$blogContents['total_count']['total_count'] = $blogsPosts['total_count'];
                $blogContents['total_count']['limit'] = $blogsPosts['limit'];
                $i++;
            }
        }
        return $blogContents;
    }
    public function getBlogById($id) {
        $filters = array();
        $endpoint = self::BASE_BLOGS_ENDPOINT . "/" . $id;
        $post = $this->readRequest($endpoint,$filters);
        $blogContents = [];
        $i = 0;
        if($post && isset($post["post_body"])){            
            $blogContents['post_body'] = $post["post_body"];
            $blogContents['title'] = $post["title"];
            $blogContents['featured_image'] = $post["featured_image"];
            $blogContents['created'] = $post["created"];
            $blogContents['created_at'] = date("M m,Y", substr($post["created"], 0, 10));
            $blogContents['post_summary'] = $post["post_summary"];
            $blogContents['slug'] = ltrim($post["slug"], 'blog/');
            $blogContents['id'] = $post["id"];
        }
        return $blogContents;
    }
    protected function readRequest($endpoint,array $filters = []){
        if($this->helperData->getEnabled()){
            try{
                $result = $this->client->getRequest($endpoint,$filters);
                if(!$result || (isset($result["status"]) && $result["status"] == 'error')){
                    return false;
                }
                else if(is_array($result)){
                    return $result;
                }
            }
            catch(Exception $e){
                return false;
            }
        }
        else {
            return false;   
        }
    }
}
