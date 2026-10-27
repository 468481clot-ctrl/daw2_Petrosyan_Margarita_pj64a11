
<?php

function authenticateUser($username, $password) {
    $file = __DIR__ . '/../../users/users.json';

    if (!file_exists($file)) {
        return false;
    }

    $content = file_get_contents($file);
    $users = json_decode($content, true);

    foreach ($users as $user) {
        if ($user['user'] == $username &&
            password_verify($password, $user['password'])) {
            return true;
        }
    }

    return false;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (authenticateUser($username, $password)) {
        session_start();
        $_SESSION['authenticated'] = true;

        header('Location: ../client/operations.html');
        exit;
    } else {
        header('Location: ../client/index.html?error=authentication');
        exit;
    }
}

?>
