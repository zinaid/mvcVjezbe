<?php
$app = new \Core\Framework();

/**
 * @throws Exception
 */
function view($view, $params = [])
{
    global $app;
    return $app->render($view, $params);
}