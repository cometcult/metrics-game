<?php

namespace TheCometCult\Notifications\Slack;

class SlackFormatter
{
    public function format(array $metrics)
    {
        arsort($metrics);
        $message = $this->getWinnerMessage($metrics);
        if (count($metrics)) {
            $message .= "Number of commits from last 24h:\n";
            foreach ($metrics as $userName => $commits) {
                $message .= sprintf("• %s = %d\n", $userName, $commits);
            }
        }
        $message .= '_The Game is on Biatch!_';

        return $message;
    }

    protected function getWinnerMessage($metrics)
    {
        $numberOfCommits = array_values($metrics);
        $max = reset($numberOfCommits);

        if (!$max) {
            return "No winners today :(\n";
        }

        $winners = array_keys($metrics, $max);

        if (count($winners) > 1) {
            $winners = implode(", ", $winners);

            return sprintf("The winners are: _%s!_\n", $winners);
        } else {
            $winners = array_keys($metrics);

            return sprintf("The Winner Is: _%s!_\n", reset($winners));
        }
    }
}
