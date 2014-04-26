<?php

namespace carlescliment\Server;

class Server {

    private $sockets = [];

    private $address;
    private $port;


    public function __construct($address, $port)
    {
        $this->address = $address;
        $this->port = $port;
    }


    public function run()
    {
        if (($master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) < 0) {
            echo "socket_create() failed, reason: " . socket_strerror($master) . "\n";
        }

        socket_set_option($master, SOL_SOCKET,SO_REUSEADDR, 1);

        if (($ret = socket_bind($master, $this->address, $this->port)) < 0) {
            echo "socket_bind() failed, reason: " . socket_strerror($ret) . "\n";
        }

        if (($ret = socket_listen($master, 5)) < 0) {
            echo "socket_listen() failed, reason: " . socket_strerror($ret) . "\n";
        }
        else {
            $started=time();
            echo "[".date('Y-m-d H:i:s')."] SERVER CREATED ( MAXCONN:".SOMAXCONN." ) \n";
            echo "[".date('Y-m-d H:i:s')."] Listening on ".$this->address.":".$this->port."\n";
        }

        $this->sockets['master'] = $master;

        while (true) {
            $changed_sockets = $this->sockets;
            $write = $except = null;
            $num_changed_sockets = socket_select($changed_sockets, $write, $except, null);

            foreach($changed_sockets as $socket) {
                $id = intval($socket);
                if ($socket == $master) {
                    if (($client = socket_accept($master)) < 0) {
                        echo "socket_accept() failed: reason: " . socket_strerror($msgsock) . "\n";
                        continue;
                    }
                    else {
                        $this->sockets[intval($socket)] = $client;
                        echo "[".date('Y-m-d H:i:s')."] CONNECTED ID:$id "."(".count($this->sockets)."/".SOMAXCONN.")\n";
                    }
                }
                else {
                    $bytes = @socket_recv($socket, $buffer, 2048, 0);

                    if (strlen($buffer) == 0)
                    {
                      $this->sendmsg($socket, "You quitted!");
                      $this->disconnectSocket($socket);
                      echo "[".date('Y-m-d H:i:s')."] QUIT ".$id."\n";
                    }
                    else {
                        echo "SENT message: $buffer";
                    }
                }
            }
        }
    }


    private function disconnectSocket($socket_to_remove)
    {
        foreach ($this->sockets as $id => $socket) {
            if ($socket == $socket_to_remove) {
                unset($this->sockets[$id]);
                @socket_shutdown($socket, 2);
                @socket_close($socket);
            }
        }
    }


    private function sendmsg($socket,$msg)
    {
        socket_write($socket, $msg.chr(0)); //send to the recipient
    }
}

$server = new Server('localhost', '9000');

$server->run();