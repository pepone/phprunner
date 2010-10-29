<?php

require_once(dirname(__FILE__) . '/../Init.php');
require_once(OZ_PATH . '/src/applications/DemoApplication.php');

$app = new DemoApplication();
$app->main();