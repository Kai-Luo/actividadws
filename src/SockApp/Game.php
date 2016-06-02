<?php
namespace SockApp;

class Game{
	public $id;
	private $tableLevel;
	private $exercisesLevel;
	private $i=0;
	private $l=0;
	

	function __construct($id){
		$this->id=$id;
		$this->connection();	
	}
	function connection(){

	//$openTxt=fopen("pruebajson.txt", "r") or die ("no SE puede abrir");
	//$txt=fread($openTxt,filesize("pruebajson.txt"));
	
	$this->tableLevel=json_decode('[
		{
		"name": "level 1",
			"exercise":[
				{"ex":"2+2","res":4},
				{"ex":"5+4","res":9},
				{"ex":"6+6","res":12},
				{"ex":"7+7","res":14},
				{"ex":"8+8","res":16}
			]
		},
		{
		"name": "level 2",
			"exercise":[
				{"ex":"12/6","res":2},
				{"ex":"20/5","res":4},
				{"ex":"30/6","res":5},
				{"ex":"50/2","res":25},
				{"ex":"30*6","res":180}
			]
		},
		{
		"name": "level 3",
			"exercise":[
				{"ex":"12*6","res":72},
				{"ex":"22*3","res":66},
				{"ex":"54*4","res":216},
				{"ex":"25*9","res":225},
				{"ex":"44*2","res":88}
			]
		}
		]'
	);

	$this->setlevel($this->i,$this->tableLevel);
	 


	}
	function firstLoad(){
		return $this->loadExercise();
	}


	//"$res" ha de ser la respuesta del cliente en formato JSON!!
	function validate($ans){
	    //Devuelve true si la respuesta es correcta.
	  $correct=$this->respuestaCorrecta($ans,$this->exercisesLevel);

	  if($correct){

	    //conn.send('{"arr":[{"msg":"Ejercicio Resuelto","user":"carlos"}]}');
	    echo("La respuesta es correcta");
	        //Comprueba si es el último ejercicio del nivel
	    if($this->lastExercise()){
	      if($this->lastLevel()){ 
	        //si no hay mas niveles sale de la funcion
	        echo("No hay mas niveles");
	        return $this->finish();
	      }
	      $this->i++;//variable global de la array tableLevel;
	      $this->setLevel($this->i,$this->tableLevel);//Carga el siguiente nivel!!!FALTA funcion para saber si es último nivel.
	      $this->l=-1;//SE iguala a -1 por que luego se hace un l++
	       
	    }
	    
	    $this->l++;//variable global index de la array exercisesLevel.
	   return $this->loadExercise();//Devuelve
	    
	  }
	  else{
	    echo("La respuesta no es correcta");
	    return '{"id":"response","subId":"incorrecta","ex":""}';

	  }
	}

	function setLevel($t,$arr) {

		for($l = 0; $l < sizeof($arr[$t]->exercise); $l++) {
	    $this->exercisesLevel=$arr[$t]->exercise;
			
	   }

	}
	function loadExercise(){
		$exercise=$this->exercisesLevel[$this->l]->ex;
		return '{"id":"response","subId":"correcta","ex":"'.$exercise.'"}';
	}
	function lastExercise(){
	  $size=sizeof($this->exercisesLevel)-1;
	  $actualIndex=$this->l;
	  if($size===$actualIndex){
	    return true;
	  }
	}
	function lastLevel(){
	  $size=sizeof($this->tableLevel)-1;
	  $actualIndex=$this->i;
	  if($size===$actualIndex){
	    return true;
	  }
	}

	function respuestaCorrecta($res,$arr){
	    $exActual=$this->exercisesLevel[$this->l]->{'ex'};
	    foreach ($arr as $el) {
	    	$respuesta=$res;
	    	if($el->{'ex'}==$exActual&&$el->{'res'}==$respuesta){
	    		return true;
	    	}
	    }
	    
	    
	  
	}
	function finish(){
		return '{"id":"finish","subId":"","ex":""}';
	}
	function countExercises(){
		$levels=count($this->tableLevel);
		$ex=count($this->exercisesLevel);
		$totalExercises=$levels*$ex;
		return '{"id":"totalEx", "number":'.$totalExercises.'}';
	}

} 
 ?>