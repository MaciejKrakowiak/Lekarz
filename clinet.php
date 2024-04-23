<?php
session_start();
error_reporting(E_ALL);
require_once("db.php");
function sanitizeInput($input,$conn) {
    return mysqli_real_escape_string($conn,htmlspecialchars(trim($input)));
}

$clientId = $_SESSION['client_id'] ?? null; 
$employeeId = $_SESSION['emp_id'] ?? null; 
$adminId = $_SESSION['admin_id'] ?? null; 


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
function getAllEmployees($conn)
{
    $employees = array();
    $result = mysqli_query($conn, "SELECT emp_id, name, lastname, specialization FROM Employees");

    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row;
    }

    return $employees;
}

function getAppointmentsByEmployee($conn, $employeeId)
{
    $appointments = array();
    $query = "SELECT A.app_id, A.date, A.emp_id, A.client_id, F.address AS fac_address, A.app_type, A.price
              FROM Appointments A
              JOIN Facilities F ON A.fac_id = F.fac_id
              WHERE A.emp_id = '$employeeId'
              ORDER BY A.date";

    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        $appointments[] = $row;
    }

    return $appointments;
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

function reserveAppointment($conn, $appointmentId, $clientId)
{
    $query = "UPDATE Appointments SET client_id = '$clientId' WHERE app_id = '$appointmentId' AND client_id IS NULL";
    mysqli_query($conn, $query);
}

function cancelAppointment($conn, $appointmentId)
{
    $query = "UPDATE Appointments SET client_id = null WHERE app_id = '$appointmentId'";
    mysqli_query($conn, $query);
}

function deleteEmployeeAppointment($conn, $appointmentId, $employeeId)
{
    $query = "DELETE FROM Appointments WHERE app_id = '$appointmentId' AND emp_id = '$employeeId'";
    mysqli_query($conn, $query);
}

function sendtoapphistory($appointment,$conn)
{   
    if ($appointment['client_id'] != null) {
        $facilityAddress = $appointment['fac_address'];
        $facilityIdQuery = "SELECT fac_id FROM Facilities WHERE address = '$facilityAddress'";
        $facilityIdResult = mysqli_query($conn, $facilityIdQuery);

        if ($facilityIdResult && $row = mysqli_fetch_assoc($facilityIdResult)) {
            $facilityId = $row['fac_id'];

            $query = "INSERT INTO Apphistory (date, fac_id, app_type, app_price, client_id, emp_id) 
                      VALUES ('{$appointment['date']}', $facilityId, '{$appointment['app_type']}', 
                              {$appointment['price']}, {$appointment['client_id']}, {$appointment['emp_id']})";
            mysqli_query($conn, $query);
        } else {
            echo "Error: Unable to retrieve facility ID for address $facilityAddress";
        }
    }
    $query = "DELETE FROM Appointments WHERE app_id = {$appointment['app_id']}";
    mysqli_query($conn, $query);
}

$allEmployees = getAllEmployees($conn);

$userLoggedIn = ($clientId != null || $employeeId != null || $adminId != null);

if (isset($_POST['employee_id'])) {
    $selectedEmployeeId = $_POST['employee_id'];

    $_SESSION['selected_employee_id'] = $selectedEmployeeId;

    $selectedEmployeeAppointments = getAppointmentsByEmployee($conn, $selectedEmployeeId);

    $selectedEmployee = $allEmployees[array_search($selectedEmployeeId, array_column($allEmployees, 'emp_id'))];
} elseif (isset($_SESSION['selected_employee_id'])) {
    $lastSelectedEmployeeId = $_SESSION['selected_employee_id'];

    $selectedEmployeeAppointments = getAppointmentsByEmployee($conn, $lastSelectedEmployeeId);

    $selectedEmployee = $allEmployees[array_search($lastSelectedEmployeeId, array_column($allEmployees, 'emp_id'))];
} else {
    $selectedEmployeeAppointments = array();
    $selectedEmployee = null;
}

$allFacilityAddresses = getAllFacilityAddresses($conn);

    if (isset($_POST['reserve_button'])) {
        $appointmentId = $_POST['appointment_id'];
        reserveAppointment($conn, $appointmentId, $clientId);
    
        header("Location: clinet.php");
        exit();
    }
    
    if (isset($_POST['cancel_button'])) {
        $appointmentId = $_POST['appointment_id'];
        cancelAppointment($conn, $appointmentId);
    
        header("Location: clinet.php");
        exit();
    }
    
    if (isset($_POST['delete_button'])) {
        $appointmentId = $_POST['appointment_id'];
    
        if ($employeeId != null) {
            $query = "SELECT * FROM Appointments WHERE app_id = '$appointmentId' AND emp_id = '$employeeId'";
            $result = mysqli_query($conn, $query);
    
            if (mysqli_num_rows($result) > 0) {
                deleteEmployeeAppointment($conn, $appointmentId, $employeeId);
            }
        }
        header("Location: clinet.php");
        exit();
    }
    
    if ($userLoggedIn && isset($_POST['edit_button'])) {
        $appointmentId = $_POST['appointment_id'];
            $newDate = sanitizeInput($_POST['new_date'],$conn);
            $newType = sanitizeInput($_POST['new_type'],$conn);
            $newPrice = sanitizeInput($_POST['new_price'],$conn);
            $newAddress = sanitizeInput($_POST['new_address'],$conn);

            $query = "UPDATE Appointments SET date = ?, app_type = ?, price = ?, fac_id = (SELECT fac_id FROM Facilities WHERE address = ?) WHERE app_id = ? AND emp_id = ?";
            $stmt2 = $conn->prepare($query);
            if ($stmt2) {
                $stmt2->bind_param('ssdsii', $newDate, $newType, $newPrice, $newAddress, $appointmentId, $employeeId);
                if ($stmt2->execute()) {
                    $stmt2->close();
                    header("Location: clinet.php");
                    exit();
                } else {
                    echo "Error in execution: " . $stmt2->error;
                }
            } else {
                echo "Error in prepared statement: " . $conn->error;
            }
            header("Location: clinet.php");
            exit();
    }
    
    if (isset($_POST['admin_delete_button'])) {
        $appointmentId = $_POST['appointment_id'];
    
        if ($adminId != null) {
            $query = "SELECT * FROM Appointments WHERE app_id = '$appointmentId' AND emp_id = '$lastSelectedEmployeeId'";
            $result = mysqli_query($conn, $query);
    
            if (mysqli_num_rows($result) > 0) {
                deleteEmployeeAppointment($conn, $appointmentId, $lastSelectedEmployeeId);
            }
        }
        header("Location: clinet.php");
        print_r(headers_list());
        exit();
    }
    
    if ($userLoggedIn && isset($_POST['admin_edit_button'])) {
        $appointmentId = $_POST['appointment_id'];

        if ($adminId != null) {
            $newDate = sanitizeInput($_POST['new_date'],$conn);
            $newType = sanitizeInput($_POST['new_type'],$conn);
            $newPrice = sanitizeInput($_POST['new_price'],$conn);
            $newAddress = sanitizeInput($_POST['new_address'],$conn);
            $query = "UPDATE Appointments SET date = ?, app_type = ?, price = ?, fac_id = (SELECT fac_id FROM Facilities WHERE address = ?) WHERE app_id = ? AND emp_id = ?";
            $stmt2 = $conn->prepare($query);
            if ($stmt2) {
                $stmt2->bind_param('ssdsii', $newDate, $newType, $newPrice, $newAddress, $appointmentId, $lastSelectedEmployeeId);
                if ($stmt2->execute()) {
                    $stmt2->close();
                    header("Location: clinet.php");
                    exit();
                } else {
                    echo "Error in execution: " . $stmt2->error;
                }
            } else {
                echo "Error in prepared statement: " . $conn->error;
            }
            
            header("Location: clinet.php");
            exit();
        }
    }

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <link rel="stylesheet" href="style.css?<?php echo time()?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wszystkie Umówione Spotkania</title>
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
            <li><a class="active" href="clinet.php">Wizyty</a></li>
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
                    echo "<li><a href='test.php'>Stwórz wizyte</a></li>";
                }
            ?>
        </ul>
    </nav>

    <div class="top">
    <form method="post" action="">
        <div class="emp"><select name="employee_id" id="employee" onchange="this.form.submit()">
            <option value="" <?php echo (empty($lastSelectedEmployeeId)) ? 'selected' : ''; ?>>Wybierz pracownika</option>
            <?php
            foreach ($allEmployees as $employee) {
                $selected = ($employee['emp_id'] == $lastSelectedEmployeeId) ? 'selected' : '';
                echo "<option value='{$employee['emp_id']}' $selected>{$employee['name']} {$employee['lastname']}</option>";
            }
            ?>
        </select></div>
    </form>

    <?php
    if ($selectedEmployee) {
        echo "<h3>Wizyty z: {$selectedEmployee['name']} {$selectedEmployee['lastname']} {$selectedEmployee['specialization']}</h3>";
    }
    else echo "<h3>Wybierz pracownika</h3>";
    ?>
    </div>

    <?php
if (isset($selectedEmployeeAppointments)) {
    if (empty($selectedEmployeeAppointments)) {
        echo "<p>Brak umówionych spotkań dla wybranego pracownika.</p>";
    } else {
        echo"<table id='customers'>";

        echo "<tr>";
            echo "<th>Data</th>";
            echo "<th>Rodzaj wizyty</th>";
            echo "<th>Cena</th> ";
            echo "<th>Adres</th>";
            echo "<th>Stan</th>";
            echo "<th>Rezerwacja</th>";
        echo"</tr>";

        foreach ($selectedEmployeeAppointments as $appointment) {
            if (new DateTime($appointment['date']) < new DateTime()) {
                sendtoapphistory($appointment, $conn);
            }
            if($appointment['client_id'] == $clientId||$appointment['client_id'] === null||$employeeId!=null||$adminId!=null){
            echo "<tr>";
            echo "<td>{$appointment['date']}</td>";
            echo "<td>{$appointment['app_type']}</td>";
            echo "<td>{$appointment['price']} zł</td> ";
            echo "<td>{$appointment['fac_address']}</td>";
            echo "<td style='color:" . (($appointment['client_id'] === null) ? 'green' : 'red') . ";'>";
            echo ($appointment['client_id'] === null) ? 'Dostępne' : 'Zarezerwowane';
            echo "</td>";
            
             if ($userLoggedIn && $clientId != null) {
                if ($appointment['client_id'] === null) {
                    echo "<form method='post'>";
                    echo "<input type='hidden' name='appointment_id' value={$appointment['app_id']}>";
                    echo "<td><button type='submit' name='reserve_button'>Zarezerwuj</button></td>";
                    echo "</form>";
                    
                } 
                else if ($appointment['client_id'] == $clientId){
                        echo "<form method='post'>";
                        echo "<input type='hidden' name='appointment_id' value={$appointment['app_id']}>";
                        echo "<td><button type='submit' name='cancel_button'>Anuluj Rezerwację</button></td>";
                        echo "</form>";
                }
                else{
                    echo"<td></td>";
                }
            }
            else{
                echo"<td></td>";
            }
            echo "</tr>";
        }

            echo "<tr>";
               if ($userLoggedIn && $employeeId != null && $appointment['emp_id'] == $employeeId) {
                echo "<form method='post'>";
                echo "<input type='hidden' name='appointment_id' value={$appointment['app_id']}>";
                echo "<td><input type='datetime-local' name='new_date' value='" . date("Y-m-d\TH:i", strtotime($appointment['date'])) . "' required></td>";
                echo "<td><input type='text' name='new_type' value='" . $appointment['app_type'] . "' required></td>";
                echo "<td><input type='number' min=0 step=0.01 name='new_price' value='" . $appointment['price'] . "' required></td>";

                echo "<td><select name='new_address' required>";
                foreach ($allFacilityAddresses as $address) {
                    $selectedAddress = ($address == $appointment['fac_address']) ? 'selected' : '';
                    echo "<option value='$address' $selectedAddress>$address</option>";
                }
                echo "</select></td>";

                echo "<td><button type='submit' name='edit_button'>Edytuj</button></td>";
                echo "</form>";
            }

                if ($userLoggedIn && $employeeId != null && $appointment['emp_id'] == $employeeId) {
                    echo "<form method='post'>";
                    echo "<input type='hidden' name='appointment_id' value='" . $appointment['app_id'] . "'>";
                    echo "<td><button type='submit' name='delete_button'>Usuń</button></td>";
                    echo "</form>";
                }
            
               if ($userLoggedIn && $adminId != null) {
                echo "<form method='post'>";
                echo "<input type='hidden' name='appointment_id' value={$appointment['app_id']}>";
                echo "<td><input type='datetime-local' name='new_date' value='".date('Y-m-d\TH:i',strtotime($appointment['date']))."'required></td>";
                echo "<td><input type='text' name='new_type' value='" . $appointment['app_type'] . "' required></td>";
                echo "<td><input type='number' min=0 step=0.01 name='new_price' value='" . $appointment['price'] . "' required></td>";

                echo "<td><select name='new_address' required>";
                foreach ($allFacilityAddresses as $address) {
                    $selectedAddress = ($address == $appointment['fac_address']) ? 'selected' : '';
                    echo "<option value='$address' $selectedAddress>$address</option>";
                }
                echo "</select></td>";

                echo "<td><button type='submit' name='admin_edit_button'>Edytuj</button></td>";
                echo "</form>";
            }

                if ($userLoggedIn && $adminId != null) {
                    echo "<form method='post'>";
                    echo "<input type='hidden' name='appointment_id' value={$appointment['app_id']}>";
                    echo "<td><button type='submit' name='admin_delete_button'>Usuń</button></td>";
                    echo "</form>";
                }
                echo "</tr>";
            }
        }
        echo"</table>";
    }
    ?>
    <?php
    if (!$userLoggedIn) {
        echo "<p>Zaloguj się, aby móc rezerwować spotkania.</p>";
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