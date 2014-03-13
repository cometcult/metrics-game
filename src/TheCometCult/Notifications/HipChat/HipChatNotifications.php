<?php

namespace TheCometCult\Notifications\HipChat;

use TheCometCult\Notifications\NotificationsSenderInterface;

use HipChat\HipChat;

class HipChatNotifications implements NotificationsSenderInterface
{
    /**
     * @var HipChat\HipChat
     */
    protected $client;

    public function __construct(HipChat $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function send(array $metrics, array $params = array())
    {
        arsort($metrics);
        $message = $this->getWinnerMessage($metrics);
        if (count($metrics)) {
            $message .= 'Number of commits from last 24h:<br/>';
            foreach ($metrics as $userName => $commits) {
                $message .= $userName . ' = ' . $commits . '<br/>';
            }
        }
        $message .= '<b>The Game is on Biatch!</b>';

        $this->client->message_room($params['room'], $params['from'], $message, true, HipChat::COLOR_PURPLE);
    }

    protected function getWinnerMessage($metrics)
    {
        $numberOfCommits = array_values($metrics);
        $max = reset($numberOfCommits);

        if (!$max) {
            return 'No winners today :(<br/>';
        }

        $winners = array_keys($metrics, $max);

        if (count($winners) > 1) {
            $winners = implode(", ", $winners);

            return 'The winners are: <b>' . $winners . '!</b><br/>';
        } else {
            $winners = array_keys($metrics);

            return 'The Winner Is: <b>' . reset($winners) . '!</b><br/>';
        }
    }
}
