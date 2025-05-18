<?php
include("include.php");
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Pääsivu</title>
</head>
<body>
    <?php printMenu(); ?>
    <h1>Tervetuloa, <?= htmlspecialchars($username) ?>!</h1>
    <button onclick="window.location.href='logout.php'">Kirjaudu ulos</button>

    <h2>Luo huone</h2>
    <label for="roomName">Huoneen nimi:</label>
    <input type="text" id="roomName" name="roomName" required>
    <button type="button" onclick="createRoom()">Luo</button>

    <h2>Huoneet</h2>
    <div id="roomsList">Ladataan huoneita...</div>

    <h2>Liity huoneeseen</h2>
    <label for="joinRoomId">Huoneen ID:</label>
    <input type="number" id="joinRoomId" name="joinRoomId" min="0" required>
    <button type="button" onclick="joinRoom()">Liity</button>
    <div id="joinMessage"></div>
    
    <div id="message"></div>
    <style>
        #message, #joinMessage {
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
    <script>
        const currentUserId = <?= json_encode($_SESSION['user_id'] ?? null) ?>;
        const isAdmin = <?= json_encode($_SESSION['isadmin'] ?? 0) ?>;

        async function createRoom() {
            const nameInput = document.getElementById('roomName');
            const name = nameInput.value.trim();
            const messageDiv = document.getElementById('message');

            if (!name) {
                messageDiv.textContent = "Huoneen nimi ei voi olla tyhjä";
                messageDiv.style.color = "red";
                return;
            }

            try {
                const response = await fetch("rooms", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ room_name: name })
                });
                const data = await response.json();

                if (response.ok) {
                    messageDiv.textContent = "Huone luotu: ID " + data.id;
                    messageDiv.style.color = "green";
                    nameInput.value = "";
                } else {
                    messageDiv.textContent = data.error || "Virhe huoneen luonnissa";
                    messageDiv.style.color = "red";
                }
            } catch (error) {
                messageDiv.textContent = "Yhteysvirhe: " + error.message;
                messageDiv.style.color = "red";
            }
        }

        async function loadRooms() {
            const roomsDiv = document.getElementById("roomsList");
            try {
                const response = await fetch("rooms");
                if (!response.ok) throw new Error("Failed to load rooms");
                const rooms = await response.json();

                if (rooms.length === 0) {
                    roomsDiv.textContent = "Ei huoneita.";
                    return;
                }

                roomsDiv.innerHTML = "";
                rooms.forEach(room => {
                    const div = document.createElement("div");
                    div.textContent = `#${room.id} ${room.room_name} (Status: ${room.status}) `;

                    if (room.host_id == currentUserId || isAdmin == 1) {
                        const deleteBtn = document.createElement("button");
                        deleteBtn.textContent = "Poista";
                        deleteBtn.style.marginLeft = "10px";
                        deleteBtn.onclick = async () => {
                            if (confirm(`Haluatko varmasti poistaa huoneen "${room.room_name}"?`)) {
                                try {
                                    const response = await fetch(`rooms/${room.id}`, {
                                        method: "DELETE"
                                    });

                                    const data = await response.json();
                                    if (response.ok) {
                                        alert("Huone poistettu.");
                                        loadRooms();
                                    } else {
                                        alert(data.error || "Virhe huoneen poistossa.");
                                    }
                                } catch (err) {
                                    alert("Virhe huoneen poistossa: " + err.message);
                                }
                            }
                        };
                        div.appendChild(deleteBtn);
                    }

                    roomsDiv.appendChild(div);
                });
            } catch (err) {
                roomsDiv.textContent = "Virhe huoneiden lataamisessa: " + err.message;
            }
        }

        async function joinRoom() {
            const joinInput = document.getElementById("joinRoomId");
            const roomId = joinInput.value.trim();
            const joinMessage = document.getElementById("joinMessage");

            if (!roomId) {
                joinMessage.textContent = "Anna huoneen ID.";
                joinMessage.style.color = "red";
                return;
            }

            try {
                const response = await fetch("rooms/join", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({ room_id: roomId })
                });

                const data = await response.json();

                if (response.ok) {
                    joinMessage.textContent = `Liityit huoneeseen #${roomId}.`;
                    joinMessage.style.color = "green";
                    // Reload rooms list to update players shown
                    loadRooms();
                } else {
                    joinMessage.textContent = data.error || "Virhe huoneeseen liittymisessä.";
                    joinMessage.style.color = "red";
                }
            } catch (error) {
                joinMessage.textContent = "Virhe huoneeseen liittymisessä: " + error.message;
                joinMessage.style.color = "red";
            }
        }
        // Load rooms on page load
        loadRooms();
    </script>
</body>
</html>