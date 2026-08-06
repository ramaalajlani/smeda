<?php
$j = file_get_contents('C:/Users/LENOVO/.cursor/projects/c-Users-LENOVO-Desktop-back-authority-authority2-api2/agent-tools/7e74b4cf-b7c3-46e2-9d26-dcd19c817c79.txt');
$routes = json_decode($j, true);
function methodStr($m){ return is_array($m) ? implode('|',$m) : (string)$m; }
function hasSanctum($m){ foreach((array)$m as $x){ if(stripos($x,'Authenticate:sanctum')!==false) return true; } return false; }
function spatie($m){ foreach((array)$m as $x){ if(preg_match('/(?:RoleOrPermission|Permission|Role)Middleware:(.+)$/',$x,$mm)) return $mm[1]; } return ''; }
function module($uri){ return explode('/', preg_replace('#^api/#','',$uri))[0] ?: 'root'; }

$byMod=[];
foreach($routes as $r){
  if(!hasSanctum($r['middleware']??[])) continue;
  $mod=module($r['uri']);
  $sp=spatie($r['middleware']);
  $byMod[$mod][]=[
    'method'=>methodStr($r['method']),
    'uri'=>$r['uri'],
    'action'=>$r['action'],
    'perm'=>$sp ? (str_contains($r['middleware'][count($r['middleware'])-1]??'','RoleOrPermission')?'role_or_permission:'.$sp:(str_contains(end($r['middleware']),'PermissionMiddleware')?'permission:'.$sp:'role:'.$sp)) : '(auth only)',
  ];
}
ksort($byMod);
foreach($byMod as $mod=>$items){
  $withPerm=count(array_filter($items,fn($i)=>$i['perm']!=='(auth only)'));
  echo "\n=== $mod (".count($items).", perm_mw: $withPerm) ===\n";
  foreach(array_slice($items,0,6) as $it){
    echo $it['method'].' | '.$it['uri'].' | '.basename(str_replace('\\','/',$it['action'])).' | '.$it['perm']."\n";
  }
  if(count($items)>6) echo '  ... +'.(count($items)-6)." more\n";
}
