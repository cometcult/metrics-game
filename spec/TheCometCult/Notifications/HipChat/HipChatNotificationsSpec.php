<?php

namespace spec\TheCometCult\Notifications\HipChat;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use HipChat\HipChat;

class HipChatNotificationsSpec extends ObjectBehavior
{
    /**
     * @param HipChat\HipChat $client
     */
    function let($client)
    {
        $this->beConstructedWith($client);
    }

    function it_is_a_notification_sender()
    {
        $this->shouldImplement('TheCometCult\Notifications\NotificationsSenderInterface');
    }

    /**
     * @param HipChat\HipChat $client
     */
    function it_should_send_metrics_to_hipchat($client)
    {
        $expectedMessage = 'The Winner Is: <b>jane doe!</b><br/>';
        $expectedMessage .= 'Number of commits from last 24h:<br/>';
        $expectedMessage .= 'jane doe = 5<br/>';
        $expectedMessage .= 'john doe = 3<br/>';
        $expectedMessage .= 'john snow = 2<br/>';
        $expectedMessage .= '<b>The Game is on Biatch!</b>';

        $client->message_room('Some Room', 'Dev Metrics', $expectedMessage, true, HipChat::COLOR_PURPLE)
            ->shouldBeCalled();

        $metrics = array(
            'john doe' => 3,
            'jane doe' => 5,
            'john snow' => 2
        );
        $this->send($metrics, array('room' => 'Some Room', 'from' => 'Dev Metrics'));
    }

    /**
     * @param HipChat\HipChat $client
     */
    function it_should_send_no_winners_metrics_to_hipchat($client)
    {
        $expectedMessage = 'No winners today :(<br/>';
        $expectedMessage .= 'Number of commits from last 24h:<br/>';
        $expectedMessage .= 'john snow = 0<br/>';
        $expectedMessage .= 'jane doe = 0<br/>';
        $expectedMessage .= 'john doe = 0<br/>';
        $expectedMessage .= '<b>The Game is on Biatch!</b>';

        $client->message_room('Some Room', 'Dev Metrics', $expectedMessage, true, HipChat::COLOR_PURPLE)
            ->shouldBeCalled();

        $metrics = array(
            'john doe' => 0,
            'jane doe' => 0,
            'john snow' => 0
        );
        $this->send($metrics, array('room' => 'Some Room', 'from' => 'Dev Metrics'));
    }

    /**
     * @param HipChat\HipChat $client
     */
    function it_should_send_ex_aequo_winners_metrics_to_hipchat($client)
    {
        $expectedMessage = 'The winners are: <b>john doe, jane doe!</b><br/>';
        $expectedMessage .= 'Number of commits from last 24h:<br/>';
        $expectedMessage .= 'john doe = 2<br/>';
        $expectedMessage .= 'jane doe = 2<br/>';
        $expectedMessage .= 'john snow = 0<br/>';
        $expectedMessage .= '<b>The Game is on Biatch!</b>';

        $client->message_room('Some Room', 'Dev Metrics', $expectedMessage, true, HipChat::COLOR_PURPLE)
            ->shouldBeCalled();

        $metrics = array(
            'john doe' => 2,
            'jane doe' => 2,
            'john snow' => 0
        );
        $this->send($metrics, array('room' => 'Some Room', 'from' => 'Dev Metrics'));
    }
}