<?php
namespace Library\GitHub;


/**
 * GitHub Api Connector
 */
class GitHubApi
{
    const AUTH_HTTP = 'BASIC';
    const AUTH_OAUTH = 'OAUTH';

    /**
     * http client instance
     * @var \GitHubHttpClientInterface|object
     */
    private $httpClient = null;

    /**
     * @param GitHubHttpClientInterface $httpClient
     */
    public function __construct(GitHubHttpClientInterface $httpClient)
    {
        $this -> httpClient = $httpClient;
    }

    /**
     * @param string $user
     * @param null   $pass
     * @param string $type
     */
    public function auth($user, $pass = null, $type = self::AUTH_OAUTH)
    {
        $this -> httpClient -> user = $user;
        $this -> httpClient -> pass = $pass;
        $this -> httpClient -> authType = $type;
    }

    /**
     * @param strint $method
     * @param array  $arg
     *
     * @return mixed
     * @throws GitHubCommonException
     * @codeCoverageIgnore
     */
    public function __call($method, $arg)
    {
        if (!isset($arg[0]) || !isset($arg[1])) {
            throw new GitHubCommonException('Missing argument');
        }
        $httpMethod = 'METHOD_' . strtoupper($method);

        switch($method) {
            case 'get':
            case 'head':
            case 'delete':
                return $this -> doRequest(constant('\Library\GitHub\GitHubHttpClientInterface::'. $httpMethod), $arg[0], $arg[1]);
                break;
            case 'post':
            case 'put':
            case 'patch':
                if (!isset($arg[2])) {
                    throw new GitHubCommonException('Missing argument for write operation');
                }

                return $this -> doRequest(constant('\Library\GitHub\GitHubHttpClientInterface::'. $httpMethod), $arg[0], $arg[1], $arg[2]);
                break;
        }
    }

    /**
     * @param string $method
     * @param string $url
     * @param array  $params
     * @param array  $input
     *
     * @return mixed
     */
    public function doRequest($method, $url, array $params, array $input = null)
    {
        if ($input === null) {
            $input = array();
        }

        $remoteUrl = $this -> prepareUrl($url, $params);

        return $this -> httpClient -> request($method, $remoteUrl, $input);
    }

    /**
     * @param string $url
     * @param array  $params
     *
     * @return bool|mixed
     */
    public function prepareUrl($url, array $params)
    {
        $hasMatch = preg_match_all('/(:(\w+))/', $url, $matches);

        ksort($params);
        $paramKeys = array_keys($params);
        $paramValues = array_values($params);

        $urlKeys = array_keys(array_flip($matches[2]));
        sort($urlKeys);

        $urlParams = array_keys(array_flip($matches[1]));
        sort($urlParams);

        if ($paramKeys === $urlKeys) {
            return str_replace($urlParams, $paramValues, $url);
        }

        return false;
    }
}