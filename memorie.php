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
    $memoryScore = $_POST['patient_mem'];

    if ($user_type == 'patient') {
        
        $sql = "UPDATE patients SET patient_mem=? WHERE patient_id=?";
    } else {
        
        $sql = "UPDATE users SET user_mem=? WHERE user_uid=?";
    }

    $stmt = $conn->prepare($sql);
    
    if ($user_type == 'patient') {
        $stmt->bind_param("ii", $memoryScore, $user_id);
    } else {
        $stmt->bind_param("ii", $memoryScore, $user_id);
    }

    if ($stmt->execute()) {
        echo "Scorul a fost actualizat cu succes!";
    } else {
        echo "Eroare la actualizarea scorului: " . $stmt->error;
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
    <title>Memorie</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #DFD9E2;
        }
        #game-container {
            display: grid;
            grid-gap: 10px;
            margin-top: 20px;
        }
        .card {
            width: 80px;
            height: 80px;
            background-color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            cursor: pointer;
            transition: transform 0.3s;
            transform-style: preserve-3d;
        }
        .card.flipped {
            transform: rotateY(180deg);
        }
        .card-front, .card-back {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .card-back {
            background-color: #ffffff;
            transform: rotateY(180deg);
        }
        #controls {
            margin-top: 20px;
        }
        #game-info {
            margin-top: 20px;
            font-size: 18px;
        }
        button {
    background-color: #9575cd;
    color: white;
    border: none;
    border-radius: 7px;
    padding: 10px 20px;
    font-size: 16px;
    cursor: pointer;
}
select {
    background-color: #9575cd;
    color: white;
    border: none;
    border-radius: 7px;
    padding: 10px;
    font-size: 16px;
    cursor: pointer;
}

option {
    background-color: white;
    color: black;
}
    </style>
</head>
<body>
    
    <div id="controls">
        <label for="card-type">Tip Card:</label>
        <select id="card-type">
            <option value="emoji">Emoji</option>
            <option value="mandarină">Mandarin</option>
        </select>
        <label for="card-count">Număr de Cartonașe:</label>
        <select id="card-count">
            <option value="16">16</option>
            <option value="36">36</option>
            <option value="64">64</option>
        </select>
        <button id="start-game">Începe Jocul</button>
        <button id="back-button">Înapoi la pagina utilizatorului</button>
    </div>
    <div id="game-info">
        Timp: <span id="timer">0</span> secunde | Greșeli: <span id="mistakes">0</span>
    </div>
    <div id="game-container"></div>

    <script>
        const emojis = ['🐶', '🐱', '🐭', '🐹', '🐰', '🦊', '🐻', '🐼', '🐨', '🐯', '🦁', '🐮', '🐷', '🐸', '🐵', '🐔', '🐧', '🐦', '🦆', '🦉', '🦇', '🐺', '🐗', '🐴', '🦄', '🐝', '🐛', '🦋', '🐌', '🐞', '🐜', '🕷️', '🦂', '🦀', '🐍'];
        const mandarinChars = ['爱', '和', '平', '美', '善', '真', '信', '望', '德', '智', '勇', '诚', '孝', '忠', '仁', '义', '礼', '廉', '耻', '勤', '谦', '宽', '温', '良', '恭', '俭', '让', '忍', '慈', '悲', '喜', '舍', '慧', '觉', '悟'];
        let cards = [];
        let flippedCards = [];
        let matchedPairs = 0;
        let mistakes = 0;
        let timer;
        let seconds = 0;

        document.getElementById('back-button').addEventListener('click', function() {
        window.location.href = 'user.php';
    });

        function shuffleArray(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
        }

        function createCard(content) {
            const card = document.createElement('div');
            card.className = 'card';
            card.innerHTML = `
                <div class="card-front"></div>
                <div class="card-back">${content}</div>
            `;
            card.addEventListener('click', flipCard);
            return card;
        }

        function flipCard() {
            if (flippedCards.length < 2 && !this.classList.contains('flipped') && !this.classList.contains('matched')) {
                this.classList.add('flipped');
                flippedCards.push(this);

                if (flippedCards.length === 2) {
                    setTimeout(checkMatch, 500);
                }
            }
        }

        function checkMatch() {
            const [card1, card2] = flippedCards;
            if (card1.innerHTML === card2.innerHTML) {
                card1.classList.add('matched');
                card2.classList.add('matched');
                matchedPairs++;

                if (matchedPairs === cards.length / 2) {
                    clearInterval(timer);
                    const memoryScore = 100 - mistakes;
                    alert(`Felicitări! Ai câștigat în ${seconds} secunde cu ${mistakes} greșeli!`);
                    sendScore(memoryScore);
                }
            } else {
                card1.classList.remove('flipped');
                card2.classList.remove('flipped');
                mistakes++;
                updateMistakes();
            }
            flippedCards = [];
        }

        function updateTimer() {
            seconds++;
            document.getElementById('timer').textContent = seconds;
        }

        function updateMistakes() {
            document.getElementById('mistakes').textContent = mistakes;
        }

        function startGame() {
            const gameContainer = document.getElementById('game-container');
            gameContainer.innerHTML = '';
            cards = [];
            flippedCards = [];
            matchedPairs = 0;
            mistakes = 0;
            seconds = 0;
            clearInterval(timer);

            const cardType = document.getElementById('card-type').value;
            const cardCount = parseInt(document.getElementById('card-count').value);
            const symbols = cardType === 'emoji' ? emojis : mandarinChars;

            const gridSize = Math.sqrt(cardCount);
            gameContainer.style.gridTemplateColumns = `repeat(${gridSize}, 1fr)`;

            const selectedSymbols = symbols.slice(0, cardCount / 2);
            const cardContents = [...selectedSymbols, ...selectedSymbols];
            shuffleArray(cardContents);

            for (let content of cardContents) {
                const card = createCard(content);
                cards.push(card);
                gameContainer.appendChild(card);
            }

            updateMistakes();
            document.getElementById('timer').textContent = '0';
            timer = setInterval(updateTimer, 1000);
        }

        function sendScore(memoryScore) {
            const formData = new FormData();
            formData.append('patient_mem', memoryScore);

            fetch('memorie.php', {
                method: 'POST',
                body: formData
            }).then(response => response.text()).then(data => {
                console.log('Data successfully submitted:', data);
            }).catch(error => {
                console.error('Error submitting data:', error);
            });
        }

        document.getElementById('start-game').addEventListener('click', startGame);
    </script>
</body>
</html>
