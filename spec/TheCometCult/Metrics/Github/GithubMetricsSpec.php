<?php

namespace spec\TheCometCult\Metrics\Github;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GithubMetricsSpec extends ObjectBehavior
{
    protected $repo1Commits;

    protected $repo2Commits;

    /**
     * @param Github\Client $client
     */
    function let($client)
    {
        $this->beConstructedWith($client);

        $this->repo1Commits = array(
            array(
                'commit' => array(
                    'author' => array(
                        'name' => 'john doe'
                    ),
                    'message' => 'something1'
                )
            )
        );

        $this->repo2Commits = array(
            array(
                'commit' => array(
                    'author' => array(
                        'name' => 'john doe'
                    ),
                    'message' => 'something2'
                )
            ),
            array(
                'commit' => array(
                    'author' => array(
                        'name' => 'jane doe'
                    ),
                    'message' => 'something3'
                )
            )
        );
    }

    function it_is_a_metrics_collector()
    {
        $this->shouldImplement('TheCometCult\Metrics\MetricsCollectorInterface');
    }

    /**
     * @param Github\Client $client
     * @param Github\Api\Repo $repoApi
     * @param Github\Api\Repository\Commits $commitsApi
     */
    function it_should_collect_github_metrics($client, $repoApi, $commitsApi)
    {
        $client->api('repos')->willReturn($repoApi);
        $repoApi->commits()->willReturn($commitsApi);

        $commitsApi->all('author1', 'repo1', Argument::type('array'))
            ->shouldBeCalled()->willReturn($this->repo1Commits);

        $commitsApi->all('author1', 'repo2', Argument::type('array'))
            ->shouldBeCalled()->willReturn($this->repo2Commits);


        $this->collect(array('author1' => array('repo1', 'repo2')))
            ->shouldReturn(array('john doe' => 2, 'jane doe' => 1));
    }

    /**
     * @param Github\Client $client
     * @param Github\Api\Repo $repoApi
     * @param Github\Api\Repository\Commits $commitsApi
     */
    function it_should_not_collect_merge_commits($client, $repoApi, $commitsApi)
    {
        $client->api('repos')->willReturn($repoApi);
        $repoApi->commits()->willReturn($commitsApi);

        $commitsApi->all('author1', 'repo1', Argument::type('array'))
            ->shouldBeCalled()->willReturn($this->repo1Commits);

        $this->repo2Commits[0]['commit']['message'] = 'Merged something into something';
        $commitsApi->all('author1', 'repo2', Argument::type('array'))
            ->shouldBeCalled()->willReturn($this->repo2Commits);


        $this->collect(array('author1' => array('repo1', 'repo2')))
            ->shouldReturn(array('john doe' => 1, 'jane doe' => 1));
    }
}
