<?php
$result = null;
session_start();
if (!isset($_SESSION['user_id']))
    header('Location: index.php');
else {
    $config = parse_ini_file("config.ini", true);

    $conn = new mysqli(
        $config["db"]["host"],
        $config["db"]["user"],
        $config["db"]["password"],
        $config["db"]["name"]
    );

    if ($conn->connect_error)
        die("Ошибка подключения: " . $conn->connect_error);

    $id = $_SESSION['user_id'];
    $result = $conn->query("select * from users where id = '$id'");

    if ($result->num_rows <= 0)
        die("Пользователь не существует");

    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        $curr = $result->fetch_assoc();

        $query = "update users set ";

        $new = [];

        if ($_POST['login'] !== $curr['login'])
            $new['login'] = "login='" . $_POST['login'] . "'";
        if ($_POST['phone'] !== $curr['phone'])
            $new['phone'] = "phone='" . $_POST['phone'] . "'";
        if ($_POST['email'] !== $curr['email'])
            $new['email'] = "email='" . $_POST['email'] . "'";
        if (!password_verify($_POST['password'], $curr['password']) and $_POST['password'] !== "")
            $new['password'] = "password='" . password_hash($_POST['password'], PASSWORD_BCRYPT) . "'";

        $query .= implode(", ", $new);

        $query .= " where id = '$id'";

        if (str_contains($query, 'login')
            or str_contains($query, 'email')
            or str_contains($query, 'password')
            or str_contains($query, 'phone')
        )
            $conn->query($query);

        header('Location: profile.php');
    }
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Профиль</title>
</head>
<body>
    <?php foreach ($result as $item) {?>
    <h1>Профиль пользователя <?=$item['login']?></h1>
    <form method="post" action="">
        <div>
            <label for="login">Логин</label>
            <input type="text" id="login" name="login" value="<?=$item['login']?>">
        </div>
        <div>
            <label for="phone">Телефон</label>
            <input type="tel" id="phone" name="phone" value="<?=$item['phone']?>">
        </div>
        <div>
            <label for="email">Почта</label>
            <input type="email" id="email" name="email" value="<?=$item['email']?>">
        </div>
        <div>
            <label for="password">Пароль</label>
            <input type="password" id="password" name="password">
        </div>
        <button type="submit">Изменить</button>
    </form>
    <?php }?>
</body>
</html>