<?php
$j = file_get_contents('C:/Users/LENOVO/.cursor/projects/c-Users-LENOVO-Desktop-back-authority-authority2-api2/agent-tools/7e74b4cf-b7c3-46e2-9d26-dcd19c817c79.txt');
$routes = json_decode($j, true);
function hasSanctum($m){ foreach((array)$m as $x){ if(stripos($x,'Authenticate:sanctum')!==false) return true; } return false; }
function methodStr($m){ return is_array($m)?implode('|',$m):(string)$m; }
$trainingPrefixes=['training-','trainers','trainees','trainer-profiles','my-trainer-profile','registration-requests','certificates','program-bank','training-kit'];
$authRoutes=[]; $training=0;
foreach($routes as $r){
  if(!hasSanctum($r['middleware']??[])) continue;
  $uri=$r['uri'];
  foreach($trainingPrefixes as $p){ if(str_contains($uri,"api/$p")||str_contains($uri,"api/".$p)) { $training++; break; } }
  if(str_contains($uri,'api/admin/') && (str_contains($r['action']??'','UserAccess')||str_contains($r['action']??'','Role')||str_contains($r['action']??'','Permission'))) $authRoutes[]=$r;
}
echo "Training-cluster protected routes: $training\n";
echo "Admin access routes sample:\n";
foreach(array_slice($authRoutes,0,8) as $r){
  $sp=''; foreach($r['middleware'] as $mw){ if(str_contains($mw,'Middleware:')) $sp=$mw; }
  echo methodStr($r['method']).' '.$r['uri'].' | '.basename(str_replace('\\','/',$r['action']))."\n  $sp\n";
}
