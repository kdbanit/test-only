<?php
session_start();
if (isset($_SESSION["user_id"]))
    header("Location: profile.php");
if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $password = $_POST['password'];
        $repeat_password = $_POST['repeat_password'];

        if ($password === $repeat_password)
        {
            $config = parse_ini_file("config.ini", true);

            $conn = new mysqli(
                $config["db"]["host"],
                $config["db"]["user"],
                $config["db"]["password"],
                $config["db"]["name"],
                $config["db"]["port"]
            );

            if ($conn->connect_error)
                die("Ошибка подключения: " . $conn->connect_error);

            $login = $_POST['login'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $password = password_hash($password, PASSWORD_BCRYPT);

            $result = $conn->query(
                "select * from users where login = '$login' or email = '$email' or phone = '$phone'"
            );

            if ($result->num_rows <= 0) {
                if ($conn->query(
                    "insert into users (login, phone, email, password) 
                       values ('$login', '$phone', '$email', '$password')"
                ))
                {
                    echo "Вы успешно зарегистрированы";
                    header("Location: login.php");
                }
                else
                    die("Ошибка регистрации: " . $conn->error);
            } else
                die("Пользователь уже существует");

            $conn->close();
        }
        else
            echo "Пароль и повтор пароля не совпадают";;
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Регистрация</title>
</head>
<body>
<h1>Регистрация</h1>
<form method="post" action="">
    <div>
        <label for="login">Логин</label>
        <input type="text" id="login" name="login" required>
    </div>
    <div>
        <label for="phone">Телефон</label>
        <input type="tel" id="phone" name="phone" required>
    </div>
    <div>
        <label for="email">Почта</label>
        <input type="email" id="email" name="email" required>
    </div>
    <div>
        <label for="password">Пароль</label>
        <input type="password" id="password" name="password" required>
    </div>
    <div>
        <label for="repeat_password">Повтор пароля</label>
        <input type="password" id="repeat_password" name="repeat_password" required>
    </div>
    <button type="submit">Зарегистрироваться</button>
</form>
</body>
</html>