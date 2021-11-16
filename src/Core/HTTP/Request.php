<?php

namespace App\Core\HTTP;

class Request
{
    public function getRequestUri(): string
    {
        return $_SERVER['REQUEST_URI'];
    }


    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }


    /**
     * @return false|string|null
     */
    public function getRedirectQueryString()
    {
        if (isset($_SERVER['REDIRECT_QUERY_STRING'])) {
            return substr($_SERVER['REDIRECT_QUERY_STRING'], 5);
        } else {
            return null;
        }
    }


    public function isGetExists(?string $key = null): bool
    {
        if ($key === null) {
            return isset($_GET);
        }
        return isset($_GET[$key]);
    }


    public function isPostExists(?string $key = null): bool
    {
        if ($key === null) {
            return isset($_POST);
        }
        return isset($_POST[$key]);
    }


    /**
     * @return mixed
     */
    public function getGetData(?string $key = null)
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key];
    }


    /**
     * @return mixed
     */
    public function getPostData(?string $key = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key];
    }


    public function getFilesData(?string $key = null): array
    {
        if ($key === null) {
            return $_FILES;
        }
        return $_FILES[$key];
    }
}
