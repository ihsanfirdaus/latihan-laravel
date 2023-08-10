// Chat.js

var baseUrl = window.baseUrl;
var token = window.token;
var fromUserId = window.fromUserId;
var toUserId = '';

var conn = new WebSocket('ws://127.0.0.1:8090/?token=' + token);

conn.onopen = function (e) {
    console.log("websocket.onOpen");
    console.log("base_url: ", baseUrl)

    loadConnectedUser(fromUserId);
}

conn.onmessage = function (e) {
    console.log("websocket.onMessage");

    var data = JSON.parse(e.data);

    if (data.response_connected_user) {
        var html = '<div class="list-group">';

        if (data.data.length > 0) {

            for (var count = 0; count < data.data.length; count++) {

                var name = data.data[count].name;
                var status = data.data[count].user_status;

                html += `
                    <a href="#" class="list-group-item d-flex justify-content-start" onclick="openChat('`+name+`')">
                        <div class="ms-2 me-auto">
                `;

                var user_image = '';

                var src = baseUrl + data.data[count].user_image;
                var src_image_not_found = baseUrl + '/img/no-image-user.png';

                if (data.data[count].user_image !== null) {
                    user_image = `<img src="` + src + `"width="40" class="rounded-circle" />`
                } else {
                    user_image = `<img src="` + src_image_not_found + `" width="40" alt="no-image" class="rounded-circle" />`;
                }

                html += `
                            &nbsp; `+ user_image + ` &nbsp; <b>` + data.data[count].name + `</b> 
                            <small class="text-muted">`+status+`</small>
                        </div>
                    </a>`;
            }
        } else {
            html += 'User tidak ditemukan';
        }

        html += '</div>';

        document.getElementById('connected_user').innerHTML = html;
    }
}

function loadConnectedUser(fromUserId) {
    var data = {
        from_user_id: fromUserId,
        type: 'request_load_connected_user'
    }

    conn.send(JSON.stringify(data));
}

function openChat(toUsername) {
    document.getElementById('chat_header').innerHTML = toUsername;
}

function closeChat() {
    document.getElementById('chat_header').innerHTML = `Chat Messages`;

    toUserId = '';
}