<?php

require("../dbconnect.php");

if ($pid) {
    $ajaxskip = ($skip) ? '&skip=1' : '';
    header("Location: ../cart.php?pid=".(int)$pid.$ajaxskip."&carttpl=ajaxcart");
    exit;
}

header("Location: ../cart.php?carttpl=ajaxcart");
exit;

?>