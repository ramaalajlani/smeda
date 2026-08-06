<?php
$j = file_get_contents('C:/Users/LENOVO/.cursor/projects/c-Users-LENOVO-Desktop-back-authority-authority2-api2/agent-tools/7e74b4cf-b7c3-46e2-9d26-dcd19c817c79.txt');
$routes = json_decode($j, true);
$seeder = file_get_contents('C:/Users/LENOVO/Desktop/back_authority/authority2/api2/database/seeders/RolePermissionSeeder.php');
preg_match_all("/'([a-z0-9_.]+)'/", $seeder, $m);
$seedPerms = array_unique(array_filter($m[1], fn($p)=>str_contains($p,'.') || str_starts_with($p,'view_') || str_starts_with($p,'manage_') || str_starts_with($p,'needs.') || str_starts_with($p,'finance.') || str_starts_with($p,'workforce.')));
$routePerms=[];
foreach($routes as $r){
  foreach($r['middleware']??[] as $mw){
    if(preg_match('/Middleware:([^|]+(?:\|[^|]+)*)/',$mw,$mm)){
      foreach(explode('|',$mm[1]) as $token){
        if(str_contains($token,'.')) $routePerms[$token]=($routePerms[$token]??0)+1;
        elseif(!in_array($token,['admin','super_admin','system_admin','general_director','deputy_general_director','deputy_director','branch_manager','branch_officer','governor','auditor','training_manager','center_user','trainer_user','trainee_user','project_owner','finance_manager','finance_officer','consultant_office','funding_partner','consultant_union_admin','central_bank_admin','incubator_manager','incubator_mentor','entrepreneur_manager','media_manager','data_entry','data_reviewer','project_services_manager','development_manager','local_development_manager','workforce_manager'])) {
          if(!str_starts_with($token,'Illuminate') && strlen($token)>3) $routePerms[$token]=($routePerms[$token]??0)+1;
        }
      }
    }
  }
}
$maybeMissing=[];
foreach(array_keys($routePerms) as $p){
  if(!str_contains($p,'.')) continue;
  if(!str_contains($seeder, "'$p'")) $maybeMissing[$p]=$routePerms[$p];
}
echo "DOT PERMS IN ROUTES NOT IN SEEDER LIST:\n";
foreach($maybeMissing as $p=>$c) echo "  $p ($c routes)\n";
