<?php
session_start();
require_once('db.php');

$clientId = $_SESSION['client_id'] ?? null;
$employeeId = $_SESSION['emp_id'] ?? null;
$adminId = $_SESSION['admin_id'] ?? null;
$userLoggedIn = ($clientId != null || $employeeId != null || $adminId != null);


$userId = null;
$userType = null;

$query = "SELECT 1";
if (isset($_SESSION['client_id'])) {
    $userId = $_SESSION['client_id'];
    $userType = 'client';
    $query = "SELECT * FROM clients WHERE client_id = $userId";
} elseif (isset($_SESSION['emp_id'])) {
    $userId = $_SESSION['emp_id'];
    $userType = 'employee';
    $query = "SELECT * FROM employees WHERE emp_id = $userId";
} elseif (isset($_SESSION['admin_id'])) {
    $userId = $_SESSION['admin_id'];
    $userType = 'admin';
    $query = "SELECT * FROM admins WHERE admin_id = $userId";
}

$result = $conn->query($query);

if ($result->num_rows > 0) {
    $userData = $result->fetch_assoc();
}
function getEmployees($conn)
{
    $employees = array();
    $result = mysqli_query($conn, "SELECT * FROM Employees");
    
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[$row['emp_id']] = $row['name'] . ' ' . $row['lastname'];
    }

    return $employees;
}

function sanitizeInput($input,$conn) {
    return mysqli_real_escape_string($conn,htmlspecialchars(trim($input)));
}


function getFacilities($conn)
{
    $facilities = array();
    $result = mysqli_query($conn, "SELECT * FROM Facilities");
    
    while ($row = mysqli_fetch_assoc($result)) {
        $facilities[$row['fac_id']] = $row['address'];
    }

    return $facilities;
}

function getEmployeeInfo($conn, $userId)
{
    $result = mysqli_query($conn, "SELECT name, lastname, specialization FROM Employees WHERE emp_id = '$userId'");
    $employeeInfo = mysqli_fetch_assoc($result);

    return $employeeInfo;
}

$isAdmin = isset($_SESSION['admin_id']) && $_SESSION['admin_id'] !== null;
$userId = isset($_SESSION['emp_id']) ? $_SESSION['emp_id'] : null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && $isAdmin) {
    $employeeId = sanitizeInput($_POST["employee"],$conn);
    $appointmentDate = sanitizeInput($_POST["appointment_date"],$conn);
    $price = sanitizeInput($_POST["price"],$conn);
    $visitType = sanitizeInput($_POST["visit_type"],$conn);
    $selectedFacility = sanitizeInput($_POST["facility"],$conn);

    $sql = "INSERT INTO Appointments (date, emp_id, client_id, fac_id, app_type, price) 
            VALUES ('$appointmentDate', '$employeeId', NULL, '$selectedFacility', '$visitType', '$price')";
    
    if (mysqli_query($conn, $sql)) {
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && !$isAdmin) {
    $appointmentDate = sanitizeInput($_POST["appointment_date"],$conn);
    $price = sanitizeInput($_POST["price"],$conn);
    $visitType = sanitizeInput($_POST["visit_type"],$conn);
    $selectedFacility = sanitizeInput($_POST["facility"],$conn);

    $sql = "INSERT INTO Appointments (date, emp_id, client_id, fac_id, app_type, price) 
            VALUES ('$appointmentDate', '$employeeId', NULL, '$selectedFacility', '$visitType', '$price')";
    
    if (mysqli_query($conn, $sql)) {
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="style.css?<?php echo time()?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visits</title>
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
                    echo "<li><a class='active' href='test.php'>Stwórz wizyte</a></li>";
                }
            ?>
        </ul>
    </nav>
    
    <?php
    if ($isAdmin) {
        $adminInfo = getEmployeeInfo($conn, $userId);
        echo"<section class='container-gray-text'>";
        echo "<form method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>";
        echo "<label for='employee'>Wybierz pracownika:</label>";
        echo "<select name='employee' required>";
        $employees = getEmployees($conn);
        foreach ($employees as $id => $name) {
            echo "<option value='$id'>$name</option>";
        }
        echo "</select>";
        echo "<br>";

        echo "<label for='facility'>Adres:</label>";
        echo "<select name='facility' required>";
        $facilities = getFacilities($conn);
        foreach ($facilities as $id => $address) {
            echo "<option value='$id'>$address</option>";
        }
        echo "</select>";
        echo "<br>";

        echo "<label for='appointment_date'>Data:</label>";
        echo "<input type='datetime-local' name='appointment_date' required>";
        echo "<br>";

        echo "<label for='price'>Cena:</label>";
        echo "<input type='number'min=0 step=0.01 name='price' required>";
        echo "<br>";

        echo "<label for='visit_type'>Rodzaj wizyty:</label>";
        echo "<input type='text' name='visit_type' required>";
        echo "<br>";

        echo "<button type='submit' >Dodaj wizyte</button>";
        echo "</form>";
        echo"</section>";
    } elseif ($userId !== null) {
        $employeeInfo = getEmployeeInfo($conn, $userId);
        echo"<section class='container-gray-text'>";
        echo "<label>Witaj,{$employeeInfo['name']} {$employeeInfo['lastname']}</label>";
        echo "<form method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>";

        echo "<label for='facility'>Adres:</label>";
        echo "<select name='facility' required>";
        $facilities = getFacilities($conn);
        foreach ($facilities as $id => $address) {
            echo "<option value='$id'>$address</option>";
        }
        echo "</select>";
        echo "<br>";

        echo "<label for='appointment_date'>Date:</label>";
        echo "<input type='datetime-local' name='appointment_date' required>";
        echo "<br>";

        echo "<label for='price'>Cena:</label>";
        echo "<input type='number' min=0 step=0.01 name='price' required>";
        echo "<br>";

        echo "<label for='visit_type'>Rodzaj wizyty:</label>";
        echo "<input type='text' name='visit_type' required>";
        echo "<br>";

       echo "<button type='submit' >Dodaj wizyte</button>";
        echo "</form>";
        echo"</section>";
    } else {
        echo "<p>You do not have permission to access this page.</p>";
    }
    ?>
    <footer>
        <div><p>Footer</p></div>
    </footer>
</body>
</html>

<?php
mysqli_close($conn);
?>
