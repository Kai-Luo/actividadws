
var data;
var conn;
var usersList=new Array();
var percent;

$(document).ready(function(){
	connection();
	$("#main, #list, #userName, #finish").hide();
	$("#sendName").click(function (){
		loadUserName();
		sendName();
		$(this).prop("disabled",true);
		$("#nameInput").prop("disabled",true);
		
	});



});


function connection(){
	conn = new WebSocket('wss://actividadws.herokuapp.com/:8080');
	conn.onopen = function(e) {
	    console.log("WebSocketConnection established!");
	};


	conn.onmessage = function(e) {
	   decode(e.data);
	   //var msg=JSON.parse(e.data);
	   //console.log(msg.answer);
	};
}

function decode(data){
	var msg=JSON.parse(data);
	selection(msg);
	
}

function selection(msg){
	switch(msg.id){
		case "start":
			showMain();
		break;
		case "response":
			loadResponse(msg);
		break;
		case "newlevel":
			setLevel(msg);//<-----ESTA POR DEFINIR
		break;	
		case "name":
			console.log("nombre recibido");
			console.log(msg.number);
			loadName(msg);
		break;
		case "finish":
			finish();
		break;
		case "otherFinish":
			otherCorrect(msg.name);
			otherFinish(msg);
		break;
		case "totalEx":
			percent=totalEx(msg.number);
		break;
		case "otherCorrect":
			console.log("Other correct recibido"+msg.user);
			otherCorrect(msg.user);
		break;
		case "full":
			console.log("Lleno");
			full();
		break;
		default:
			error();
	}
}

function loadResponse(msg){

	if(msg.subId==="incorrecta"){
		incorrecta();
	}
	else if(msg.subId==="correcta"){
		data=msg.ex;
		conn.send('{"id":"correct"}'); // Mirar de hacer esto en Manager.php
		loadExercise(msg.ex);
	}
}

function incorrecta(){
	
	document.getElementById("ex").innerHTML="Incorrecto";
	window.setTimeout(function(){ loadExercise(data);},2000);
}
function loadExercise(ex){
	
	document.getElementById("ex").innerHTML=ex;
}
function answer(){

	var respuesta=document.getElementById("respuesta").value;
	if(respuesta===""){

		incorrecta();
	}
	conn.send('{"id":"answer", "res":'+respuesta+'}');
}
function loadName(msg){
	usersList[msg.number]=msg.name;
	console.log(usersList[msg.number]);
	//$("#list").append("<div class='users' id='"+msg.number+"'></div>");
	$("#list").append("<div id='"+msg.number+"' class='progress'><div class='progress-label'>"+msg.name+"</div></div>");
	$( "#"+msg.number ).progressbar({
      value: 0 
    }); 
    $( "#"+msg.number).progressbar({
  max: 100
});
}
function sendName(){
	var name=$("#nameInput").val();
	conn.send('{"id":"name", "name":"'+name+'"}');
}
function showMain(){
	$("#main, #list, #userName").fadeIn("slow");
	$("#setName").hide();
}
function loadUserName(){
	var name=$("#nameInput").val();
	document.getElementById("userName").innerHTML=name;

}
function finish(){
	conn.send('{"id":"finish"}');
	$("#ex, #respuesta, #send").hide();
	$("#finish").text("Has ganado");
	$("#finish").fadeIn("slow");
}
function otherFinish(msg){
	console.log("test otherfinish :"+usersList[msg.name]);
	var username=usersList[msg.name];
	$("#ex, #respuesta, #send").hide();
	$("#finish").text(username+" ha ganado");
	$("#finish").fadeIn("slow");
}
function totalEx(num){
	
	var percent;
	percent=100/num;
	return percent;

}
function otherCorrect(user){
	console.log(user);
	/*$("#"+user).animate({
		width:'+='+percent+'%'
	},500);

	*/
	var val = $( "#"+user ).progressbar( "value" );
   	$( "#"+user ).progressbar("option","value",val+percent); 
}	
function full(){

	$("#setName").text("La partida esta llena. Prueba mas tarde");
	

}
