<?php
namespace Library\GitHub;

/**
 * interface base
 */
interface GitHubHttpClientInterface
{

    const METHOD_GET = 'GET';
    const METHOD_PUT = 'PUT';
    const METHOD_POST = 'POST';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';
    const METHOD_HEAD = 'HEAD';

    const API_URL = 'https://api.github.com';

    /**
     * @param string $requestType
     * @param string $url
     * @param array  $params
     *
     * @return mixed
     */
    public function request($requestType, $url, $params);


}