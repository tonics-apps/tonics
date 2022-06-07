<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Default Configuration
    |--------------------------------------------------------------------------
    |
    | Everything defined in the "default" config will be used
    | for every module, otherwise, we use the config defined in each module,
    |
    */

    'default' => [

        /*
        |--------------------------------------------------------------------------
        | Module Structure
        |--------------------------------------------------------------------------
        |
        | In case your desired module structure differs
        | from the default structure defined here
        | feel free to change it the way you like it,
        |
        */

        'structure' => [
            'controllers' => 'Controllers',
            'resources' => 'Http/Resources',
            'requests' => 'Http/Requests',
            'models' => 'Models',
            'mails' => 'Mail',
            'notifications' => 'Notifications',
            'events' => 'Events',
            'listeners' => 'EventHandlers',
            'observers' => 'Observers',
            'jobs' => 'Jobs',
            'rules' => 'Rules',
            'views' => 'Views',
            'translations' => 'resources/lang',
            'Routes' => 'Routes',
            'migrations' => 'database/migrations',
            'seeds' => 'database/seeds',
            'factories' => 'database/factories',
            'helpers' => '',
        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | Module Specific Configuration
    |--------------------------------------------------------------------------
    |
    | In the "instances" config you can disable individual modules
    | plus also determine which route you want enable
    |
    */

    'instances' => [
    ],
];
