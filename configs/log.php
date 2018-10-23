<?php
$log['master'] = array(
    'type' => 'FileLog',
    'file' => ROOT_PATH . '/log/app.log',
);
$log['Test'] = array(
    'type' => 'FileLog',
    'file' => ROOT_PATH . '/log/Test.log',
);
return $log;