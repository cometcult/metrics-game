<?php

namespace TheCometCult\Metrics\Github;

use TheCometCult\Metrics\MetricsCollectorInterface;

use Github\Client;

class GithubMetrics implements MetricsCollectorInterface
{
    /**
     * @var Github\Client
     */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(array $repos)
    {
        $commitsApi = $this->client->api('repos')->commits();

        $metrics = array();
        foreach ($repos as $author => $authorRepos) {
            foreach ($authorRepos as $repo) {
                $repoCommits = $commitsApi->all($author, $repo, array(
                    'since' => date('Y-m-d\TH:i:s\Z', time() - 60 * 60 * 24)));
                foreach ($repoCommits as $commit) {
                    $userName = $commit['commit']['author']['name'];
                    $message = $commit['commit']['message'];
                    $wasAMerge = strpos($message, 'Merge') !== false;
                    if (!$wasAMerge) {
                        if (array_key_exists($userName, $metrics)) {
                            $metrics[$userName]++;
                        } else {
                            $metrics[$userName] = 1;
                        }
                    }
                }
            }
        }

        return $metrics;
    }
}
