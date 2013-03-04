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
        $mockedPullRequestsResponse =  unserialize (file_get_contents($this->fixturesPath . "pullsResponseWith1PR.txt") );

        Phake::when($gitHubApiMock)->get(
            '/repos/:owner/:repo/pulls',
            array(
                'owner' => $this->config->get("github_repository_owner"),
                'repo' => $this->config->get("github_repository_name")
            )
        )->thenReturn($mockedPullRequestsResponse);


     //   \App::singleton($this->app);

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

        //Phake::verify($this->app)->dispatch();

        $this->assertTrue(true);

    }


    public function testOpenPullRequestsReachingMaxOpenPullRequests()
    {
        $gitHubAdapter = new \Library\GitHub\GitHubAdapter($this->config);


        $gitHubApiMock = \Phake::mock("\\Library\\GitHub\\GitHubApi");
        $mockedPullRequestsResponse = unserialize (file_get_contents($this->fixturesPath . "pullsResponseWithMoreThan10PR.txt"));

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

        $this->assertTrue(true);

    }


    public function getStatusTest()
    {
        $shaTest = "abcd";
        $gitHubAdapter = new \Library\GitHub\GitHubAdapter($this->config);


        $gitHubApiMock = \Phake::mock("\\Library\\GitHub\\GitHubApi");
        $mockShaStatusResponse = unserialize (file_get_contents($this->fixturesPath . "pullsResponseWithMoreThan10PR.txt"));

        Phake::when($gitHubApiMock)->get(
            '/repos/:owner/:repo/statuses/:sha',
            array(
                'owner' => $this->repositoryOwner,
                'repo' => $this->repositoryName,
                'sha' => $shaTest
            )
        )->thenReturn($mockShaStatusResponse);


        \App::singleton($this->app);

        $gitHubAdapter->addDependency("gitHubApi", $gitHubApiMock);
        $gitHubAdapter->getStatus($shaTest);



    }

    public function testMerge()
    {

    }


    public function testAddComment()
    {

    }

    public function testGetPullRequestComments()
    {

    }


}