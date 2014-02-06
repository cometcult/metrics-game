<?php

namespace spec\TheCometCult\Metrics\Bitbucket;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BitbucketMetricsSpec extends ObjectBehavior
{
    protected $repo1Commits;

    protected $repo2Commits;

    /**
     * @param Bitbucket\API\Repositories\Changesets $client
     */
    function let($client)
    {
        $this->beConstructedWith($client);

        $repo1Commits = array(
            'changesets' => array(
                array(
                    'author' => 'john doe',
                    'message' => 'something1',
                    'timestamp' => date('Y-m-d H:i:s')
                )
            )
        );

        $repo2Commits = array(
            'changesets' => array(
                array(
                    'author' => 'john doe',
                    'message' => 'something2',
                    'timestamp' => date('Y-m-d H:i:s')
                ),
                array(
                    'author' => 'jane doe',
                    'message' => 'something3',
                    'timestamp' => date('Y-m-d H:i:s')
                )
            )
        );

        $this->repo1Commits = json_encode($repo1Commits);
        $this->repo2Commits = json_encode($repo2Commits);
    }

    function it_is_a_metrics_collector()
    {
        $this->shouldImplement('TheCometCult\Metrics\MetricsCollectorInterface');
    }

    /**
     * @param Bitbucket\API\Repositories\Changesets $client
     */
    function it_should_collect_bitbucket_metrics($client)
    {
        $client->all('author1', 'repo1')
            ->shouldBeCalled()->willReturn($this->repo1Commits);
        $client->all('author1', 'repo2')
            ->shouldBeCalled()->willReturn($this->repo2Commits);

        $this->collect(array('author1' => array('repo1', 'repo2')))
            ->shouldReturn(array('john doe' => 2, 'jane doe' => 1));
    }

    /**
     * @param Bitbucket\API\Repositories\Changesets $client
     */
    function it_should_not_collect_merge_commits($client)
    {
        $repo1Commits = array(
            'changesets' => array(
                array(
                    'author' => 'john doe',
                    'message' => 'Merged something into something',
                    'timestamp' => date('Y-m-d H:i:s', time() + 3600)
                )
            )
        );
        $client->all('author1', 'repo1')
            ->shouldBeCalled()->willReturn(json_encode($repo1Commits));
        $client->all('author1', 'repo2')
            ->shouldBeCalled()->willReturn($this->repo2Commits);

        $this->collect(array('author1' => array('repo1', 'repo2')))
            ->shouldReturn(array('john doe' => 1, 'jane doe' => 1));
    }

    /**
     * @param Bitbucket\API\Repositories\Changesets $client
     */
    function it_should_not_collect_commits_older_then_24h($client)
    {
        $repo1Commits = array(
            'changesets' => array(
                array(
                    'author' => 'john doe',
                    'message' => 'something3',
                    'timestamp' => date('Y-m-d H:i:s', time() - 48 * 60 * 60)
                )
            )
        );
        $client->all('author1', 'repo1')
            ->shouldBeCalled()->willReturn(json_encode($repo1Commits));
        $client->all('author1', 'repo2')
            ->shouldBeCalled()->willReturn($this->repo2Commits);

        $this->collect(array('author1' => array('repo1', 'repo2')))
            ->shouldReturn(array('john doe' => 1, 'jane doe' => 1));
    }
}
