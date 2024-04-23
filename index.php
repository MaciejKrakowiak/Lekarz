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
            <li><a class="active" href="index.php">Strona główna</a></li>
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
                    echo "<li><a href='test.php'>Stwórz wizyte</a></li>";
                }
            ?>
        </ul>
    </nav>
    <h1>Informacje o lekarzach</h1>
    <div class="mp">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Nunc sed blandit libero volutpat. Risus nullam eget felis eget nunc lobortis. Libero justo laoreet sit amet cursus sit amet dictum sit. Sapien faucibus et molestie ac feugiat sed lectus. Egestas dui id ornare arcu odio ut sem nulla. Blandit libero volutpat sed cras ornare arcu. Facilisi nullam vehicula ipsum a arcu cursus vitae congue. Cras fermentum odio eu feugiat pretium nibh ipsum consequat nisl. In ante metus dictum at tempor. Ipsum a arcu cursus vitae. Scelerisque varius morbi enim nunc faucibus a pellentesque. Quam vulputate dignissim suspendisse in est ante in.</div>

    <div id="frame">
		<input type="radio" name="frame" id="frame1" checked />
		<input type="radio" name="frame" id="frame2" />
		<input type="radio" name="frame" id="frame3" />
		<div id="slides">
			<div id="overflow">
				<div class="inner">
					<div class="frame">
						<img src="p1.jpg">
					</div>
					<div class="frame">
						<img src="p2.jpg">
					</div>
					<div class="frame">
						<img src="p3.jpg">
					</div>
				</div>
			</div>
		</div>
		<div id="controls">
			<label for="frame1"></label>
			<label for="frame2"></label>
			<label for="frame3"></label>
		</div>
		<div id="bullets">
			<label for="frame1"></label>
			<label for="frame2"></label>
			<label for="frame3"></label>
		</div>
	</div>
</div>
    <div class="mp">Mattis aliquam faucibus purus in. Quisque egestas diam in arcu cursus euismod quis viverra nibh. Tempor nec feugiat nisl pretium fusce. In aliquam sem fringilla ut morbi tincidunt augue interdum velit. Eu nisl nunc mi ipsum faucibus vitae aliquet nec ullamcorper. Elit scelerisque mauris pellentesque pulvinar pellentesque habitant morbi tristique senectus. Quam nulla porttitor massa id neque aliquam vestibulum. Amet massa vitae tortor condimentum. At varius vel pharetra vel turpis nunc eget lorem dolor. Gravida in fermentum et sollicitudin ac. Orci ac auctor augue mauris augue neque gravida in. Proin fermentum leo vel orci porta non pulvinar neque. In cursus turpis massa tincidunt dui ut ornare lectus sit. Pellentesque nec nam aliquam sem et tortor. Dictum varius duis at consectetur lorem. Nulla facilisi etiam dignissim diam quis. Aenean pharetra magna ac placerat vestibulum. Erat pellentesque adipiscing commodo elit at imperdiet dui. Ac orci phasellus egestas tellus rutrum tellus.</div>
    <div class="mp">Tristique nulla aliquet enim tortor at. Aliquet risus feugiat in ante metus dictum at. Enim sed faucibus turpis in. Sagittis id consectetur purus ut faucibus pulvinar. Consequat interdum varius sit amet mattis vulputate enim nulla. Tortor posuere ac ut consequat semper viverra. Placerat vestibulum lectus mauris ultrices eros. Dictum varius duis at consectetur lorem donec massa. Dignissim sodales ut eu sem. Sagittis id consectetur purus ut faucibus pulvinar elementum integer. Interdum posuere lorem ipsum dolor sit. Etiam non quam lacus suspendisse faucibus. Commodo nulla facilisi nullam vehicula ipsum a arcu cursus vitae. Magna ac placerat vestibulum lectus mauris ultrices eros in. Elementum curabitur vitae nunc sed velit dignissim. Natoque penatibus et magnis dis. Tortor condimentum lacinia quis vel eros donec ac odio tempor.</div>
    <div class="mp">Nulla porttitor massa id neque aliquam vestibulum morbi blandit. Nam at lectus urna duis convallis convallis. Convallis posuere morbi leo urna molestie at. Tortor dignissim convallis aenean et tortor at. Nunc consequat interdum varius sit amet mattis vulputate enim. Arcu non sodales neque sodales ut. Pulvinar neque laoreet suspendisse interdum consectetur libero id. Quis risus sed vulputate odio. Id volutpat lacus laoreet non curabitur gravida. Massa tincidunt nunc pulvinar sapien. Consectetur libero id faucibus nisl tincidunt eget nullam. Aliquam malesuada bibendum arcu vitae elementum curabitur vitae. Id cursus metus aliquam eleifend mi in nulla posuere. Faucibus et molestie ac feugiat sed lectus vestibulum mattis ullamcorper. Risus viverra adipiscing at in tellus integer feugiat scelerisque varius. Et malesuada fames ac turpis egestas. Ipsum dolor sit amet consectetur adipiscing elit. Purus viverra accumsan in nisl nisi scelerisque. Tristique senectus et netus et malesuada fames ac.</div>
    <div class="mp">Aliquet lectus proin nibh nisl condimentum id venenatis a condimentum. Etiam tempor orci eu lobortis elementum nibh. Penatibus et magnis dis parturient montes nascetur ridiculus mus. Gravida cum sociis natoque penatibus et magnis dis parturient. Adipiscing commodo elit at imperdiet dui accumsan. Ultrices mi tempus imperdiet nulla malesuada. Lorem donec massa sapien faucibus et molestie. Netus et malesuada fames ac turpis egestas integer eget. Risus nec feugiat in fermentum posuere. Elit pellentesque habitant morbi tristique. Tellus molestie nunc non blandit massa enim nec. Mauris nunc congue nisi vitae suscipit tellus. Odio ut sem nulla pharetra diam sit amet nisl.</div>
    <footer>
        <div><p>Footer</p></div>
    </footer>
</body>
</html>

<?php mysqli_close($conn); ?>