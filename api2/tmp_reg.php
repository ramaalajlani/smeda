<?php
$j = file_get_contents('C:/Users/LENOVO/.cursor/projects/c-Users-LENOVO-Desktop-back-authority-authority2-api2/agent-tools/7e74b4cf-b7c3-46e2-9d26-dcd19c817c79.txt');
$routes = json_decode($j, true);
foreach($routes as $r){
  if(str_contains($r['uri'],'registration-requests') && str_contains($r['action']??'','show')){
    $mw=implode(' | ',$r['middleware']);
    echo methodStr($r['method']).' '.$r['uri'].' -> '.$r['action']."\n  $mw\n";
  }
}
function methodStr($m){ return is_array($m)?implode('|',$m):(string)$m; }
