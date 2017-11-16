<?php
error_reporting(E_ALL);
session_start();
require_once 'db_connect.php';

if(isset($_GET['submit']) && !empty($_GET['business'])){
    $user_id = $link->query("SELECT id FROM user WHERE login = '". $_SESSION['author'] ."'");
    $user_array = $user_id->fetch(PDO::FETCH_ASSOC);
    $int_id = (int) $user_array['id'];

    $business = $_GET['business'];

    $insert = "INSERT INTO business(user_id,content,status,date) VALUES (? ,? ,? ,NOW())";
    $statement = $link->prepare($insert);
    $statement->execute([$int_id, $business, 0]);
    header('Location: business.php');
}


$statement = $link->prepare("SELECT * FROM business");
$statement->execute();

$qq = $link->query("SELECT * FROM business");

// Изменение записи
if(isset($_GET['id']) && !empty($_POST['business']) && isset($_POST['save'])){
    $update_content = "UPDATE business SET content = ? WHERE id=?";
    $statement = $link->prepare($update_content);
    $statement->execute([$_POST['business'], $_GET['id']]);
header('Location: business.php');
}

// Изменение статуса
if(isset($_GET['id']) && ($_GET['action']) == 'done'){
$update_status = "UPDATE business SET status=1 WHERE id=?";
$statement = $link->prepare($update_status);
$statement->execute([$_GET['id']]);
header('Location: business.php');
}

// Удаление записи
if(isset($_GET['id']) && $_GET['action'] == 'delete'){
    $delete = "DELETE FROM business WHERE id = ?";
    $statement = $link->prepare($delete);
    $statement->execute([$_GET['id']]);
    header('Location: business.php');
    exit();
}

// Получить всех пользователей
    $reg = $link->query("SELECT login,id FROM user");
    $users = $reg->fetchAll(PDO::FETCH_ASSOC);

// Добавить ответственного пользователя
if(isset($_POST['submit_id'])){
    $assigned_id = $link->prepare("UPDATE business SET assigned_user_id=? WHERE id = ?");
    $assigned_id->execute([$_POST['assigned_user_id'], $_GET['user']]);
    header('Location: business.php');
}

$v = $link->query("SELECT id FROM user WHERE login = '".$_SESSION['author']."'");
$respon_user = $v->fetch(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Список дел!!!</title>
    <style>
        table {
            border-spacing: 0;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table td, table th {
            border: 1px solid #ccc;
            padding: 5px;
        }

        table th {
            background: #eee;
        }
    </style>
</head>
<body>
<a href="index.php">Выход</a>
<h1>Список дел на сегодня</h1>
<?php if(isset($_GET['id']) && ($_GET['action']) == 'edit') :?>
    <form method="POST" action="business.php?id=<?= $_GET['id']?>">
        <input type="text" name="business" placeholder="Обновить запись">
        <input type="submit" name="save" value="Сохранить">
    </form>
<?php else : ?>
    <form method="GET" action="business.php">
        <input type="text" name="business" placeholder="Новая запись">
        <input type="submit" name="submit" value="Добавить">
    </form>
<?php endif; ?>
    <table>
        <tr>
            <th>Описание задачи</th>
            <th>Статус</th>
            <th>Дата добавления</th>
            <th>Редактирование</th>
            <th>Ответственный</th>
            <th>Автор</th>
            <th>Закрепить задачу за пользователем</th>
        </tr>
        <?php while($row = $statement->fetch(PDO::FETCH_ASSOC)): if($row['user_id'] === $respon_user['id']):?>
            <tr>
            <td><?= htmlspecialchars($row['content']); ?></td>
            <td><?= htmlspecialchars($status = ($row['status'] == 0) ? 'Не выполнено' : 'Выполнено'); ?></td>
            <td><?= htmlspecialchars($row['date'])?></td>
            <td>
                <a href="?id=<?= $row['id']; ?>&action=edit">Изменить</a>
                <a href="?id=<?= $row['id']; ?>&action=done">Выполнить</a>
                <a href="?id=<?= $row['id']; ?>&action=delete">Удалить</a>
            </td>
            <td><?php
                $respons = $link->query("SELECT login FROM user WHERE id = '".$row['assigned_user_id']."'");
                $responsible = $respons->fetch(PDO::FETCH_ASSOC);
                echo $echo = !empty($row['assigned_user_id'])? $responsible['login']:$_SESSION['author'];
            ?></td>
            <td>
                <?php
                    $author_get = $link->query("SELECT login FROM user WHERE id = '".$row['user_id'] ."'");
                    $author_id = $author_get->fetch(PDO::FETCH_ASSOC);
                    echo htmlspecialchars($author_id['login']);
                ?></td>
            <td>

                <form method="post" action="business.php?user=<?= $row['id']; ?>">
                    <select name="assigned_user_id">
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id']; ?>"><?= htmlspecialchars($user['login']); ?></option>
                        <?php endforeach; ?>
                    </select><br>
                    <input type="submit" name="submit_id" value="Переложить ответственность">
                </form>

            </td>
            </tr>
        <?php endif; endwhile; ?>

    </table>
    <h3>Также, посмотрите, что от Вас требуют другие люди:</h3>
    <table>
       <tr>
           <th>Описание задачи</th>
           <th>Статус</th>
           <th>Дата добавления</th>
           <th>Редактирование</th>
           <th>Ответственный</th>
           <th>Автор</th>
       </tr>
        <?php  while($row = $qq->fetch(PDO::FETCH_ASSOC)):
            if($row['assigned_user_id'] == $respon_user['id'] ):
        ?>
        <tr>
            <td><?= htmlspecialchars($row['content']); ?></td>
            <td><?= htmlspecialchars($status = ($row['status'] == 0) ? 'Не выполнено' : 'Выполнено'); ?></td>
            <td><?= htmlspecialchars($row['date'])?></td>
            <td>
                <a href="?id=<?= $row['id']; ?>&action=edit">Изменить</a>
                <a href="?id=<?= $row['id']; ?>&action=delete">Удалить</a>
            </td>
            <td><?php
                $respons = $link->query("SELECT login FROM user WHERE id = '".$row['assigned_user_id']."'");
                $responsible = $respons->fetch(PDO::FETCH_ASSOC);
                echo $responsible['login'];
                ?></td>
            <td>
                <?php
                    $author_get = $link->query("SELECT login FROM user WHERE id = '".$row['user_id'] ."'");
                    $author_id = $author_get->fetch(PDO::FETCH_ASSOC);
                    echo htmlspecialchars($author_id['login']);
                ?>
            </td>
        <?php endif; endwhile; ?>
    </table>
</body>
</html>