<?php
$j = file_get_contents('C:/Users/LENOVO/.cursor/projects/c-Users-LENOVO-Desktop-back-authority-authority2-api2/agent-tools/7e74b4cf-b7c3-46e2-9d26-dcd19c817c79.txt');
$routes = json_decode($j, true);
function hasSanctum($m){ foreach((array)$m as $x){ if(stripos($x,'Authenticate:sanctum')!==false) return true; } return false; }
$fin=[];
foreach($routes as $r){
  if(!hasSanctum($r['middleware']??[]) || !str_starts_with($r['uri'],'api/finance/')) continue;
  $parts=explode('/', str_replace('api/finance/','',$r['uri']));
  $fin[$parts[0]??'?']=($fin[$parts[0]??'?']??0)+1;
}
arsort($fin); foreach($fin as $k=>$v) echo "$k: $v\n";
