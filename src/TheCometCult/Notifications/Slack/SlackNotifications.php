<?php

namespace TheCometCult\Notifications\Slack;

use TheCometCult\Notifications\NotificationsSenderInterface;

class SlackNotifications implements NotificationsSenderInterface
{
    /**
     * @var Maknz\Slack\Client
     */
    protected $client;

    /**
     * @var SlackFormatter
     */
    protected $formatter;

    public function __construct(\Maknz\Slack\Client $client, SlackFormatter $formatter)
    {
        $this->client = $client;
        $this->formatter = $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function send(array $metrics, array $params = array())
    {
        $message = $this->formatter->format($metrics);

        $this->client->to($params['room'])->from($params['from'])->send($message);
    }
}
