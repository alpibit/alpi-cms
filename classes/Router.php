<?php

class Router
{
    private $db;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    public function getRoute($requestUri)
    {
        $requestUri = trim($requestUri, '/');
        $segments = explode('/', $requestUri);

        if (empty($segments[0])) {
            return ['type' => 'home'];
        }

        if ($segments[0] === 'admin') {
            return ['type' => 'admin'];
        }

        if ($this->isCategory($segments[0])) {
            if (isset($segments[1])) {
                return $this->getPostRoute($segments[0], $segments[1]);
            } else {
                return ['type' => 'category', 'slug' => $segments[0]];
            }
        }

        return $this->getPageRoute($segments[0]);
    }

    private function isCategory($slug)
    {
        $categoryObj = new Category($this->db);
        return $categoryObj->categoryExists($slug);
    }

    private function getPageRoute($slug)
    {
        $pageObj = new Page($this->db);
        $pageContent = $pageObj->getPageBySlug($slug);
        return $pageContent ? ['type' => 'page', 'content' => $pageContent] : ['type' => '404'];
    }

    private function getPostRoute($categorySlug, $postSlug)
    {
        $postObj = new Post($this->db);
        $postContent = $postObj->getPostByCategoryAndSlug($categorySlug, $postSlug);
        return $postContent ? ['type' => 'post', 'content' => $postContent] : ['type' => '404'];
    }

    public function generateUrl($type, $slug = '', $categorySlug = '')
    {
        if ($type === 'page') {
            return "/$slug.php";
        } else if ($type === 'post') {
            return "/$categorySlug/$slug";
        } else if ($type === 'category') {
            return "/$slug/";
        } else {
            return '/';
        }
    }
}
