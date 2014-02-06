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

        $container
            ->register('notifications.hipchat', 'TheCometCult\Notifications\HipChat\HipChatNotifications')
            ->addArgument(new Reference('client.hipchat'));

        $container
            ->register('client.hipchat', 'HipChat\HipChat')
            ->addArgument('%hipchat.token%');

        $container
            ->register('client.github', 'Github\Client')
            ->addMethodCall('authenticate', array('%github.username%', '%github.token%'));

        $container
            ->register('client.bitbucket', 'Bitbucket\API\Repositories\Changesets')
            ->addArgument(new Reference('client.curl'))
            ->addMethodCall('setCredentials', array(new Reference('bitbucket.credentials')));

        $container
            ->register('bitbucket.credentials', 'Bitbucket\API\Authentication\Basic')
            ->addArgument('%bitbucket.username%')
            ->addArgument('%bitbucket.password%');

        $container
            ->register('client.curl', 'Buzz\Client\Curl')
            ->addMethodCall('setTimeout', array('%buzz.curl_timeout%'));

        $container->setParameter('buzz.curl_timeout', $this->configuration['settings']['buzz']['curl_timeout']);
        $container->setParameter('hipchat.token', $this->configuration['credentials']['hipchat']['token']);
        $container->setParameter('bitbucket.username', $this->configuration['credentials']['bitbucket']['username']);
        $container->setParameter('bitbucket.password', $this->configuration['credentials']['bitbucket']['password']);
        $container->setParameter('github.username', $this->configuration['credentials']['github']['username']);
        $container->setParameter('github.token', $this->configuration['credentials']['github']['token']);

        return $container;
    }
}