<?php
session_start();


if (isset($_SESSION['patient_id'])) {
    
    $user_type = 'patient';
    $user_id = $_SESSION['patient_id'];
} elseif (isset($_SESSION['user_uid'])) {
    
    $user_type = 'individual_user';
    $user_id = $_SESSION['user_uid'];
} else {
    
    header("Location: login.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "conturi";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexiunea a eșuat: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $score = $_POST['score'];
    $avg_rt = $_POST['avg_rt'];

    if ($user_type == 'patient') {
        
        $sql = "UPDATE patients SET patient_math=?, patient_rt=? WHERE patient_id=?";
    } else {
        
        $sql = "UPDATE users SET user_math=?, user_rt=? WHERE user_uid=?";
    }

    $stmt = $conn->prepare($sql);
    
    if ($user_type == 'patient') {
        $stmt->bind_param("ssi", $score, $avg_rt, $user_id);
    } else {
        $stmt->bind_param("ssi", $score, $avg_rt, $user_id);
    }

    if ($stmt->execute()) {
        
    } else {
        
    }

    $stmt->close();
    $conn->close();
    exit();
}
?>


<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Joc de Matematică</title>
    <script src="https:
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #DFD9E2;
        }
        #game-container, #end-game-container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        #intrebare {
            font-size: 24px;
            margin-bottom: 20px;
        }
        #raspuns {
            font-size: 18px;
            padding: 5px;
            width: 100px;
        }
        #submit, #restart, #go-back {
    font-size: 18px;
    padding: 5px 10px;
    margin-left: 10px;
    background-color: #9575cd;
    color: white;
    border-radius: 5px;
    border: none;
}

        #scor, #timer, #greseli {
            font-size: 18px;
            margin-top: 20px;
        }
        #end-game-container {
            display: none;
        }
    </style>
</head>
<body>
    <div id="game-container">
        <div id="intrebare"></div>
        <input type="number" id="raspuns" step="0.1">
        <button id="submit">Răspunde</button>
        <div id="scor">Scor: 0</div>
        <div id="timer">Timp: 0s</div>
        <div id="greseli">Greșeli: 0</div>
    </div>

    <div id="end-game-container">
        <h2>Joc Terminat</h2>
        <div id="scor-final"></div>
        <div id="timp-total"></div>
        <div id="timp-medie-raspuns"></div>
        <form id="end-game-form">
    <input type="hidden" name="score" id="final-score">
    <input type="hidden" name="avg_rt" id="avg-response-time">
</form>
        <button id="restart">Restart</button>
        <button id="go-back">Înapoi la Pagina de Utilizator</button>
    </div>

    <script>
        const intrebareElement = document.getElementById('intrebare');
        const raspunsElement = document.getElementById('raspuns');
        const submitButton = document.getElementById('submit');
        const scorElement = document.getElementById('scor');
        const timerElement = document.getElementById('timer');
        const greseliElement = document.getElementById('greseli');
        const gameContainer = document.getElementById('game-container');
        const endGameContainer = document.getElementById('end-game-container');
        const finalScoreElement = document.getElementById('scor-final');
        const finalScoreInput = document.getElementById('final-score');
        const avgResponseTimeInput = document.getElementById('avg-response-time');
        const timpTotalElement = document.getElementById('timp-total');
        const timpMedieRaspunsElement = document.getElementById('timp-medie-raspuns');
        const endGameForm = document.getElementById('end-game-form');
        const restartButton = document.getElementById('restart');
        const goBackButton = document.getElementById('go-back');

        let scor = 0;
        let greseli = 0;
        let questionCount = 0;
        let startTime;
        let questionStartTime;
        let timerInterval;
        let totalResponseTime = 0;
        let raspunsCurent;

        function generateQuestion() {
            let num1, num2, operator;
            const difficulty = Math.floor(questionCount / 25);

            if (difficulty === 0) {
                num1 = Math.floor(Math.random() * 10) + 1;
                num2 = Math.floor(Math.random() * 10) + 1;
                operator = Math.random() < 0.5 ? '+' : '-';
            } else if (difficulty === 1) {
                num1 = Math.floor(Math.random() * 20) + 1;
                num2 = Math.floor(Math.random() * 20) + 1;
                operator = ['+', '-', '*'][Math.floor(Math.random() * 3)];
            } else if (difficulty === 2) {
                if (Math.random() < 0.7) {
                    num1 = Math.floor(Math.random() * 10) + 1;
                    num2 = Math.floor(Math.random() * 10) + 1;
                } else {
                    num1 = (Math.floor(Math.random() * 10) + 1) * 10;
                    num2 = Math.floor(Math.random() * 10) + 1;
                }
                operator = ['+', '-', '*', '/'][Math.floor(Math.random() * 4)];
            } else {
                num1 = +(Math.random() * 10).toFixed(1);
                num2 = +(Math.random() * 10).toFixed(1);
                operator = Math.random() < 0.5 ? '+' : '-';
            }

            if (operator === '/') {
                num1 = num1 * num2;
            }

            const question = `${num1} ${operator} ${num2}`;
            intrebareElement.textContent = question;
            return math.evaluate(question);
        }

        function startGame() {
            scor = 0;
            greseli = 0;
            questionCount = 0;
            totalResponseTime = 0;
            startTime = Date.now();
            updateScore();
            updateMistakes();
            nextQuestion();  
            timerInterval = setInterval(updateTimer, 1000);
            gameContainer.style.display = 'block';
            endGameContainer.style.display = 'none';
        }

        function nextQuestion() {
            if (questionCount >= 100) {
                endGame();
                return;
            }
            questionCount++;
            raspunsElement.value = '';
            raspunsCurent = generateQuestion();
            questionStartTime = Date.now();
        }

        function checkAnswer() {
            const raspunsUser = parseFloat(raspunsElement.value);
            const timpRaspuns = (Date.now() - questionStartTime) / 1000;
            totalResponseTime += timpRaspuns;

            if (Math.abs(raspunsUser - raspunsCurent) < 0.1) {
                scor++;
                updateScore();
                nextQuestion();
            } else {
                greseli++;
                updateMistakes();
                if (greseli >= 5) {
                    endGame();
                } else {
                    nextQuestion();
                }
            }
        }

        function updateScore() {
            scorElement.textContent = `Scor: ${scor}`;
        }

        function updateMistakes() {
            greseliElement.textContent = `Greșeli: ${greseli}`;
        }

        function updateTimer() {
            const timpElapsed = Math.floor((Date.now() - startTime) / 1000);
            timerElement.textContent = `Timp: ${timpElapsed}s`;
        }

        function endGame() {
    clearInterval(timerInterval);
    const timpFinal = Math.floor((Date.now() - startTime) / 1000);
    const timpMedieRaspuns = (totalResponseTime / questionCount).toFixed(2);

    finalScoreElement.textContent = `Scor Final: ${scor}`;
    timpTotalElement.textContent = `Timp Total: ${timpFinal}s`;
    timpMedieRaspunsElement.textContent = `Timp Mediu de Răspuns: ${timpMedieRaspuns}s`;

    finalScoreInput.value = scor;
    avgResponseTimeInput.value = timpMedieRaspuns;

    
    const formData = new FormData(endGameForm);
    fetch('', {
        method: 'POST',
        body: formData
    }).then(response => response.text()).then(data => {
        console.log('Data successfully submitted:', data);
    }).catch(error => {
        console.error('Error submitting data:', error);
    });

    gameContainer.style.display = 'none';
    endGameContainer.style.display = 'block';
}


        submitButton.addEventListener('click', checkAnswer);
        raspunsElement.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                checkAnswer();
            }
        });
        restartButton.addEventListener('click', startGame);
        goBackButton.addEventListener('click', () => {
            window.location.href = 'user.php';
        });

        startGame();
    </script>
</body>
</html>
