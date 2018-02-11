let webSocket = new WebSocket("ws://localhost:8080");

webSocket.onopen = function() {
    console.log("Connection has established");
};

webSocket.onmessage = function(event) {
    console.log(event.data);
}