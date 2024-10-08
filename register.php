<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "conturi";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function random_num($length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_type = $_POST['user_type'];

    if ($user_type == "owner") {
        
        $owner_name = $_POST['owner_name'] ?? '';  
        $owner_email = $_POST['owner_email'] ?? '';
        $owner_password = $_POST['owner_password'] ?? '';
        $owner_business = $_POST['owner_business'] ?? '';
        $owner_uid = random_num(20);

        
        if (preg_match('/\s/', $owner_email) || preg_match('/\s/', $owner_name) || preg_match('/\s/', $owner_business)) {
            die("Spațiile nu sunt permise");
        }

        
        $check_email = $conn->prepare("SELECT owner_email FROM owners WHERE owner_email = ?");
        $check_email->bind_param("s", $owner_email);
        $check_email->execute();
        $result = $check_email->get_result();

        if ($result->num_rows > 0) {
            die("Acest email a fost deja folosit");
        }

        
        $sql = $conn->prepare("INSERT INTO owners (owner_name, owner_email, owner_password, owner_business, owner_uid) VALUES (?, ?, ?, ?, ?)");
        $sql->bind_param("sssss", $owner_name, $owner_email, password_hash($owner_password, PASSWORD_DEFAULT), $owner_business, $owner_uid);

        if ($sql->execute()) {
            header("Location: index.php");
            exit();
        } else {
            echo "Eroare: " . $conn->error;
        }

    } else if ($user_type == "user") {
        
        $user_name = $_POST['user_name'] ?? '';
        $user_email = $_POST['user_email'] ?? '';
        $user_password = $_POST['user_password'] ?? '';
        $user_age = $_POST['user_age'] ?? '';
        $user_gender = $_POST['user_gender'] ?? '';
        $user_uid = random_num(20);

        
        if (preg_match('/\s/', $user_email) || preg_match('/\s/', $user_name)) {
            die("Spațiile nu sunt permise");
        }

        
        $check_email = $conn->prepare("SELECT user_email FROM users WHERE user_email = ?");
        $check_email->bind_param("s", $user_email);
        $check_email->execute();
        $result = $check_email->get_result();

        if ($result->num_rows > 0) {
            die("Acest email a fost deja folosit");
        }

        
        $sql = $conn->prepare("INSERT INTO users (user_name, user_email, user_password, user_age, user_gender, user_uid) VALUES (?, ?, ?, ?, ?, ?)");
        $sql->bind_param("ssssss", $user_name, $user_email, password_hash($user_password, PASSWORD_DEFAULT), $user_age, $user_gender, $user_uid);

        if ($sql->execute()) {
            header("Location: index.php");
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    }

    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Înregistrare</title>
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
    .register-form {
        background-color: white;
        padding: 2rem;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 1;
        width: 300px;
    }
    input, select {
        width: 100%;
        padding: 0.5rem;
        margin-bottom: 1rem;
        border: 1px solid #ccc;
        border-radius: 4px;
        transition: border 0.3s;
        box-sizing: border-box;
    }
    button {
        width: 100%;
        padding: 0.5rem;
        background-color: #9575cd;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    .tabs {
        display: flex;
        margin-bottom: 1rem;
    }
    .tab {
        flex: 1;
        text-align: center;
        padding: 0.5rem;
        cursor: pointer;
        border: 1px solid #ccc;
        background-color: #e0e0e0;
        transition: background-color 0.3s, border 0.3s;
        border-radius: 4px;
    }
    .tab.active {
        background-color: #9575cd;
        color: white;
        border-color: #9575cd;
    }
    select:focus, input:focus {
        outline: none;
        border: 1px solid #9575cd;
    }
    .login-link {
        text-align: center;
        margin-top: 1rem;
        font-size: 0.9rem;
    }
    .login-link a {
        color: #9575cd;
        text-decoration: none;
    }
    #owner-fields, #user-fields {
        display: none;
    }
    select {
    background-color: white;
    color: grey;
    border: 1px solid light grey;
    border-radius: 5px;
    padding: 10px;
    font-size: 14px;
    cursor: pointer;
}

</style>
    <script>
        function showForm(type) {
            
            document.getElementById('owner-fields').style.display = 'none';
            document.getElementById('user-fields').style.display = 'none';

            
            document.querySelectorAll('#owner-fields input').forEach(el => el.disabled = true);
            document.querySelectorAll('#user-fields input, #user-fields select').forEach(el => el.disabled = true);

            if (type === 'owner') {
                
                document.getElementById('owner-fields').style.display = 'block';
                document.querySelectorAll('#owner-fields input').forEach(el => el.disabled = false);
                document.getElementById('user_type').value = 'owner';
                document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
                document.querySelector('.tab.owner').classList.add('active');
            } else if (type === 'user') {
                
                document.getElementById('user-fields').style.display = 'block';
                document.querySelectorAll('#user-fields input, #user-fields select').forEach(el => el.disabled = false);
                document.getElementById('user_type').value = 'user';
                document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
                document.querySelector('.tab.user').classList.add('active');
            }
        }

        
        window.onload = function() {
            showForm('owner');
        };
    </script>
</head>
<body>
    <div class="container">
        <div class="background-shape"></div>
        <div class="register-form">
            <div class="tabs">
                <div class="tab owner active" onclick="showForm('owner')">Înregistrare Proprietar</div>
                <div class="tab user" onclick="showForm('user')">Înregistrare Utilizator</div>
            </div>
            <form action="register.php" method="post">
                <input type="hidden" name="user_type" id="user_type" value="owner"> <!-- Default to owner -->

                <!-- Owner Fields -->
                <div id="owner-fields">
                    <input type="text" name="owner_name" placeholder="Nume" required>
                    <input type="email" name="owner_email" placeholder="Email" required>
                    <input type="password" name="owner_password" placeholder="Parolă" required>
                    <input type="text" name="owner_business" placeholder="Nume afacere" required>
                </div>

                <!-- User Fields -->
                <div id="user-fields">
                    <input type="text" name="user_name" placeholder="Nume" required disabled>
                    <input type="email" name="user_email" placeholder="Email" required disabled>
                    <input type="password" name="user_password" placeholder="Parolă" required disabled>
                    <input type="number" name="user_age" placeholder="Vârsta" required disabled>
                    <select name="user_gender" required disabled>
                        <option value="" disabled selected>Gen</option>
                        <option value="male">Masculin</option>
                        <option value="female">Feminin</option>
                        <option value="other">Altul</option>
                    </select>
                </div>

                <button type="submit">Înregistrează-te</button>
            </form>
            <div class="login-link">
                <p>Ai deja cont?</p> <a href="login.php">Loghează-te</a>
            </div>
        </div>
    </div>
    <script>
    
    document.querySelector('form').addEventListener('submit', function(event) {
        let inputs = document.querySelectorAll('input');
        for (let input of inputs) {
            if (/\s/.test(input.value)) {
                alert('Spațiile nu sunt permise');
                event.preventDefault(); 
                return;
            }
        }
    });
</script>
</body>
</html>
