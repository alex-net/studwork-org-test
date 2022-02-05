<?php

return [
    'class' => 'yii\db\Connection',
    // CREATE TABLE notes (id integer  primary key, dcr integer not null,dpubl integer not null, user integer not null, capt text, body text  );
    // create index crind on notes (dcr)
    // create index publind on notes (dpubl)
    'dsn' => 'sqlite:/home/alex/www/yii2-test/test.db',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
