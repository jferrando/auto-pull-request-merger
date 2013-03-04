<?php

namespace Tests;

require_once __DIR__ . "/../../BaseTestDefinition.php";

use Library\GitHub;
use Phake;

class GitHubAdapterTest extends \Tests\BaseTestDefinition
{


    public function setUp()
    {
        parent::setUp();

    }

    public function testOpenPullRequestsNotReachingMaxOpenPullRequests()
    {

        $gitHubAdapter = new \Library\GitHub\GitHubAdapter($this->config);


        $gitHubApiMock = \Phake::mock("\\Library\\GitHub\\GitHubApi");
        $mockedPullRequestsResponse = unserialize(file_get_contents($this->fixturesPath . "pullsResponseWith1PR.txt"));

        Phake::when($gitHubApiMock)->get(
            '/repos/:owner/:repo/pulls',
            array(
                'owner' => $this->config->get("github_repository_owner"),
                'repo' => $this->config->get("github_repository_name")
            )
        )->thenReturn($mockedPullRequestsResponse);


        $gitHubAdapter->addDependency("gitHubApi", $gitHubApiMock);
        $gitHubAdapter->openPullRequests();

        Phake::verify($gitHubApiMock)->auth(
            $this->config->get("github_user"),
            $this->config->get("github_password"),
            \Library\GitHub\GitHubApi::AUTH_HTTP
        );


        Phake::verify($gitHubApiMock)->get(
            '/repos/:owner/:repo/pulls',
            array(
                'owner' => $this->config->get("github_repository_owner"),
                'repo' => $this->config->get("github_repository_name")
            )
        );

        $this->assertEquals("This is a test with verifications", "This is a test with verifications");

    }


    public function testOpenPullRequestsReachingMaxOpenPullRequests()
    {
        $gitHubAdapter = new \Library\GitHub\GitHubAdapter($this->config);


        $gitHubApiMock = \Phake::mock("\\Library\\GitHub\\GitHubApi");
        $mockedPullRequestsResponse = unserialize(
            file_get_contents($this->fixturesPath . "pullsResponseWithMoreThan10PR.txt")
        );

        Phake::when($gitHubApiMock)->get(
            '/repos/:owner/:repo/pulls',
            array(
                'owner' => $this->config->get("github_repository_owner"),
                'repo' => $this->config->get("github_repository_name")
            )
        )->thenReturn($mockedPullRequestsResponse);


        \App::singleton($this->app);

        $gitHubAdapter->addDependency("gitHubApi", $gitHubApiMock);
        $gitHubAdapter->openPullRequests();

        Phake::verify($gitHubApiMock)->auth(
            $this->config->get("github_user"),
            $this->config->get("github_password"),
            \Library\GitHub\GitHubApi::AUTH_HTTP
        );


        Phake::verify($gitHubApiMock)->get(
            '/repos/:owner/:repo/pulls',
            array(
                'owner' => $this->config->get("github_repository_owner"),
                'repo' => $this->config->get("github_repository_name")
            )
        );

        Phake::verify($this->app)->dispatch("too_many_open_requests", null);

        $this->assertEquals("This is a test with verifications", "This is a test with verifications");


    }


    public function testMergeSuccess()
    {
        $gitHubAdapter = new \Library\GitHub\GitHubAdapter($this->config);
        $number = "1234";

        $gitHubApiMock = \Phake::mock("\\Library\\GitHub\\GitHubApi");


        Phake::when($gitHubApiMock)->put(
            '/repos/:owner/:repo/pulls/:number/merge',
            array(
                'owner' => $this->config->get("github_repository_owner"),
                'repo' => $this->config->get("github_repository_name"),
                'number' => $number
            ),
            array(
                'message' => 'merged automatically',
            )
        )->thenReturn(true);


        \App::singleton($this->app);

        $gitHubAdapter->addDependency("gitHubApi", $gitHubApiMock);
        $gitHubAdapter->merge($number);

        Phake::verify($gitHubApiMock)->auth(
            $this->config->get("github_user"),
            $this->config->get("github_password"),
            \Library\GitHub\GitHubApi::AUTH_HTTP
        );

        Phake::verify($this->app)->dispatch("log", "Merged pull $number");
        Phake::verify($this->app)->dispatch("pull_request_merged", null);
        $this->assertEquals("This is a test with verifications", "This is a test with verifications");
    }


    public function testMergeException()
    {
        $gitHubAdapter = new \Library\GitHub\GitHubAdapter($this->config);
        $number = "1234";
        $exceptionMessage = "error 123";
        $gitHubApiMock = \Phake::mock("\\Library\\GitHub\\GitHubApi");


        Phake::when($gitHubApiMock)->put(
            '/repos/:owner/:repo/pulls/:number/merge',
            array(
                'owner' => $this->config->get("github_repository_owner"),
                'repo' => $this->config->get("github_repository_name"),
                'number' => $number
            ),
            array(
                'message' => 'merged automatically',
            )
        )->thenThrow(new \Exception($exceptionMessage));


        \App::singleton($this->app);

        $gitHubAdapter->addDependency("gitHubApi", $gitHubApiMock);
        $gitHubAdapter->merge($number);

        Phake::verify($gitHubApiMock)->auth(
            $this->config->get("github_user"),
            $this->config->get("github_password"),
            \Library\GitHub\GitHubApi::AUTH_HTTP
        );

        Phake::verify($this->app)->dispatch("cannot_merge_pull_request", $exceptionMessage);
        $this->assertEquals("This is a test with verifications", "This is a test with verifications");
    }


}