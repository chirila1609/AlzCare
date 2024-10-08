<?php
session_start();


if (isset($_SESSION['patient_name']) && isset($_SESSION['patient_id'])) {
    $user_type = 'patient';
    $user_name = $_SESSION['patient_name'];
    $user_id = $_SESSION['patient_id'];

    
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "conturi";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Conexiunea a eșuat: " . $conn->connect_error);
    }

    
    $sql = "SELECT patient_rt, patient_mem, patient_math, patient_med FROM patients WHERE patient_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_rt = $row['patient_rt'];
        $user_mem = $row['patient_mem'];
        $user_math = $row['patient_math'];

        
        $user_med = ($user_mem + $user_math) / 2;

        
        $update_sql = "UPDATE patients SET patient_med=? WHERE patient_id=?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("di", $user_med, $user_id);
        $update_stmt->execute();
    } else {
        $user_rt = $user_mem = $user_math = $user_med = "N/A";
    }

    $conn->close();

} elseif (isset($_SESSION['user_uid'])) {
    $user_type = 'individual_user';
    $user_id = $_SESSION['user_uid'];

    
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "conturi";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Conexiunea a eșuat: " . $conn->connect_error);
    }

    
    $sql = "SELECT user_name, user_rt, user_mem, user_math, user_med FROM users WHERE user_uid=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_name = $row['user_name'];
        $user_rt = $row['user_rt'];
        $user_mem = $row['user_mem'];
        $user_math = $row['user_math'];

        
        $user_med = ($user_mem + $user_math) / 2;

        
        $update_sql = "UPDATE users SET user_med=? WHERE user_uid=?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ds", $user_med, $user_id);
        $update_stmt->execute();
    } else {
        $user_rt = $user_mem = $user_math = $user_med = "N/A";
    }

    $conn->close();

} else {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil utilizator</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #DFD9E2;
        }
        .wave-container {
            position: relative;
            height: 200px;
            background: #3D1053;
            overflow: hidden;
            width: 100%; 
        }
        .wave {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 200%;
            height: 400px;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg"');
            background-size: 100% 750px; 
            animation: wave 15s linear infinite;
        }
        .wave:nth-child(2) {
            bottom: 10px;
            opacity: 0.5;
            animation: wave 10s linear infinite;
        }
        .wave:nth-child(3) {
            bottom: 15px;
            opacity: 0.2;
            animation: wave 7s linear infinite;
        }
        @keyframes wave {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .content {
            padding: 20px;
            color: #3D1053;
            max-width: 800px;
            margin: 0 auto;
        }
        h1 {
            margin-bottom: 10px;
        }
        .stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .stat-item {
            background-color: #d1c4e9;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            flex: 1;
            margin: 5px;
        }
        .buttons {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .button {
    background-color: #9575cd;
    color: white;
    padding: 15px 20px;
    margin: 5px;
    border-radius: 5px;
    text-decoration: none;
    flex: 1;
    min-width: 120px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.button img {
    max-width: 50px; 
    margin-bottom: 10px; 
}
    </style>
</head>
<body>
    <div class="wave-container">
        <div class="wave"></div>
        <div class="wave"></div>
        <div class="wave"></div>
    </div>
    <div class="content">
        <h1>Bună, <?php echo htmlspecialchars($user_name); ?></h1>
        <div class="stats">
            <div class="stat-item">Timp de Răspuns: <?php echo htmlspecialchars($user_rt); ?> secunde</div>
            <div class="stat-item">Memorie: <?php echo htmlspecialchars($user_mem); ?></div>
            <div class="stat-item">Scor Matematică: <?php echo htmlspecialchars($user_math); ?></div>
            <div class="stat-item">Scor Mediu: <?php echo number_format($user_med, 2); ?></div>
        </div>
        <div class="buttons">
    <a href="math.php" class="button">
        <img src="images/2838862-bdf285b2.png" alt="Matematică">
        Matematică
    </a>
    <a href="memorie.php" class="button">
        <img src="images/3401572-47fab21e.png" alt="Memorie">
        Memorie
    </a>
    <a href="sudoku.html" class="button">
        <img src="images/3400564-ec5c89ad.png" alt="Sudoku">
        Sudoku
    </a>
    <a href="maze.html" class="button">
        <img src="images/4292135-1d6e6c39.png" alt="Labirint">
        Labirint
    </a>
    <a href="clock.html" class="button">
        <img src="images/2277975-5edecfd7.png" alt="Ceas">
        Ceas
    </a>
    <a href="integrame.html" class="button">
        <img src="images/3850692-3b3ffd59.png" alt="Integrame">
        Integrame
    </a>
</div>

    </div>
</body>
</html>
