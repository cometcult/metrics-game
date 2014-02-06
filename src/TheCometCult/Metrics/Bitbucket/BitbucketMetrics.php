<?php

namespace TheCometCult\Metrics\Bitbucket;

use TheCometCult\Metrics\MetricsCollectorInterface;

use Bitbucket\API\Repositories\Changesets;

class BitbucketMetrics implements MetricsCollectorInterface
{
    /**
     * @var Bitbucket\API\Repositories\Changesets
     */
    protected $client;

    public function __construct(Changesets $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(array $repos)
    {
        $metrics = array();
        foreach ($repos as $author => $authorRepos) {
            foreach ($authorRepos as $repo) {
                $repoCommits = $this->client->all($author, $repo);
                $repoCommits = json_decode($repoCommits);
                foreach ($repoCommits->changesets as $commit) {
                    $time = $commit->timestamp;
                    if (strtotime($time) >= time() - 60 * 60 * 24) {
                        $message = $commit->message;
                        $wasAMerge = strpos($message, 'Merge') !== false;
                        if (!$wasAMerge) {
                            $userName = $commit->author;
                            if (array_key_exists($userName, $metrics)) {
                                $metrics[$userName]++;
                            } else {
                                $metrics[$userName] = 1;
                            }
                        }
                    }
                }
            }
        }

        return $metrics;
    }
}
