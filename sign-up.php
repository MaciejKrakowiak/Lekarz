
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
    <title>Document</title>
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
                    echo "<li><a class='active' href='sign-up.php'>Rejestracja</a></li>";
                    echo "<li><a href='login.php'>Logowanie</a></li>";
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
    <section class = "container-gray-text">
        <h3>Zarejestruj się</h3>
        <form class="white" method="POST">
            <div>
            <label for="name">Imię:</label>
            <input type="text" id="name" name="name">
            </div>
            <div>
            <label for="lastnamename">Nazwisko:</label>
            <input type="text" id="lastname" name="lastname">
            </div>
            <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email">
            </div>
            <div>
            <label for="passworld">Hasło:</label>
            <input type="password" id="password" name="password">
            </div>
            <div>
            <label for="vpassword">Potwierdź hasło:</label>
            <input type="password" id="vpassword" name="vpassword">
            </div>
            <div class="center">
                <button type="submit" name="submit" value="submit" class= button>Sign up</button>
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
    if (empty($_POST["name"]) || empty($_POST["lastname"]) || empty($_POST["email"]) || empty($_POST["password"]) || empty($_POST["vpassword"])) {
        echo "<script>alert('All fields are required');</script>";
    } else {
        $name = sanitizeInput($_POST["name"], $conn);
        $lastname = sanitizeInput($_POST["lastname"], $conn);
        $email = filter_var(sanitizeInput($_POST["email"], $conn), FILTER_VALIDATE_EMAIL);
        $password = sanitizeInput($_POST["password"], $conn);
        $vpassword = sanitizeInput($_POST["vpassword"], $conn);

        if (!$email) {
            echo "<script>alert('Valid email is required');</script>";
        }

        if (strlen($password) < 8) {
            echo "<script>alert('Password must be at least 8 characters');</script>";
        }

        if (!preg_match("/[a-z]/i", $password)) {
            echo "<script>alert('Password must contain at least one letter');</script>";
        }

        if (!preg_match("/[0-9]/", $password)) {
            echo "<script>alert('Password must contain at least one number');</script>";
        }

        if ($password !== $vpassword) {
            echo "<script>alert('Passwords must match');</script>";
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $stmt = $conn->prepare("INSERT INTO Clients (name, lastname, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $lastname, $email, $password_hash);

        if ($stmt->execute()) {
        } else {
            echo "Error during registration: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
      
    }
}
?>