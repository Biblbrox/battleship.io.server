let webSocket = new WebSocket("wss://0.0.0.0:");

webSocket.onopen = function() {
    console.log("Connection has established");
};

webSocket.onmessage = function(event) {
    let data = JSON.parse(event.data);
    console.log(event.data);
}
