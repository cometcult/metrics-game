<?php

namespace TheCometCult\Notifications;

interface NotificationsSenderInterface
{
    /**
     * @param array $metrics array of author => number of commits
     * @param array $params array of additional parameters
     */
    public function send(array $metrics, array $params = array());
}