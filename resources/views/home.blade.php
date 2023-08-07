@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Kontak</div>
                <div class="card-body">

                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Chat</div>
                <div class="card-body">
                    
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                {{-- <div class="card-header">Undang Teman</div> --}}
                <div class="card-body">
                    <div id="invite_user"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script>
        var conn = new WebSocket('ws://127.0.0.1:8090/?token={{ auth()->user()->token }}');
        var fromUserId = '{{ auth()->user()->id }}';
        var toUserId = '';

        conn.onopen = function(e) {
            console.log("websocket.onOpen");

            loadUnconnectedUser(fromUserId);
        }

        conn.onmessage = function(e) {
            console.log("websocket.onMessage");

            var data = JSON.parse(e.data);

            if (data.response_load_unconnected_user)
            {
                var html = '';

                if (data.data.length > 0) 
                {
                    html += '<ul class="list-group">';

                    for (var count = 0; count < data.data.length; count++) 
                    {
                        var user_image = '';

                        if (data.data[count].user_image !== null) {
                            user_image = `<img src="{{ asset("images/") }}" /`+data.data[count].user_image+`
                            width="40" class="rounded-circle" />`
                        } else {
                            user_image = `<img src="{{ asset("img/no-image-user.png") }}" 
                            width="40" class="rounded-circle" />`;
                        }

                        html += `
                            <li class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col col-9">`+user_image+`&nbsp;`+data.data[count].name+`</div>    
                                    <div class="col col-3">
                                        <button type="button">Kirim</button>    
                                    </div>
                                </div>    
                            </li>
                        `;

                    }

                    html += '</ul>';
                } else {
                    html = 'No User Found';
                }

                document.getElementById('invite_user').innerHTML = html;
            }
        }

        function loadUnconnectedUser(fromUserId)
        {
            var data = {
                from_user_id: fromUserId,
                type: 'request_load_unconnected_user'
            }

            conn.send(JSON.stringify(data));
        }
    </script>
@endsection
