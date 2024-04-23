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

function freeapp($userData, $conn, $emp)
{
    $sql = "SELECT * FROM appointments WHERE emp_id = ? AND client_id IS NULL";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param('i', $emp['emp_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $freeAppointments = $result->fetch_all(MYSQLI_ASSOC);
        if (!empty($freeAppointments)) return true;
        else return false;
    }
}

function isfavorite($userId, $conn, $empid)
{
    if ($empid != '0') {
        $sql3 = "SELECT * FROM favorites WHERE client_id = ? AND emp_id = ?";
        $stmt3 = $conn->prepare($sql3);
        if ($stmt3) {
            $stmt3->bind_param('ii', $userId, $empid);
            $stmt3->execute();
            $result3 = $stmt3->get_result();
            if ($result3->num_rows > 0) {
                $stmt3->close();
                return true;
            } else {
                $stmt3->close();
                return false;
            }
        } else {
            echo "Error in prepared statement: " . $conn->error;
            return false;
        }
    }
    header("Location: favorites.php");
}

function addToFavorites($userId, $conn, $empid)
{
    if ($empid != '0') {
        $sql3 = "INSERT INTO favorites (client_id, emp_id) VALUES (?, ?)";
        $stmt3 = $conn->prepare($sql3);
        if ($stmt3) {
            $stmt3->bind_param('ii', $userId, $empid);
            $stmt3->execute();
            $stmt3->close();
        }
    }
    header("Location: favorites.php");
}

function removeFromFavorites($userId, $conn, $empid)
{
    if ($empid != '0') {
        $sql3 = "DELETE FROM favorites WHERE client_id = ? AND emp_id = ?";
        $stmt3 = $conn->prepare($sql3);
        if ($stmt3) {
            $stmt3->bind_param('ii', $userId, $empid);
            $stmt3->execute();
            $stmt3->close();
        }
    }
    header("Location: favorites.php");
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

$allEmployees = getAllEmployees($conn);

$selectedEmployeeId = isset($_SESSION['selected_employee_id']) ? $_SESSION['selected_employee_id'] : '';

if (isset($_POST["add_button"]) && !isfavorite($userId, $conn, $selectedEmployeeId)) {
    addToFavorites($userId, $conn, $selectedEmployeeId);
}

if (isset($_POST["delete_button"]) && isfavorite($userId, $conn, $selectedEmployeeId)) {
    removeFromFavorites($userId, $conn, $selectedEmployeeId);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="style.css?<?php echo time() ?>">
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
                    echo "<li><a href='change-profile.php'>Twój profil</a></li>";
                }
                if($clientId!=null) echo "<li><a class='active' href='favorites.php'>Twoi lekarze</a></li>";
                if($userLoggedIn && ($employeeId != null || $adminId!=null))
                {
                    echo "<li><a href='test.php'>Stwórz wizyte</a></li>";
                }
            ?>
        </ul>
    </nav>

    <div class="top">
        <form method="post">
            <div class="emp">
                <select name="employee" id="employee">
                    <option value='0'>Wybierz pracownika</option>
                    <?php
                    foreach ($allEmployees as $employee) {
                        $selected = ($employee['emp_id'] == $selectedEmployeeId) ? 'selected' : '';
                        echo "<option value='{$employee['emp_id']}' $selected>{$employee['name']} {$employee['lastname']}</option>";
                    }
                    ?>
                </select>
            </div>
            <button type='submit' name='choose_button'>Wybierz</button>
        </form>
        <?php
        if (isset($_POST['choose_button']) && ($_POST['employee'] != '0')) {
            $selectedEmployeeId = $_POST['employee'];
            $_SESSION['selected_employee_id'] = $selectedEmployeeId;
            if (!isfavorite($userId, $conn, $selectedEmployeeId)) {
                echo "<form method='post'>";
                echo "<input type='hidden' name='employee_id' value='$selectedEmployeeId'>"; // Add hidden input to pass employee ID
                echo "<button type='submit' name='add_button'>Dodaj do ulubionych</button>";
                echo "</form>";
            } else {
                echo "<form method='post'>";
                echo "<input type='hidden' name='employee_id' value='$selectedEmployeeId'>"; // Add hidden input to pass employee ID
                echo "<button type='submit' name='delete_button'>Usuń z ulubionych</button>";
                echo "</form>";
            }

        }
        ?>
    </div>

    <?php
    $sql = "SELECT * FROM favorites WHERE client_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param('i', $clientId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            echo "<table id='favorites'>";

            echo "<tr>";
            echo "<th>Lekarz</th>";;
            echo "<th>Dostępność wizyt</th> ";
            echo"</tr>";
            while ($row = $result->fetch_assoc()) {
                $sql2 = "SELECT * FROM Employees WHERE emp_id = ?";
                $stmt2 = $conn->prepare($sql2);
                $stmt2->bind_param("i", $row['emp_id']);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                $emp = $result2->fetch_assoc();
                echo "<tr>";
                echo "<td>{$emp['name']} {$emp['lastname']} {$emp['specialization']}</td>";

                if (freeapp($userData, $conn, $emp)) echo "<td style='color:green;'>Dostępne wizyty z wybranym lekarzem</td>";
                else echo "<td style='color:red;'>Brak dostępnych wizyt z wybranym lekarzem</td>";
            }
            echo "</table>";
        } else echo "<h1>Brak ulubionych lekarzy</h1>";
    }
    ?>
    <footer>
        <div>
            <p>Footer</p>
        </div>
    </footer>
</body>
</html>

<?php $conn->close(); ?>
