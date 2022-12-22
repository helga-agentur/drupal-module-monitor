<?php

$projects =  [
  [
    'identifier' => 'Bystronic',
    'environments' => [
      [
        'identifier' => 'Live',
        'host' => 'https://bystronic.com',
        'timestamp' => time(),
        'drupal' => [
          'version' => '9.4.2',
          'risk' => 'no',
        ],
        'php' => [
          'version' => '7.4.1',
          'risk' => 'low',
        ],
        'node' => [
          'version' => '18.9.0',
        ],
        'git' => [
          'head' => 'v1.0.2',
          'headUrl' => '#',
          'commit' => '9739f87',
          'commitUrl' => '#',
          'commitDate' => '02.11.2022',
        ],
      ],
      [
        'identifier' => 'Integration',
        'host' => 'https://integration.bystronic.com',
        'timestamp' => time(),
        'drupal' => [
          'version' => '9.4.2',
          'risk' => 'moderate',
        ],
        'php' => [
          'version' => '7.4.1',
          'risk' => 'high',
        ],
        'git' => [
          'head' => 'v1.0.3',
          'headUrl' => '#',
          'commit' => '04988d3',
          'commitUrl' => '#',
          'commitDate' => '02.11.2022',
        ],
        'node' => [
          'version' => '18.9.0',
        ],
      ],

    ],
  ],
  [
    'identifier' => 'RONDO',
    'environments' => [
      [
        'identifier' => 'Live',
        'host' => 'https://rondo-online.com',
        'drupal' => [
          'version' => '9.2.2',
          'risk' => 'moderate',
        ],
        'php' => [
          'version' => '8.1.1',
          'risk' => 'high',
        ],
        'git' => [
          'head' => 'v1.2.3',
          'headUrl' => '#',
          'commit' => '37590a8f',
          'commitUrl' => '#',
          'commitDate' => '02.11.2022',
        ],
        'node' => [
          'version' => '18.9.0',
        ],
        'timestamp' => time(),
      ],
      [
        'identifier' => 'Integration',
        'host' => 'https://integration.rondo-online.com',
        'drupal' => [
          'version' => '9.2.2',
          'risk' => 'no',
        ],
        'php' => [
          'version' => '8.1.1',
          'risk' => 'moderate',
        ],
        'git' => [
          'head' => 'v1.3.0',
          'headUrl' => '#',
          'commit' => '37590a8f',
          'commitUrl' => '#',
          'commitDate' => '02.11.2022',
        ],
        'node' => [
          'version' => '18.9.0',
        ],
        'timestamp' => time(),
      ],
      [
        'identifier' => 'Stage',
        'host' => 'https://stage.rondo-online.com',
        'drupal' => [
          'version' => '9.2.2',
          'risk' => 'no',
        ],
        'php' => [
          'version' => '8.1.1',
          'risk' => 'no',
        ],
        'git' => [
          'head' => '6392bf3',
          'headUrl' => '#',
          'commit' => '1564979',
          'commitUrl' => '#',
          'commitDate' => '02.11.2022',
        ],
        'node' => [
          'version' => '18.9.0',
        ],
        'timestamp' => time(),
      ],
    ],
  ],
];

