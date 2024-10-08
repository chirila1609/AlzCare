<?php
session_start();

if (!isset($_SESSION['owner_uid'])) {
    header("Location: login.php");
    exit;
}

$owner_uid = $_SESSION['owner_uid'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "conturi";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sql = "SELECT owner_name, owner_business FROM owners WHERE owner_uid = '$owner_uid'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $owner_name = $row['owner_name'];
    $owner_business = $row['owner_business'];
} else {
    echo "Owner not found";
    exit;
}


$sql = "SELECT patient_id, patient_name, patient_age, patient_gender, patient_rt, patient_mem, patient_math, patient_med FROM patients WHERE patient_uid = '$owner_uid'";
$patients_result = $conn->query($sql);


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_patient'])) {
    $patient_name = $_POST['patient_name'];
    $patient_password = password_hash($_POST['patient_password'], PASSWORD_DEFAULT);
    $patient_age = $_POST['patient_age'];
    $patient_gender = $_POST['patient_gender'];

    $sql = "INSERT INTO patients (patient_name, patient_password, patient_age, patient_gender, patient_uid) 
            VALUES ('$patient_name', '$patient_password', '$patient_age', '$patient_gender', '$owner_uid')";

    if ($conn->query($sql) === TRUE) {
        header("Location: profile.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_patient'])) {
    $patient_id = $_POST['patient_id'];

    $sql = "DELETE FROM patients WHERE patient_id = '$patient_id' AND patient_uid = '$owner_uid'";

    if ($conn->query($sql) === TRUE) {
        header("Location: profile.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proprietar</title>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #DFD9E2;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(#3d1053, #9575cd);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background-color: white;
            padding: 20px;
            border-radius: 0 0 10px 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .button {
            background-color: #9575cd;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 300px;
            border-radius: 10px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
        input, select {
            width: 100%;
            padding: 8px;
            margin: 5px 0 15px;
            display: inline-block;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bună, <?php echo $owner_name; ?> de la <?php echo $owner_business; ?></h1>
            <p>Cod logare pacienți: <?php echo $owner_uid; ?></p>
        </div>
        <div class="content">
            <h2>Pacienți:</h2>
            <table>
                <tr>
                    <th>Nr.</th>
                    <th>Nume</th>
                    <th>Vârstă</th>
                    <th>Gen</th>
                    <th>Timp de Răspuns</th>
                    <th>Memorie</th>
                    <th>Matematică</th>
                    <th>Scor mediu</th>
                    <th>Acțiuni</th>
                </tr>
                <?php
                if ($patients_result->num_rows > 0) {
                    $index = 1;
                    while($patient = $patients_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $index++ . ".</td>";
                        echo "<td>" . $patient['patient_name'] . "</td>";
                        echo "<td>" . $patient['patient_age'] . "</td>";
                        echo "<td>" . $patient['patient_gender'] . "</td>";
                        echo "<td>" . ($patient['patient_rt'] !== null ? $patient['patient_rt'] : '-') . "</td>";
                        echo "<td>" . ($patient['patient_mem'] !== null ? $patient['patient_mem'] : '-') . "</td>";
                        echo "<td>" . ($patient['patient_math'] !== null ? $patient['patient_math'] : '-') . "</td>";
                        echo "<td>" . ($patient['patient_med'] !== null ? $patient['patient_med'] : '-') . "</td>";
                        echo "<td><form action='profile.php' method='post' style='display:inline;'>
                                <input type='hidden' name='patient_id' value='" . $patient['patient_id'] . "'>
                                <input type='submit' name='delete_patient' value='Șterge' class='button'>
                              </form></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='9'>Niciun pacient.</td></tr>";
                }
                ?>
            </table>
            <button id="addPatientBtn" class="button" style="margin-top: 20px;">Adaugă pacient</button>
        </div>
    </div>

    <div id="addPatientModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Adaugă pacient</h2>
            <form action="profile.php" method="post">
                <label for="nume">Nume pacient:</label>
                <input type="text" id="nume" name="patient_name" required>

                <label for="parola">Parolă pacient:</label>
                <input type="password" id="parola" name="patient_password" required>

                <label for="varsta">Vârstă pacient:</label>
                <input type="number" id="varsta" name="patient_age" required>

                <label for="gen">Gen pacient:</label>
                <select id="gen" name="patient_gender" required>
                    <option value="">Selectează</option>
                    <option value="Bărbat">Bărbat</option>
                    <option value="Femeie">Femeie</option>
                </select>

                <button type="submit" name="add_patient" class="button">Adaugă</button>
            </form>
        </div>
    </div>

    <script>
        var modal = document.getElementById("addPatientModal");
        var btn = document.getElementById("addPatientBtn");
        var span = document.getElementsByClassName("close")[0];

        btn.onclick = function() {
            modal.style.display = "block";
        }

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>
