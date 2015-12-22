<?php

function MakeTheSocketFile()
{
    if(!is_dir("remote"))
    {
        mkdir("remote", 0777);
    }
    $base = "module.exports={run:function(server){var io=require('socket.io').listen(server);var clients=[];io.sockets.on('connection',function(socket){socket.on('token',function(token){clients.push({id:socket.id,token:token});});/*--*/});}}";
    $pattern = "socket.on('%REPLACE%',function(data){for(var client in clients){if(clients[client].token===data.token)io.to(clients[client].id).emit('%REPLACE%',true);}});/*--*/";
    $o = file_get_contents('app/map.json');
    $o = json_decode($o);

    $base_array = explode("/*--*/", $base);
    $end_base = end($base_array);
    array_pop($base_array);

    foreach($o AS $k => $v)
    {
        $k = str_replace("%REPLACE%", $k, $pattern);
        array_push($base_array, $k);
    }

    array_push($base_array, $end_base);
    file_put_contents(__DIR__."/remote/sockets.js", implode("", $base_array));
}

function MakeTheRemoteFile($host)
{
    $patternRemote = "var socket=io.connect('%HOST%');if(/Mobi/.test(navigator.userAgent)){var href=document.location.href;var id=href.substr(href.lastIndexOf('/')+1);if(window.DeviceMotionEvent){window.addEventListener('devicemotion',function(event){var x=event.accelerationIncludingGravity.x;var y=event.accelerationIncludingGravity.y;if(x>0){%ONRIGHT%}else{%ONLEFT%}if(y>0){%ONUP%}else{%ONDOWN%}});}/*--*/";
    $actionRemote = "document.getElementById('%ELEMENT%').on%REPLACE%=function(){socket.emit('%ACTION%',{token:id});}/*--*/";
    $patternWeb = "else{var text='';var possible='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';for(var i=0;i<5;i++)text+=possible.charAt(Math.floor(Math.random()*possible.length));document.title=text;socket.emit('token',text);";
    $actionWeb = "socket.on('%ONRECEIVE%',function(){%ACTION%;});";

    $o = file_get_contents('app/map.json');
    $o = json_decode($o);

    foreach($o AS $k => $v)
    {
        if($k === "onRight") {
            $patternRemote = str_replace("%ONRIGHT%", "socket.emit('onRight',{token:id});", $patternRemote);
        } 
        if($k === "onLeft") {
            $patternRemote = str_replace("%ONLEFT%", "socket.emit('onLeft',{token:id});", $patternRemote);
        }
        if($k === "onUp") {
            $patternRemote = str_replace("%ONUP%", "socket.emit('onUp',{token:id});", $patternRemote);
        }
        if($k === "onDown") {
            $patternRemote = str_replace("%ONDOWN%", "socket.emit('onDown',{token:id});", $patternRemote);
        }
        if($k !== "onRight" && $k !== "onLeft" && $k !== "onUp" && $k !== "onDown")
        {
            $z = explode("On", $k);
            $q = str_replace("%ELEMENT%", $z[1], $actionRemote);
            $q = str_replace("%REPLACE%", $z[0], $q);
            $q = str_replace("%ACTION%", $k, $q);
            $patternRemote .= $q;
        }
    }
    $patternRemote .= "}";
    
    foreach($o AS $k => $v)
    {
        $q = str_replace("%ONRECEIVE%", $k, $actionWeb);
        $q = str_replace("%ACTION%", $v, $q);
        $patternWeb .= $q;
    }

    $patternWeb .= "}";
    $patternRemote .= $patternWeb;
    $patternRemote = str_replace("%HOST%", $host, $patternRemote);
    $patternRemote = str_replace("%ONRIGHT%", "", $patternRemote);
    $patternRemote = str_replace("%ONLEFT%", "", $patternRemote);
    $patternRemote = str_replace("%ONUP%", "", $patternRemote);
    $patternRemote = str_replace("%ONDOWN%", "", $patternRemote);
    file_put_contents(__DIR__."/remote/remote.js", $patternRemote);
}

$args = $argv;

MakeTheSocketFile();
MakeTheRemoteFile($args[1]);
