<?php
require_once 'from_user_data.php';
session_start();

$err = [];
if(isset($_POST['submit_log'])) {

// проверяем пуста ли поля
    if (empty($log) || empty($pass)) {
        $err[] = "Заполните пустые поля";
    }

// проверям логин
    if (!preg_match("/^[a-zA-Z0-9]+$/", $log)) {
        $err[] = "Логин может состоять только из букв английского алфавита и цифр";
    }

    if($log !== $user_log && $pass !== $user_pass) {
        $err[] = 'Такой пользовател не найден';
    }

    if ($log === $user_log && $pass === $user_pass) {
        $_SESSION["author"] = $log;
        header('Location: business.php');
        exit();
    }
}

// Регистрация
if(isset($_POST['submit_reg'])) {

    // проверяем пуста ли поля
    if(empty($log) || empty($pass)){
        $err[] = "Заполните пустые поля";
    }

// проверям логин
    if (!preg_match("/^[a-zA-Z0-9]+$/", $log)) {
        $err[] = "Логин может состоять только из букв английского алфавита и цифр";
    }

    if (strlen($log) < 3 || strlen($log) > 30) {
        $err[] = "Логин должен быть не меньше 3-х символов и не больше 30";
    }

// проверяем, не сущестует ли пользователя с таким именем
    if ($log === $user_log || $pass === $user_pass) {
        $err[] = "Такой пользователь уже существует в базе данных";
    }

// Если нет ошибок, то добавляем в БД нового пользователя
    if (count($err) == 0) {
        // Убераем лишние пробелы и делаем двойное шифрование
        $pass = trim($pass);

        $insert = "INSERT INTO user (login, password) VALUES (?, ?)";
        $statement = $link->prepare($insert);
        $statement->execute([$log, $pass]);

        $_SESSION["author"] = $log;
        header("Location: business.php");
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
    <p class="error"><?php if(isset($_POST['submit_log']) || isset($_POST['submit_reg'])) echo $err[0]; ?></p>
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

