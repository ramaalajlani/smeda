<?php
$j = file_get_contents('C:/Users/LENOVO/.cursor/projects/c-Users-LENOVO-Desktop-back-authority-authority2-api2/agent-tools/7e74b4cf-b7c3-46e2-9d26-dcd19c817c79.txt');
$routes = json_decode($j, true);
$by=[]; foreach($routes as $r){ $k=($r['method']??'').' '.$r['uri']; $by[$k][]=$r['action']; }
foreach($by as $k=>$acts){ if(count($acts)>1) echo "$k\n  ".implode("\n  ",$acts)."\n"; }
