<?php 


namespace SockApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Manager implements MessageComponentInterface {
    protected $clients;
    protected $games;
    protected $totalPlayers=2;
    protected $totalNameSet=0;

    public function __construct() {

        $this->clients = new \SplObjectStorage;
        $this->games= new \SplObjectStorage; 

        
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        if($this->totalPlayers==sizeof($this->clients)){
            echo "Lleno\n";
            $conn->send('{"id":"full"}');
            $conn->close();
        }
        $this->clients->attach($conn);
        $game= new Game($conn->resourceId); //Ahora no se sobreescribe la variable game.
        $conn->send($game->firstLoad());
        $conn->send($game->countExercises());//se envian el total de ejercicios
        $this->games->attach($game); //  Se añade un nuevo juego a la array de objetos games.

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        $jsonMsg=json_decode($msg);
        echo sprintf('Connection %d sending message to %d other connection%s' . "\n"
            , $from->resourceId, $numRecv, $numRecv == 1 ? '' : 's');

        $this->decode($jsonMsg,$from);
        
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
        if($this->totalNameSet>0){
        $this->totalNameSet=$this->totalNameSet-1;
        } 
        echo "Connection {$conn->resourceId} has disconnected\n";
        echo "Nombre seteados".$this->totalNameSet."</br>";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();

    }
    protected function decode($msg,$from){
        switch ($msg->id) {
            case 'name':
                    $this->loadName($from,$msg);
                break;
            case 'answer':
                    $this->answer($from,$msg);
            break;    
            case "finish": 
                    $this->finish($from);   
                break;
            case "correct":
                    echo "correct recibido".$from->resourceId;
                    $this->otherCorrect($from);
                break;    
            default:
                # code...
                break;
        }



    }
    protected function loadName($from,$msg){
        foreach ($this->clients as $client) {
            if ($from !== $client) {
               // The sender is not the receiver, send to each client connected
                echo"enviando nombre";
                $client->send('{"id":"name", "name":"'.$msg->name.'", "number":"'.$from->resourceId.'"}');
                
            }
        }
        $this->SetNameAndCheckStart();
    }
    protected function finish($from){
        $id=$from->resourceId;
        foreach ($this->clients as $client) {
            if ($from !== $client) {
               // The sender is not the receiver, send to each client connected
                echo $from->resourceId." ha finalizado la partida(enviando otherFinish) </br>";
                echo $id;
                $client->send('{"id":"otherFinish","name":"'.$id.'"}');
            }
        }

    }

    public function answer($from,$msg){
        $answer;
        foreach ($this->games as $el) {
                    if($el->id==$from->resourceId){

                        $from->send($el->validate($msg->{"res"}));
                    }
                }
    }
    public function SetNameAndCheckStart(){
        $this->totalNameSet=$this->totalNameSet+1;
        echo "Nombre seteados".$this->totalNameSet."</br>";
        if($this->totalNameSet==$this->totalPlayers){
            $this->start();
        }

    }
    public function start(){

            foreach ($this->clients as $client) {
                $client->send('{"id":"start"}');
            }

        
    }
    public function otherCorrect($from){
        foreach ($this->clients as $client) {
            if($from != $client){

                $client->send('{"id":"otherCorrect","user":"'.$from->resourceId.'"}');
            }
        }
    }

}

