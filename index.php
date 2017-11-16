<?php
error_reporting(E_ALL);
session_start();
require_once 'db_connect.php';

    $login_post = !empty($_POST['login']) ? $_POST['login'] : '';
    $sql = $link->query("SELECT login, password FROM user WHERE login = '" . $login_post . "'");
    $log_pass = $sql->fetch(PDO::FETCH_ASSOC);

    $err = [];
if(isset($_POST['submit_log'])) {

    // проверяем пуста ли поля
    if (empty($_POST['login']) && empty($_POST['password'])) {
        $err[] = "Заполните пустые поля";
    }

    // Проверяем имя пользователя
    if($_POST['login'] !== $log_pass['login']) {
        $err[] = "Вы не правильно ввели имя пользователя";
    }

    // Проверяем пароль
    if($_POST['password'] !== $log_pass['password']) {
        $err[] = "Вы не правильно ввели пароль";
    }

    // проверям логин
    if (!preg_match("/^[a-zA-Z0-9]+$/", $_POST['login'])) {
        $err[] = "Логин может состоять только из букв английского алфавита и цифр";
    }

    if ($_POST['login'] === $log_pass['login'] && $_POST['password'] === $log_pass['password']) {
        $_SESSION["author"] = $_POST['login'];
        header('Location: business.php');
        exit();
    }
}

// Регистрация
if(isset($_POST['submit_reg'])) {

    // проверяем пуста ли поля
    if(empty($_POST['login']) && empty($_POST['password'])){
        $err[] = "Заполните пустые поля";
    }

// проверям логин
    if (!preg_match("/^[a-zA-Z0-9]+$/", $_POST['login'])) {
        $err[] = "Логин может состоять только из букв английского алфавита и цифр";
    }

    if (strlen($_POST['login']) < 3 || strlen($_POST['login']) > 30) {
        $err[] = "Логин должен быть не меньше 3-х символов и не больше 30";
    }

// проверяем, не сущестует ли пользователя с таким именем
    if ($_POST['login'] === $log_pass['login'] && $_POST['password'] === $log_pass['password']) {
        $err[] = "Такой пользователь уже существует в базе данных";
    }

// Если нет ошибок, то добавляем в БД нового пользователя
    if (count($err) == 0) {
        // Убераем лишние пробелы и делаем двойное шифрование
        $pass = trim($_POST['password']);

        $insert = "INSERT INTO user (login, password) VALUES (?, ?)";
        $statement = $link->prepare($insert);
        $statement->execute([$_POST['login'], $pass]);

        $_SESSION["author"] = $_POST['login'];
        header("Location: business.php");
        exit();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Главная</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="wrapper">
    <p class="error">
        <?php if(!empty($err)) echo $err[0]; ?>
    </p>
    <form class="login" action="index.php" method="post">
        <div>
            <label for="login">Логин: </label>
            <input id="login" type="text" name="login">
        </div>
        <div>
            <label for="password">Пароль: </label>
            <input id="password" type="password" name="password">
        </div>
        <input type="submit" value="Войти" name="submit_log">
        <input type="submit" value="Регистрация" name="submit_reg">
    </form>
</div>

</body>

</html>

