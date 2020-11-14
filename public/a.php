<?php
set_time_limit(0);
$n = $_GET['n'];
function fab($n){
    if($n==1 || $n==2) {
        return 1;
    }
    return fab($n-2) + fab($n-1);
}
echo fab(40);echo '</br>';
/*
for($i=1;$i<=10;$i++){
    echo fab($i);echo '</br>';
}
*/
