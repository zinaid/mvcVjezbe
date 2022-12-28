<?php

use Core\Framework;

$app = new Framework();

$app::get('/phpmvc', 'HomeController', 'home');
$app::post('/phpmvc', 'HomeController', 'home');
$app->run();