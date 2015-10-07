<?php

namespace spec\TheCometCult\Notifications\Slack;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SlackFormatterSpec extends ObjectBehavior
{
    function it_should_format_metrics_for_slack()
    {
        $expectedMessage = 'The Winner Is: _jane doe!_
Number of commits from last 24h:
• jane doe = 5
• john doe = 3
• john snow = 2
_The Game is on Biatch!_';

        $metrics = array(
            'john doe' => 3,
            'jane doe' => 5,
            'john snow' => 2
        );
        $this->format($metrics)->shouldReturn($expectedMessage);
    }

    function it_should_format_metrics_no_winners_for_slack()
    {
        $expectedMessage = 'No winners today :(
Number of commits from last 24h:
• john snow = 0
• jane doe = 0
• john doe = 0
_The Game is on Biatch!_';

        $metrics = array(
            'john doe' => 0,
            'jane doe' => 0,
            'john snow' => 0
        );
        $this->format($metrics)->shouldReturn($expectedMessage);
    }

    function it_should_format_metrics_ex_aequo_winners_for_slack ()
    {
        $expectedMessage = 'The winners are: _john doe, jane doe!_
Number of commits from last 24h:
• john doe = 2
• jane doe = 2
• john snow = 0
_The Game is on Biatch!_';

        $metrics = array(
            'john doe' => 2,
            'jane doe' => 2,
            'john snow' => 0
        );
        $this->format($metrics)->shouldReturn($expectedMessage);
    }
}