<?php
session_start();
if (isset($_SESSION['user_id']))
    header('Location: profile.php');

$config = parse_ini_file("config.ini", true);
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    function check_captcha($token): bool
    {
        global $config;
        $ch = curl_init();
        $args = http_build_query([
            "secret" => $config['captcha']['server_key'],
            "token" => $token,
            "ip" => $_SERVER['REMOTE_ADDR'], // Нужно передать IP-адрес пользователя.
            // Способ получения IP-адреса пользователя зависит от вашего прокси.
        ]);
        curl_setopt($ch, CURLOPT_URL, "https://smartcaptcha.yandexcloud.net/validate?$args");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $config['main']['ssl']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);

        $server_output = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) {
            echo "Allow access due to an error: code=$http_code; message=$server_output\n";
            return true;
        }
        $resp = json_decode($server_output);
        return $resp->status === "ok";
    }

    if (isset($_POST['smart-token']))
    {
        $token = $_POST['smart-token'];
        if (check_captcha($token)) {
                $conn = new mysqli(
                    $config["db"]["host"],
                    $config["db"]["user"],
                    $config["db"]["password"],
                    $config["db"]["name"]
                );

                if ($conn->connect_error)
                    die("Ошибка подключения: " . $conn->connect_error);

                $login = $_POST["login"];
                $query = "select * from users where ";

                if (preg_match("/^[0-9+][0-9]+$/", $login))
                    $query .= "phone = $login";
                else
                    $query .= "email = $login";

                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    foreach ($result as $item)
                    {
                        if (password_verify($_POST["password"], $item["password"]))
                        {
                            $_SESSION["user_id"] = $item["id"];
                            echo "Вы успешно авторизовались";
                            header("Location: profile.php");
                        } else
                        {
                            die("Неверный пароль");
                        }
                    }
                } else
                    die("Пользователь не существует");

                $result->free();
            }
        } else {
            http_response_code(403);
            echo "<h1>Invalid captcha</h1>\n";
            header("Location: google.com");
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
    <script src="https://smartcaptcha.yandexcloud.net/captcha.js" defer></script>
    <title>Авторизация</title>
</head>
<body>
<h1>Авторизация</h1>
<form method="post" action="">
    <div>
        <label for="login">Номер телефона или почта</label>
        <input type="text" id="login" name="login" required>
    </div>
    <div>
        <label for="password">Пароль</label>
        <input type="password" id="password" name="password" required>
    </div>
    <div
        id="captcha-container"
        class="smart-captcha"
        data-sitekey=<?=$config['captcha']['data_sitekey']?>
    ></div>
    <button type="submit">Авторизоваться</button>
</form>
</body>
</html>
