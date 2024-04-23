<?php
session_start();
require_once('db.php');

$clientId = $_SESSION['client_id'] ?? null;
$employeeId = $_SESSION['emp_id'] ?? null;
$adminId = $_SESSION['admin_id'] ?? null;
$userLoggedIn = ($clientId != null || $employeeId != null || $adminId != null);

$userId = null;
$userType = null;

function sanitizeInput($input,$conn) {
    return mysqli_real_escape_string($conn,htmlspecialchars(trim($input)));
}


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

function freeapp($userData,$conn)
{
    $sql = "SELECT * FROM appointments WHERE emp_id = ? AND client_id IS NULL";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param('i', $userData['emp_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $freeAppointments = $result->fetch_all(MYSQLI_ASSOC);
        if (!empty($freeAppointments)) return true;
        else return false; 
    }
}

function editprofile($userData, $userType, $userId, $conn)
{
    if ($userType != 'admin') {
        $newName = !empty($_POST['new_name']) ? sanitizeInput($_POST['new_name'],$conn) : $userData['name'];
        $newLastname = !empty($_POST['new_lastname']) ? sanitizeInput($_POST['new_lastname'],$conn) : $userData['lastname'];
    }

    if ($userType == 'employee') {
        $newSpec = !empty($_POST['new_specialization']) ? sanitizeInput($_POST['new_specialization'],$conn) : $userData['specialization'];
    }

    $newEmail = !empty($_POST['new_email']) ? sanitizeInput($_POST['new_email'],$conn) : $userData['email'];
    $newPass = !empty($_POST['new_password']) ? sanitizeInput($_POST['new_password'],$conn) : $userData['password'];
    if($newPass!=$userData['password']){
        $vpassword = sanitizeInput($_POST['confirm_password'],$conn);
    if (strlen($newPass) < 8) {
        echo "<script>alert('Password must be at least 8 characters');</script>";
    }

    if (!preg_match("/[a-z]/i", $newPass)) {
        echo "<script>alert('Password must contain at least one letter');</script>";
    }

    if (!preg_match("/[0-9]/", $newPass)) {
        echo "<script>alert('Password must contain at least one number');</script>";
    }

    if ($newPass !== $vpassword) {
        echo "<script>alert('Passwords must match');</script>";
    }
    $newPass = password_hash($newPass, PASSWORD_DEFAULT);
    }

    if ($userType == 'client') {
        $sql2 = "UPDATE Clients SET name = ?, lastname = ?, email = ?, password = ? WHERE client_id = ?";
        $stmt2 = $conn->prepare($sql2);
        if ($stmt2) {
            $stmt2->bind_param('ssssi', $newName, $newLastname, $newEmail, $newPass, $userId);
            $stmt2->execute();
            $stmt2->close();
        } 
        else {
            echo "Error in prepared statement: " . $conn->error;
        }
    } 
    elseif ($userType == 'employee') 
    {
        $sql2 = "UPDATE Employees SET name = ?, lastname = ?, email = ?, password = ?, specialization = ? WHERE emp_id = ?";
        $stmt2 = $conn->prepare($sql2);
        if ($stmt2) {
            $stmt2->bind_param('sssssi', $newName, $newLastname, $newEmail, $newPass, $newSpec, $userId);
            $stmt2->execute();
            $stmt2->close();
        } 
        else {
            echo "Error in prepared statement: " . $conn->error;
        }
    } 
    elseif ($userType == 'admin') {
        $sql2 = "UPDATE Admins SET email = ?, password = ? WHERE admin_id = ?";
        $stmt2 = $conn->prepare($sql2);
        if ($stmt2) {
            $stmt2->bind_param('ssi', $newEmail, $newPass, $userId);
            $stmt2->execute();
            $stmt2->close();
        } 
        else {
            echo "Error in prepared statement: " . $conn->error;
        }
    }

    header("Location: change-profile.php");
    exit();
}

function getAllEmployees($conn)
{
    $employees = array();
    $result = mysqli_query($conn, "SELECT emp_id, name, lastname, specialization FROM Employees");
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row;
    }
    return $employees;
}

function getAllClients($conn)
{
    $employees = array();
    $result = mysqli_query($conn, "SELECT client_id, name, lastname FROM Clients");
    while ($row = mysqli_fetch_assoc($result)) {
        $clients[] = $row;
    }
    return $clients;
}

function getAllFacilityAddresses($conn)
{
    $addresses = array();
    $result = mysqli_query($conn, "SELECT address FROM Facilities");

    while ($row = mysqli_fetch_assoc($result)) {
        $addresses[] = $row['address'];
    }

    return $addresses;
}

function getAppointmentsByClient($conn, $clientId)
{
    if($clientId!=null){
    $appointments = array();
    $query = "SELECT A.app_id, A.date, A.emp_id, A.client_id, F.address AS fac_address, A.app_type, A.app_price
              FROM apphistory A
              JOIN Facilities F ON A.fac_id = F.fac_id
              WHERE A.client_id = '$clientId'
              ORDER BY A.date";

    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        $appointments[] = $row;
    }

    return $appointments;
    }
}

function getAppointmentsByEmployee($conn, $employeeId)
{
    if($employeeId!=null){
    $appointments = array();
    $query = "SELECT A.app_id, A.date, A.emp_id, A.client_id, F.address AS fac_address, A.app_type, A.app_price
    FROM apphistory A
    JOIN Facilities F ON A.fac_id = F.fac_id
    WHERE A.emp_id = '$employeeId'
    ORDER BY A.date";

    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        $appointments[] = $row;
    }

    return $appointments;
}
}

$allEmployees = getAllEmployees($conn);
$allClients = getAllClients($conn);
$allFacilityAddresses = getAllFacilityAddresses($conn);
$selectedClientAppointments = getAppointmentsByClient($conn, $clientId);
$selectedEmployeeAppointments = getAppointmentsByEmployee($conn, $employeeId);


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
                    echo "<li><a href='sign-up.php'>Rejestracja</a></li>";
                    echo "<li><a href='login.php'>Logowanie</a></li>";
                }
                else
                {
                    echo "<li><a href='logout.php'>Wyloguj się</a></li>";
                    echo "<li><a class='active' href='change-profile.php'>Twój profil</a></li>";
                }
                if($clientId!=null) echo "<li><a href='favorites.php'>Twoi lekarze</a></li>";
                if($userLoggedIn && ($employeeId != null || $adminId!=null))
                {
                    echo "<li><a href='test.php'>Stwórz wizyte</a></li>";
                }
            ?>
        </ul>
    </nav>

<?php
    echo"<section class='container-gray-text'>";
    echo "<div class='profile'>";
    echo "<form method='POST'>";
        if($userType!='admin')
        {
            echo"<label>Imie: {$userData['name']}</label>";
            echo"<div><input type='text' name='new_name' placeholder='edytuj imie'></div>";
            
            echo"<label>Nazwisko: {$userData['lastname']}</label>";
            echo"<div><input type='text' name='new_lastname' placeholder='edytuj nazwisko'></div>";
        }
        if($userType=='employee') {
            echo"<label>Specializacja: {$userData['specialization']}</label>";
            echo"<div><input type='text' name='new_specialization' placeholder='edytuj specjalizacje'></div>";
        }
        echo"<label>Email: {$userData['email']}</label>";
        echo"<div><input type='email' name='new_email' placeholder='edytuj mail'></div>";
        
        echo"<label>Zmień hasło:</label>";
        echo"<div><input type='password' name='new_password' placeholder='edytuj hasło'></div>";
        echo"<div><input type='password' name='confirm_password' placeholder='potwierdź hasło'></div>";

        echo "<div><button type='submit' name='save_button'>Zapisz</button></div>";
        if ($userLoggedIn && isset($_POST['save_button'])) {
            editprofile($userData, $userType, $userId, $conn);
        }
    echo"</form>";
    echo"</div>";
    echo "</section>";

    echo "<h1>Historia spotkań</h1>";
    if (isset($selectedClientAppointments)) {
    if (empty($selectedClientAppointments)) {
        echo "<p>Brak historii spotkań.</p>";
    } else {
        echo"<table id='customers'>";

        echo "<tr>";
            echo "<th>Pracownik</th>";
            echo "<th>Data</th>";
            echo "<th>Rodzaj wizyty</th>";
            echo "<th>Cena</th> ";
            echo "<th>Adres</th>";
        echo"</tr>";
        foreach ($selectedClientAppointments as $appointment) {
            foreach($allEmployees as $empp)
        {
            if($empp['emp_id'] == $appointment['emp_id']) $apemp[] = $empp;
        }
            echo "<tr>";
            echo "<td>{$apemp[count($apemp) - 1]['name']} {$apemp[count($apemp) - 1]['lastname']} {$apemp[count($apemp) - 1]['specialization']}</td>";
            echo "<td>{$appointment['date']}</td>";
            echo "<td>{$appointment['app_type']}</td>";
            echo "<td>{$appointment['app_price']} zł</td> ";
            echo "<td>{$appointment['fac_address']}</td>";
            echo "</tr>";
        }
        echo"</table>";
    }
}
else if (isset($selectedEmployeeAppointments)) {
    if (empty($selectedEmployeeAppointments)) {
        echo "<h3>Brak historii spotkań.</h3>";
    } else {
        echo"<table id='customers'>";

        echo "<tr>";
            echo "<th>Klient</th>";
            echo "<th>Data</th>";
            echo "<th>Rodzaj wizyty</th>";
            echo "<th>Cena</th> ";
            echo "<th>Adres</th>";
        echo"</tr>";
        foreach ($selectedEmployeeAppointments as $appointment) {
            foreach($allClients as $clie)
        {
            if($clie['client_id'] == $appointment['client_id']) $apemp[] = $clie;
        }
            echo "<tr>";
            echo "<td>{$apemp[count($apemp) - 1]['name']} {$apemp[count($apemp) - 1]['lastname']}</td>";
            echo "<td>{$appointment['date']}</td>";
            echo "<td>{$appointment['app_type']}</td>";
            echo "<td>{$appointment['app_price']} zł</td> ";
            echo "<td>{$appointment['fac_address']}</td>";
            echo "</tr>";
        }
        echo"</table>";
    }
}
?>
    <footer>
        <div><p>Footer</p></div>
    </footer>
</body>
</html>

<?php mysqli_close($conn); ?>