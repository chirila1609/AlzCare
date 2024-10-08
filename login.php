<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "conturi";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_type = $_POST['login_type'];

    
    if ($login_type === 'owner') {
        $owner_email = $_POST['owner_email'];
        $owner_password = $_POST['owner_password'];

        $sql = "SELECT owner_uid, owner_password FROM owners WHERE owner_email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $owner_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $hashed_password = $row['owner_password'];
            $owner_uid = $row['owner_uid'];

            if (password_verify($owner_password, $hashed_password)) {
                $_SESSION['owner_uid'] = $owner_uid;
                header("Location: profile.php");
                exit;
            } else {
                $error = "E-mail sau parolă greșită";
            }
        } else {
            $error = "Proprietarul nu a fost găsit";
        }

    
    } elseif ($login_type === 'patient') {
        $patient_name = $_POST['patient_name'];
        $patient_password = $_POST['patient_password'];
        $patient_uid = $_POST['patient_uid'];

        if (empty($patient_name) || empty($patient_password) || empty($patient_uid)) {
            $error = "Vă rugăm să completați toate câmpurile";
        } else {
            $sql = "SELECT patient_id, patient_password FROM patients WHERE patient_name = ? AND patient_uid = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $patient_name, $patient_uid);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $hashed_password = $row['patient_password'];
                $patient_id = $row['patient_id'];

                if (password_verify($patient_password, $hashed_password)) {
                    $_SESSION['patient_name'] = $patient_name;
                    $_SESSION['patient_id'] = $patient_id;
                    header("Location: user.php");
                    exit;
                } else {
                    $error = "Nume, parolă sau cod de conectare greșite";
                }
            } else {
                $error = "Pacientul nu a fost găsit sau cod de conectare incorect";
            }
        }

    
    } elseif ($login_type === 'user') {
        $user_email = $_POST['user_email'];
        $user_password = $_POST['user_password'];

        $sql = "SELECT user_uid, user_password FROM users WHERE user_email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $user_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $hashed_password = $row['user_password'];
            $user_uid = $row['user_uid'];

            if (password_verify($user_password, $hashed_password)) {
                $_SESSION['user_uid'] = $user_uid;
                header("Location: user.php");  
                exit;
            } else {
                $error = "E-mail sau parolă greșită";
            }
        } else {
            $error = "Utilizatorul nu a fost găsit";
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loghează-te</title>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #DFD9E2;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
            background-color: #9575cd;
            position: relative;
            overflow: hidden;
        }
        .background-shape {
            position: absolute;
            background-color: #DFD9E2;
            width: 200%;
            height: 200%;
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -70%);
        }
        .login-form {
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1;
            width: 300px;
        }
        .tabs {
            display: flex;
            margin-bottom: 1rem;
        }
        .tab {
            flex: 1;
            text-align: center;
            padding: 0.5rem;
            background-color: #e0e0e0;
            cursor: pointer;
        }
        .tab:first-child {
            border-top-left-radius: 5px;
            border-bottom-left-radius: 5px;
        }
        .tab:last-child {
            border-top-right-radius: 5px;
            border-bottom-right-radius: 5px;
        }
        .tab.active {
            background-color: #9575cd;
            color: white;
        }
        input {
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input:focus {
            outline: none;
            border: 1px solid #9575cd;
        }
        button {
            width: 100%;
            padding: 0.5rem;
            background-color: #9575cd;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .signup-link {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.9rem;
        }
        .signup-link a {
            color: #9575cd;
            text-decoration: none;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
    <script>
        function toggleLoginFields(loginType) {
            const ownerFields = document.getElementById('owner_fields');
            const patientFields = document.getElementById('patient_fields');
            const userFields = document.getElementById('user_fields');  

            const ownerEmail = document.querySelector('input[name="owner_email"]');
            const ownerPassword = document.querySelector('input[name="owner_password"]');
            const patientName = document.querySelector('input[name="patient_name"]');
            const patientPassword = document.querySelector('input[name="patient_password"]');
            const patientUid = document.querySelector('input[name="patient_uid"]');
            const userEmail = document.querySelector('input[name="user_email"]');
            const userPassword = document.querySelector('input[name="user_password"]');

            document.getElementById('login_type').value = loginType;

            
            ownerFields.style.display = 'none';
            patientFields.style.display = 'none';
            userFields.style.display = 'none';

            
            ownerEmail.required = false;
            ownerPassword.required = false;
            patientName.required = false;
            patientPassword.required = false;
            patientUid.required = false;
            userEmail.required = false;
            userPassword.required = false;

            
            if (loginType === 'owner') {
                ownerFields.style.display = 'block';
                ownerEmail.required = true;
                ownerPassword.required = true;
            } else if (loginType === 'patient') {
                patientFields.style.display = 'block';
                patientName.required = true;
                patientPassword.required = true;
                patientUid.required = true;
            } else if (loginType === 'user') {
                userFields.style.display = 'block';
                userEmail.required = true;
                userPassword.required = true;
            }

            
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.querySelector(`.tab[data-type="${loginType}"]`).classList.add('active');
        }

        function noSpaces(input) {
            input.value = input.value.replace(/\s/g, '');
        }
    </script>
</head>
<body onload="toggleLoginFields('owner')">
    <div class="container">
        <div class="background-shape"></div>
        <div class="login-form">
            <div class="tabs">
                <div class="tab active" data-type="owner" onclick="toggleLoginFields('owner')">Proprietar</div>
                <div class="tab" data-type="patient" onclick="toggleLoginFields('patient')">Pacient</div>
                <div class="tab" data-type="user" onclick="toggleLoginFields('user')">Utilizator</div>
            </div>
            <form action="login.php" method="post" onsubmit="return checkSpaces()">
                <div class="error"><?php echo $error; ?></div>

                <input type="hidden" name="login_type" id="login_type" value="owner">

                <div id="owner_fields">
                    <input type="email" name="owner_email" placeholder="Email" required oninput="noSpaces(this)">
                    <input type="password" name="owner_password" placeholder="Parolă" required oninput="noSpaces(this)">
                </div>

                <div id="patient_fields" style="display: none;">
                    <input type="text" name="patient_name" placeholder="Nume" oninput="noSpaces(this)">
                    <input type="password" name="patient_password" placeholder="Parolă" oninput="noSpaces(this)">
                    <input type="text" name="patient_uid" placeholder="Cod Logare" oninput="noSpaces(this)">
                </div>

                <div id="user_fields" style="display: none;">
                    <input type="email" name="user_email" placeholder="Email" oninput="noSpaces(this)">
                    <input type="password" name="user_password" placeholder="Parolă" oninput="noSpaces(this)">
                </div>

                <button type="submit">Loghează-te</button>
            </form>
            <div class="signup-link">
                <a href="register.php">Nu ai cont? Înregistrează-te</a>
            </div>
        </div>
    </div>
</body>
</html>
