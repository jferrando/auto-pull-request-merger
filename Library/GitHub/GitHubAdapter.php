<?php

Namespace Library\GitHub;

use App;

class GitHubAdapter extends \Library\Base
{

    protected $_gitHubApi;

    protected $repositoryName;
    protected $repositoryOwner;
    protected $user;
    protected $password;

    public function __construct( \Config\Config $config)
    {
        $this->config = $config;
        $this->repositoryName = $config->get("github_repository_name");
        $this->repositoryOwner = $config->get("github_repository_owner");
        $this->user = $config->get("github_user");
        $this->password = $config->get("github_password");

    }

    public function auth()
    {
        $this->_gitHubApi->auth(
            $this->user,
            $this->password,
            \Library\GitHub\GitHubApi::AUTH_HTTP
        );
    }

    /**
     * Get the open pull requests of the repo
     * @return array
     */
    public function openPullRequests()
    {
        try {
            $this->auth();
            $prs = $this->_gitHubApi->get(
                '/repos/:owner/:repo/pulls',
                array(
                    'owner' => $this->repositoryOwner,
                    'repo' => $this->repositoryName
                )
            );
            if (count($prs) >= $this->config->get("max_open_pull_requests")) {
                App::dispatchEvent("too_many_open_requests");

            }

            return $prs;

        } catch (\Exception $e) {
            App::log($e);
            return null;
        }
    }

    /**
     * @param $pullRequestNumber
     * @return mixed
     * @IgnoreCodeCoverage
     */
    public function pullRequestComments($pullRequestNumber)
    {
        $this->auth();

        $prs = $this->_gitHubApi->get(
            '/repos/:owner/:repo/issues/:number/comments',
            array(
                'owner' => $this->repositoryOwner,
                'repo' => $this->repositoryName,
                'number' => $pullRequestNumber
            )
        );

        return $prs;

    }

    /**
     *
     * get build status
     * @param $sha
     * @return null
     */
    public function getStatus($sha)
    {
        $this->auth();

        $response = $this->_gitHubApi->get(
            '/repos/:owner/:repo/statuses/:sha',
            array(
                'owner' => $this->repositoryOwner,
                'repo' => $this->repositoryName,
                'sha' => $sha
            )
        );
        $last = isset($response[0]) ? $response[0] : null;

        return $last;
    }


    public function merge($number)
    {

        try {
            $this->auth();
            $this->_gitHubApi->put(
                '/repos/:owner/:repo/pulls/:number/merge',
                array(
                    'owner' => $this->repositoryOwner,
                    'repo' => $this->repositoryName,
                    'number' => $number
                ),
                array(
                    'message' => 'merged automatically',
                )
            );
            App::log("Merged pull $number");
            App::dispatchEvent("pull_request_merged");

        } catch (\Exception $e) {
            $ex = json_decode($e->getMessage());
            App::dispatchEvent("cannot_merge_pull_request", $e->getMessage());
        }

    }

    public function addComment($number, $message)
    {
        $this->auth();

        $this->_gitHubApi->post(
            '/repos/:owner/:repo/issues/:number/comments',
            array(
                'owner' => $this->repositoryOwner,
                'repo' => $this->repositoryName,
                'number' => $number
            ),
            array(
                'body' => $message,
            )
        );

    }

}