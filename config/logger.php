<?php

return [
    'name' => 'wheelmap-jobs',
    'path' => __DIR__ . '/../storage/logs/jobs.log',
    'level' => \Monolog\Logger::DEBUG,
    /**
     * Uncomment the following line & add your webhook URL
     * if you want to send notifications to a Slack webhook
     */
    //'slack' => '[YOUR SLACK WEBHOOK URL]',
];
