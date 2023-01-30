<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2
 * @ Decoder version: 1.0.4
 * @ Release: 01/09/2021
 */

include "functions.php";
if (isset($_SESSION["hash"])) {
    if (!$rPermissions["is_admin"]) {
        header("Location: ./reseller.php");
    } else {
        header("Location: ./dashboard.php");
    }
} else {
    header("Location: ./login.php");
}

?>