<!DOCTYPE html>
<html>
<head>
    <title>WebSocket Client</title>
</head>
<body>
<h1>Random Numbers</h1>
<div id="random-number"></div>

<script>
    const socket = new WebSocket('ws://localhost:8080');

    socket.onopen = (event) => {
        console.log('WebSocket connection established');
        socket.send(JSON.stringify({Id: 111})); // Здесь отправляем ID на сервер
    };


    socket.addEventListener('message', function (event) {
        const jsonData = JSON.parse(event.data);
        console.log('Received random number:', jsonData.qr);

        const randomNumberElement = document.getElementById('random-number');
        // randomNumberElement.textContent = jsonData.qr;
        randomNumberElement.textContent = `Received random number: ${jsonData.qr}`;

    });

    socket.onmessage = function(event) {
        console.log('WebSocket message received:', event.data);
        var data = JSON.parse(event.data);

        if (data.qr) {
            document.getElementById('random-number').innerText = data.qr;
        }
    };


    socket.addEventListener('close', function (event) {
        console.log('Connection closed.');
    });

</script>
</body>
</html>
