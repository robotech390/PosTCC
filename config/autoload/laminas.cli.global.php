<?php

use Application\Command\ListenMqttCommand;

return [
    'laminas-cli' => [
        'commands' => [
            'app:mqtt:listen' => ListenMqttCommand::class,
        ],
    ],
];