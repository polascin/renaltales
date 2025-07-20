<?php
// Debugging
echo '<h1>Debug Information</h1><hr>';
echo '<br>REQUEST:<br><pre>' . var_dump($_REQUEST) . '</pre><hr>';
echo 'ENVIRONMENT:<br><pre>' . var_dump($_ENV) . '</pre><hr>';
echo 'POST:<br><pre>' . var_dump($_POST) . '</pre><hr>';
echo 'GET:<br><pre>' . var_dump($_GET) . '</pre><hr>';
echo 'FILES:<br><pre>' . var_dump($_FILES) . '</pre><hr>';
echo 'COOKIE:<br><pre>' . var_dump($_COOKIE) . '</pre><hr>';
echo 'SESSION:<br><pre>' . var_dump($_SESSION) . '</pre><hr>';
echo 'CURRENT SCRIPT:<br><pre>' . var_dump($_SERVER['SCRIPT_FILENAME']) . '</pre><hr>';
echo 'CURRENT DIRECTORY:<br><pre>' . var_dump(getcwd()) . '</pre><hr>';
echo __DIR__ . '<br><pre>' . var_dump(__DIR__) . '</pre><hr>';
echo dirname(__DIR__) . '<br><pre>' . var_dump(dirname(__DIR__)) . '</pre><hr>';
echo '<br><br><br>';