<?php
require_once 'db_connect.php';

$log = htmlspecialchars($_POST['login']);
$pass = htmlspecialchars($_POST['password']);
$sql = $link->query("SELECT login, password FROM user WHERE login = '". $log ."'");
$log_pass = $sql->fetch(PDO::FETCH_ASSOC);
$user_log = $log_pass['login'];
$user_pass = $log_pass['password'];

