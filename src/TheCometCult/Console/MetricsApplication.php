<?php

namespace TheCometCult\Console;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;

use TheCometCult\Console\Command\DevMetricsCommand;

class MetricsApplication extends Application
{
    protected $configuration;

    protected $container;

    /**
     * @param array $configuration
     */
    public function configure(array $configuration)
    {
        $this->configuration = $configuration;

        $this->container = $this->createContainer();

        $devMetricsCommand = new DevMetricsCommand();
        $devMetricsCommand->setConfiguration($this->configuration);
        $devMetricsCommand->setContainer($this->container);

        $this->add($devMetricsCommand);
    }

    protected function createContainer()
    {
        $container = new ContainerBuilder();

        $container
            ->register('metrics.bitbucket', 'TheCometCult\Metrics\Bitbucket\BitbucketMetrics')
            ->addArgument(new Reference('client.bitbucket'));

        $container
            ->register('metrics.github', 'TheCometCult\Metrics\Github\GithubMetrics')
            ->addArgument(new Reference('client.github'));

        $container->register('notifications.slack', 'TheCometCult\Notifications\Slack\SlackNotifications')
            ->addArgument(new Reference('client.slack'))
            ->addArgument(new Reference('slack.formatter'));

        $container
            ->register('client.slack', 'Maknz\Slack\Client')
            ->addArgument('%slack.endpoint%');

        $container
            ->register('slack.formatter', 'TheCometCult\Notifications\Slack\SlackFormatter');

        $container
            ->register('client.github', 'Github\Client')
            ->addMethodCall('authenticate', array('%github.username%', '%github.token%'));


        if (!empty($this->configuration['credentials']['bitbucket'])) {
            $container
                ->register('bitbucket.auth_listener', 'Bitbucket\API\Http\Listener\OAuthListener')
                ->addArgument(array(
                    'oauth_consumer_key' => $this->configuration['credentials']['bitbucket']['oauth1']['key'],
                    'oauth_consumer_secret' => $this->configuration['credentials']['bitbucket']['oauth1']['secret']
                ));

            $container
                ->register('client.curl', 'Buzz\Client\Curl')
                ->addMethodCall('setTimeout', array('%buzz.curl_timeout%'));

            $container
                ->register('bitbucket.client', 'Bitbucket\API\Http\Client')
                ->addArgument(array(), new Reference('client.curl'))
                ->addMethodCall('addListener', array(new Reference('bitbucket.auth_listener')));

            $container
                ->register('client.bitbucket', 'Bitbucket\API\Repositories\Changesets')
                ->addMethodCall('setClient', array(new Reference('bitbucket.client')));
        }


        if (!empty($this->configuration['credentials']['hipchat'])) {
            $container
            ->register('notifications.hipchat', 'TheCometCult\Notifications\HipChat\HipChatNotifications')
            ->addArgument(new Reference('client.hipchat'));

            $container
                ->register('client.hipchat', 'HipChat\HipChat')
                ->addArgument('%hipchat.token%');

            $container->setParameter('hipchat.token', $this->configuration['credentials']['hipchat']['token']);
        }

        $container->setParameter('buzz.curl_timeout', $this->configuration['settings']['buzz']['curl_timeout']);
        $container->setParameter('slack.endpoint', $this->configuration['credentials']['slack']['endpoint']);
        $container->setParameter('github.username', $this->configuration['credentials']['github']['username']);
        $container->setParameter('github.token', $this->configuration['credentials']['github']['token']);

        return $container;
    }
}
