<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\WebSocket\MessageComponentInterface;

class SocketController extends Controller implements MessageComponentInterface
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);

        $queryString = $conn->httpRequest->getUri()->getQuery();

        parse_str($queryString, $queryArray);

        if (isset($queryArray['token'])) {
            User::query()
                ->where('token','=',$queryArray['token'])
                ->update(['connection_id' => $conn->resourceId]);
        }
    }

    public function onMessage(ConnectionInterface $conn, MessageInterface $msg)
    {
        $data = json_decode($msg);

        if (isset($data->type))
        {
            if ($data->type == 'request_load_unconnected_user')
            {
                $user_data = User::query()
                    ->select('id','name','user_status','user_image')
                    ->where('id','!=',$data->from_user_id)
                    ->orderBy('name','ASC')
                    ->get();

                $sub_data = [];

                foreach ($user_data as $item)
                {
                    $sub_data[] = [
                        'id' => $item->id,
                        'name' => $item->name,
                        'user_status' => $item->user_status,
                        'user_image' => $item->user_image
                    ];
                }

                $sender_connection_id = User::query()
                    ->select('connection_id')
                    ->where('id','=',$data->from_user_id)
                    ->first();

                $send_data['data'] = $sub_data;
                $send_data['response_load_unconnected_user'] = true;
                
                foreach ($this->clients as $client)
                {
                    if ($client->resourceId === $sender_connection_id->connection_id) {
                        $client->send(json_encode($send_data));
                    }
                }
            }

            if ($data->type == 'request_load_connected_user')
            {
                $sender = User::query()
                    ->select('id','connection_id')
                    ->where('id','=',$data->from_user_id)
                    ->first();
                
                $user_connected_chat = User::query()
                    ->select('id','name','connection_id','user_image','user_status')
                    ->where('connection_id','!=',0)
                    ->where('id','!=', $sender->id)
                    ->get();

                foreach ($user_connected_chat as $item)
                {
                    $sub_data[] = [
                        'id' => $item->id,
                        'name' => $item->name,
                        'user_status' => $item->user_status,
                        'user_image' => $item->user_image
                    ];
                }

                foreach ($this->clients as $client)
                {
                    if ($client->resourceId == $sender->connection_id) {
                        $send_data['response_connected_user'] = true;
                        $send_data['data'] = $sub_data;
                        $client->send(json_encode($send_data));
                    }
                }
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);

        $queryString = $conn->httpRequest->getUri()->getQuery();

        parse_str($queryString, $queryArray);

        if (isset($queryArray['token'])) {
            User::query()
                ->where('token','=',$queryArray['token'])
                ->update(['connection_id' => 0]);
        }
    }

    public function onError(ConnectionInterface $conn, Exception $e)
    {
        echo "An error has occured: {$e->getMessage()}\n";

        $conn->close();
    }
}
