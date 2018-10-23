<?php
$log['master'] = array(
    'type' => 'FileLog',
    'file' => WEBPATH . '/logs/app.log',
);

$log['Test'] = array(
    'type' => 'FileLog',
    'file' => WEBPATH . '/logs/Test.log',
);

return $log;