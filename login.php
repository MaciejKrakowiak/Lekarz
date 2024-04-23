<?php
session_start();
require_once('db.php');

$clientId = $_SESSION['client_id'] ?? null;
$employeeId = $_SESSION['emp_id'] ?? null;
$adminId = $_SESSION['admin_id'] ?? null;
$userLoggedIn = ($clientId != null || $employeeId != null || $adminId != null);

function sanitizeInput($input,$conn) {
    return mysqli_real_escape_string($conn,htmlspecialchars(trim($input)));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="style.css?<?php echo time()?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
</head>
<body>
<nav>
        <div class="logo">Logo</div>
        <ul>
            <?php
            if($userLoggedIn & $adminId===null) 
            {
                echo "<li class='user'><b>Witaj {$userData['name']}</b></li>";
            }
            elseif($adminId!=null) echo "<li class='user'><b>Witaj Adminie</b></li>";
            ?>
            <li><a href="index.php">Strona główna</a></li>
            <li><a href="clinet.php">Wizyty</a></li>
            <?php
                if(!$userLoggedIn)
                {
                    echo "<li><a href='sign-up.php'>Rejestracja</a></li>";
                    echo "<li><a class='active' href='login.php'>Logowanie</a></li>";
                }
                else
                {
                    echo "<li><a href='logout.php'>Wyloguj się</a></li>";
                    echo "<li><a href='change-profile.php'>Twój profil</a></li>";
                }
                if($clientId!=null) echo "<li><a href='favorites.php'>Twoi lekarze</a></li>";
                if($userLoggedIn && ($employeeId != null || $adminId!=null))
                {
                    echo "<li><a href='test.php'>Stwórz wizyte</a></li>";
                }
            ?>
        </ul>
    </nav>
    <section class="container-gray-text">
        <h3 class="center">Zaloguj się</h3>
        <form class="white" action="login.php" method="POST">
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div>
                <label for="password">Hasło:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="center">
                <button type="submit" name="submit" value="Submit" class="button">Zaloguj się</button>
            </div>
        </form>
    </section>
    <footer>
        <div><p>Footer</p></div>
    </footer>
</body>
</html>

<?php

if (isset($_POST['submit'])) {
    $email = filter_var(sanitizeInput($_POST['email'],$conn), FILTER_SANITIZE_EMAIL);
    $password = sanitizeInput($_POST['password'],$conn);
    
    $stmt_clients = $conn->prepare("SELECT client_id, name, lastname, email, password FROM Clients WHERE email = ?");
    $stmt_clients->bind_param("s", $email);
    $stmt_clients->execute();
    $stmt_clients->bind_result($clientId_clients, $name_clients, $lastname_clients, $dbEmail_clients, $dbPassword_clients);
    $stmt_clients->fetch();
    $stmt_clients->close();

    $stmt_employees = $conn->prepare("SELECT emp_id, name, lastname, email, password FROM Employees WHERE email = ?");
    $stmt_employees->bind_param("s", $email);
    $stmt_employees->execute();
    $stmt_employees->bind_result($empId_employees, $name_employees, $lastname_employees, $dbEmail_employees, $dbPassword_employees);
    $stmt_employees->fetch();
    $stmt_employees->close();

    $stmt_admins = $conn->prepare("SELECT admin_id, email, password FROM Admins WHERE email = ?");
    $stmt_admins->bind_param("s", $email);
    $stmt_admins->execute();
    $stmt_admins->bind_result($adminId_admins, $dbEmail_admins, $dbPassword_admins);
    $stmt_admins->fetch();
    $stmt_admins->close();


    if ($dbEmail_clients && password_verify($password, $dbPassword_clients)) {
        $_SESSION['client_id'] = $clientId_clients;
        $_SESSION['emp_id'] = null;
        $_SESSION['admin_id'] = null;
        header("Location: index.php");

        echo "Welcome, $name_clients $lastname_clients (Client)";
    }
    elseif ($dbEmail_employees && password_verify($password, $dbPassword_employees)) {
        $_SESSION['emp_id'] = $empId_employees;
        $_SESSION['client_id'] = null;
        $_SESSION['admin_id'] = null;
        header("Location: index.php");

        echo "Welcome, $name_employees $lastname_employees (Employee)";
    } 
    elseif ($dbEmail_admins && password_verify($password, $dbPassword_admins)) {
        $_SESSION['admin_id'] = $adminId_admins;
        $_SESSION['emp_id'] = null;
        $_SESSION['client_id'] = null;
        header("Location: index.php");

        echo "Welcome, Admin number $adminId_admins";
    }
    else {
        echo "<script>alert('Niepoprawny mail lub hasło');</script>";
    }

    $conn->close();
}

?>