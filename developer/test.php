<?php

$cadenas1 = array(2, 3, 4, 3, 2,2,5,2,1);

function Encontrar($arr,$find) {
    $r1 = array_count_values($arr);
	
   if(isset($r1[$find])){
       return $r1[$find];
   }
   return -1;
}

echo "Numero de veces Encontrado : " .Encontrar($cadenas1,2);

?>