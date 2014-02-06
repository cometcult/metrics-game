<?php

namespace TheCometCult\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Github\Client;
use HipChat\HipChat;

class DevMetricsCommand extends Command
{
    protected $container;

    protected $configuration;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param array $configuration
     */
    public function setConfiguration(array $configuration)
    {
        $this->configuration = $configuration;
    }

    protected function configure()
    {
        $this
            ->setName('thecometcult:metrics')
            ->setDescription('Run the Metrics Game');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Gathering metrics</comment>');

        $githubResults = array();
        if (!empty($this->configuration['repositories']['github'])) {
            try {
                $githubMetrics = $this->container->get('metrics.github');
                $githubResults = $githubMetrics->collect($this->configuration['repositories']['github']);
            } catch (\Exception $e) {
                $output->writeln(sprintf('<error>Github error occured: %s</error>', $e->getMessage()));
            }
        }

        $bitbucketResults = array();
        if (!empty($this->configuration['repositories']['bitbucket'])) {
            try {
                $bitbucketMetrics = $this->container->get('metrics.bitbucket');
                $bitbucketResults = $bitbucketMetrics->collect($this->configuration['repositories']['bitbucket']);
            } catch (\Exception $e) {
                $output->writeln(sprintf('<error>Bitbucket error occured: %s</error>', $e->getMessage()));
            }
        }

        $results = $this->mergeResults(array($githubResults, $bitbucketResults));

        $output->writeln('<comment>Sending metrics notifications</comment>');

        try {
            $hipChatNotifications = $this->container->get('notifications.hipchat');
            $hipChatNotifications->send($results, array(
                'room' => $this->configuration['credentials']['hipchat']['room'],
                'from' => $this->configuration['credentials']['hipchat']['from'],
            ));
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>HipChat error occured: %s</error>', $e->getMessage()));
        }

        $output->writeln('<info>Metrics Done!</info>');
    }

    protected function mergeResults(array $results)
    {
        $mergedResults = array();
        foreach ($results as $result) {
            foreach ($result as $key => $value) {
                if (empty($mergedResults[$key])) {
                    $mergedResults[$key] = $value;
                } else {
                    $mergedResults[$key] += $value;
                }
            }
        }

        return $mergedResults;
    }
}