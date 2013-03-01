<?php

Namespace Library\GitHub;

class GitHub
{

    protected $_client;

    protected $repositoryName;
    protected $owner;

    public function __construct($user, $password, $repositoryOwner, $repositoryName)
    {
        $this->repositoryName = $repositoryName;
        $this->repositoryOwner = $repositoryOwner;
        $this->_client = new GitHubApi(new GitHubCurl());
        $this->_client->auth(
            $user,
            $password,
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

            $prs = $this->_client->get(
                '/repos/:owner/:repo/pulls',
                array(
                    'owner' => $this->owner,
                    'repo' => $this->repositoryName
                )
            );

            if (count($prs) >= $this->config->get("max_open_pull_requests")) {
                App::dispatchEvent("too_many_open_requests");

            }

            return $prs;

        } catch (\Exception $e) {
            echo "$e\n";

            return array();
        }
    }


    public function commentsInPullRequest($pullRequestNumber)
    {
        $prs = $this->_client->get(
            '/repos/:owner/:repo/issues/:number/comments',
            array(
                'owner' => $this->repositoryOwner,
                'repo' => $this->repositoryName,
                'number' => $pullRequestNumber
            )
        );

        return $prs;

    }


    public function getStatus($sha)
    {
        $response = $this->_client->get(
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
            $this->_client->put(
                '/repos/:owner/:repo/pulls/:number/merge',
                array(
                    'owner' => $this->repositoryOwner,
                    'repo' => $this->repositoryName,
                    'number' => $number
                ),
                array(
                    'message' => 'test',
                )
            );
            echo("Merged pull $number\n");

        } catch (\Exception $e) {
            $ex = json_decode($e->getMessage());
            $this->_addCommentToPullRequest($number, $ex->message);
            App::dispatchEvent("cannot_merge_pull_request");
        }

    }

    public function addComment($number, $message)
    {
        $this->_client->post(
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

    public function getPullRequestComments($number){
        $pullRequestComments = array();

        $prs = $this->_client->get(
            '/repos/:owner/:repo/issues/:number/comments',
            array(
                'owner' => $this->repositoryOwner,
                'repo' => $this->repositoryName,
                'number' => $number
            )
        );
        foreach ($prs as $pr) {
            $pullRequestComments[] = new PullRequestComment($pr);
        }

        return $pullRequestComments;
    }

}