<?php
$hostname = "localhost";
$username = "root";
$password = "";
$database = "test";

$connection = new mysqli($hostname, $username, $password, $database);

if ($connection == false) {
    die("Connection failed: " . mysqli_connect_error());
}

function isValidName(string $name) : bool {
    return !empty($name) && preg_match('/^[\p{L}\s]+$/u', $name);
}

function isValidEmail(string $email) : bool {
    return !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL);
}

function isValidPhone(string $phone) : int {
    if (empty($phone)) return false;
    $phone = str_replace(['', '-', '+', '(', ')'], '', $phone);
    return preg_match('/^[0-9]{11}\z/', $phone);
}

if (isValidName($_POST['name']) && isValidPhone($_POST['phone']) && isValidEmail($_POST['email'])) {
    $name = $connection->real_escape_string($_POST['name']);
    $email = $connection->real_escape_string($_POST['email']);
    $phone = $connection->real_escape_string($_POST['phone']);

    $sql_check = "SELECT id FROM orders
                  WHERE name = '$name'
                    AND email = '$email'
                    AND phone = '$phone'
                    AND timecreated > (NOW() - INTERVAL 5 MINUTE)";
    $result = $connection->query($sql_check);

    if ($result->num_rows > 0) {
        echo json_encode(array('success' => 0, 'message' => 'Заявка с такими данными уже была отправлена недавно.'));
    } else {
        $sql_insert = "INSERT INTO orders (name, email, phone) VALUES ('$name', '$email', '$phone')";
        if ($connection->query($sql_insert)) {
            echo json_encode(array('success' => 1, 'message' => 'Заявка успешно отправлена.'));
        } else {
            echo json_encode(array('success' => 0, 'message' => 'Ошибка при отправке заявки: ' . $connection->error));
        }
    }
} else {
    echo json_encode(array('success' => 0, 'message' => 'Введены некорректные данные'));
}
?>