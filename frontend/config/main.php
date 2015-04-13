<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                        'class' => 'yii\log\FileTarget',
                        'levels' => ['info'],
                        'categories' => ['callbackInfo'],
                        'logFile' => '@app/runtime/logs/callback.log',
                        'maxFileSize' => 1024 * 2,
                        'maxLogFiles' => 20,
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'db'    => [
                'class' => 'yii\db\Connection',
                'dsn' => 'mysql:host=localhost;dbname=shopify', // MySQL, MariaDB
                //'dsn' => 'sqlite:/path/to/database/file', // SQLite
                //'dsn' => 'pgsql:host=localhost;port=5432;dbname=mydatabase', // PostgreSQL
                //'dsn' => 'cubrid:dbname=demodb;host=localhost;port=33000', // CUBRID
                //'dsn' => 'sqlsrv:Server=localhost;Database=mydatabase', // MS SQL Server, sqlsrv driver
                //'dsn' => 'dblib:host=localhost;dbname=mydatabase', // MS SQL Server, dblib driver
                //'dsn' => 'mssql:host=localhost;dbname=mydatabase', // MS SQL Server, mssql driver
                //'dsn' => 'oci:dbname=//localhost:1521/mydatabase', // Oracle
                'username' => 'root',
                'password' => '5dr4MnVlb',
                'charset' => 'utf8',
        ],
    ],
    'params' => $params,
];
