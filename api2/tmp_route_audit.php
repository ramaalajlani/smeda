<?php
$j = file_get_contents('C:/Users/LENOVO/.cursor/projects/c-Users-LENOVO-Desktop-back-authority-authority2-api2/agent-tools/7e74b4cf-b7c3-46e2-9d26-dcd19c817c79.txt');
$routes = json_decode($j, true);
function methodStr($m){ return is_array($m) ? implode('|',$m) : (string)$m; }
function hasSanctum($m){ foreach((array)$m as $x){ if(stripos($x,'Authenticate:sanctum')!==false || stripos($x,'auth:sanctum')!==false) return true; } return false; }
function permMw($m){ $p=[]; foreach((array)$m as $x){ if(preg_match('/RoleMiddleware|PermissionMiddleware|RoleOrPermissionMiddleware/',$x)) $p[]=$x; } return $p; }
function shortMw($m){ return array_map(function($x){ if(preg_match('/RoleOrPermissionMiddleware:(.+)$/',$x,$mm)) return 'role_or_permission:'.$mm[1]; if(preg_match('/PermissionMiddleware:(.+)$/',$x,$mm)) return 'permission:'.$mm[1]; if(preg_match('/RoleMiddleware:(.+)$/',$x,$mm)) return 'role:'.$mm[1]; if(stripos($x,'Authenticate:sanctum')!==false) return 'auth:sanctum'; return $x; }, (array)$m); }
function module($uri){ $u=preg_replace('#^api/#','',$uri); return explode('/',$u)[0] ?: 'root'; }

$public=[]; $protected=[]; $byMod=[]; $withPerm=[]; $noPermProtected=[];
foreach($routes as $r){
  $uri=$r['uri']??'';
  $meth=methodStr($r['method']??'');
  $action=$r['action']??'';
  $mw=$r['middleware']??[];
  $sm=shortMw($mw);
  $perms=permMw($mw);
  $row=['method'=>$meth,'uri'=>$uri,'action'=>$action,'middleware'=>implode(', ',$sm),'perms'=>implode('; ', array_map(function($x){
    if(preg_match('/RoleOrPermissionMiddleware:(.+)$/',$x,$mm)) return 'role_or_permission:'.$mm[1];
    if(preg_match('/PermissionMiddleware:(.+)$/',$x,$mm)) return 'permission:'.$mm[1];
    if(preg_match('/RoleMiddleware:(.+)$/',$x,$mm)) return 'role:'.$mm[1];
    return $x;
  }, $perms))];
  if($perms) $withPerm[]=$row;
  if(hasSanctum($mw)) {
    $protected[]=$row; $m=module($uri); $byMod[$m]=($byMod[$m]??0)+1;
    if(!$perms) $noPermProtected[]=$row;
  } else $public[]=$row;
}
$outDir='C:/Users/LENOVO/.cursor/projects/c-Users-LENOVO-Desktop-back-authority-authority2-api2/agent-tools';
echo 'TOTAL: '.count($routes)."\nPUBLIC: ".count($public)."\nPROTECTED: ".count($protected)."\nWITH_PERM_MW: ".count($withPerm)."\nPROTECTED_NO_PERM_MW: ".count($noPermProtected)."\n";
arsort($byMod);
echo "MODULE_COUNTS:\n"; foreach($byMod as $k=>$v) echo "  $k: $v\n";
$keys=[]; $dups=[];
foreach($routes as $r){ $k=methodStr($r['method']).' '.$r['uri']; if(isset($keys[$k])) $dups[]=$k; $keys[$k]=1; }
echo 'DUPLICATES: '.count(array_unique($dups))."\n"; foreach(array_unique($dups) as $d) echo "  $d\n";
file_put_contents("$outDir/route-audit-public.json", json_encode($public, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
file_put_contents("$outDir/route-audit-no-perm-protected.json", json_encode($noPermProtected, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
$group=[]; foreach($protected as $row){ $group[module($row['uri'])][]=$row; }
$summary=[];
foreach($byMod as $mod=>$cnt){
  $items=$group[$mod]??[];
  $permCount=0; foreach($items as $it) if($it['perms']!=='') $permCount++;
  $summary[$mod]=['count'=>$cnt,'with_permission_mw'=>$permCount,'auth_only'=>$cnt-$permCount];
}
file_put_contents("$outDir/route-module-summary.json", json_encode($summary, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo "\nPUBLIC_ROUTES:\n";
foreach($public as $p) echo $p['method']."\t".$p['uri']."\t".$p['action']."\t".$p['middleware']."\n";
