<?php
$j = file_get_contents('C:/Users/LENOVO/.cursor/projects/c-Users-LENOVO-Desktop-back-authority-authority2-api2/agent-tools/7e74b4cf-b7c3-46e2-9d26-dcd19c817c79.txt');
$routes = json_decode($j, true);
foreach($routes as $r){
  if($r['uri']==='api/map/trainers'||$r['uri']==='api/map/training-courses'){
    echo $r['uri'].":\n"; foreach($r['middleware'] as $m) echo "  $m\n";
  }
}
