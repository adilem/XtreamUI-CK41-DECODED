<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2
 * @ Decoder version: 1.0.4
 * @ Release: 01/09/2021
 */

include "functions.php";
if (isset($_SESSION["hash"])) {
    header("Location: ./dashboard.php");
    exit;
}
$rAdminSettings = getAdminSettings();
if (0 < intval($rAdminSettings["login_flood"])) {
    $result = $db->query("SELECT COUNT(`id`) AS `count` FROM `login_flood` WHERE `ip` = '" . ESC(getIP()) . "' AND TIME_TO_SEC(TIMEDIFF(NOW(), `dateadded`)) <= 86400;");
    if ($result && $result->num_rows == 1 && intval($rAdminSettings["login_flood"]) <= intval($result->fetch_assoc()["count"])) {
        $_STATUS = 7;
    }
}
if (!isset($_STATUS)) {
    $rGA = new PHPGangsta_GoogleAuthenticator();
    if (isset($_POST["username"]) && isset($_POST["password"])) {
        if ($rAdminSettings["recaptcha_enable"]) {
            $rResponse = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $rAdminSettings["recaptcha_v2_secret_key"] . "&response=" . $_POST["g-recaptcha-response"]), true);
            if (!$rResponse["success"] && !in_array("invalid-input-secret", $rResponse["error-codes"])) {
                $_STATUS = 5;
            }
        }
        if (!isset($_STATUS)) {
            $rUserInfo = doLogin($_POST["username"], $_POST["password"]);
            if (isset($rUserInfo)) {
                if (isset($rAdminSettings["google_2factor"]) && $rAdminSettings["google_2factor"]) {
                    if (strlen($rUserInfo["google_2fa_sec"]) == 0) {
                        $rGA = new PHPGangsta_GoogleAuthenticator();
                        $rSecret = $rGA->createSecret();
                        $rUserInfo["google_2fa_sec"] = $rSecret;
                        $db->query("UPDATE `reg_users` SET `google_2fa_sec` = '" . ESC($rSecret) . "' WHERE `id` = " . intval($rUserInfo["id"]) . ";");
                        $rNew2F = true;
                    }
                    $rQR = $rGA->getQRCodeGoogleUrl("Xtream UI", $rUserInfo["google_2fa_sec"]);
                    $rAuth = md5($rUserInfo["password"]);
                } else {
                    if (strlen($_POST["password"]) < intval($rAdminSettings["pass_length"]) && 0 < intval($rAdminSettings["pass_length"])) {
                        $rChangePass = md5($rUserInfo["password"]);
                    } else {
                        $rPermissions = getPermissions($rUserInfo["member_group_id"]);
                        if ($rPermissions && ($rPermissions["is_admin"] || $rPermissions["is_reseller"]) && !$rPermissions["is_banned"] && $rUserInfo["status"] == 1) {
                            $db->query("UPDATE `reg_users` SET `last_login` = UNIX_TIMESTAMP(), `ip` = '" . ESC(getIP()) . "' WHERE `id` = " . intval($rUserInfo["id"]) . ";");
                            $_SESSION["hash"] = md5($rUserInfo["username"]);
                            $_SESSION["ip"] = getIP();
                            if ($rPermissions["is_admin"]) {
                                $db->query("INSERT INTO `login_users`(`owner`, `type`, `login_ip`, `date`) VALUES(" . intval($rUserInfo["id"]) . ", '<b>[UserPanel]</b> -> Admin " . $_["logged_in"] . "', '" . ESC(getIP()) . "', " . intval(time()) . ");");
                                if (0 < strlen($_POST["referrer"])) {
                                    header("Location: ." . ESC($_POST["referrer"]));
                                } else {
                                    header("Location: ./dashboard.php");
                                }
                            } else {
                                $db->query("INSERT INTO `login_users`(`owner`, `type`, `login_ip`, `date`) VALUES(" . intval($rUserInfo["id"]) . ", '<b>[UserPanel]</b> -> " . $_["logged_in"] . "', '" . ESC(getIP()) . "', " . intval(time()) . ");");
                                if (0 < strlen($_POST["referrer"])) {
                                    header("Location: ." . ESC($_POST["referrer"]));
                                } else {
                                    header("Location: ./reseller.php");
                                }
                            }
                        } else {
                            if ($rPermissions && ($rPermissions["is_admin"] || $rPermissions["is_reseller"]) && $rPermissions["is_banned"]) {
                                $_STATUS = 2;
                            } else {
                                if ($rPermissions && ($rPermissions["is_admin"] || $rPermissions["is_reseller"]) && !$rUserInfo["status"]) {
                                    $_STATUS = 3;
                                } else {
                                    $_STATUS = 4;
                                }
                            }
                        }
                    }
                }
            } else {
                if (0 < intval($rAdminSettings["login_flood"])) {
                    $db->query("INSERT INTO `login_flood`(`username`, `ip`) VALUES('" . ESC($_POST["username"]) . "', '" . ESC(getIP()) . "');");
                }
                $_STATUS = 0;
            }
        }
    } else {
        if (isset($_POST["gauth"]) && isset($_POST["hash"]) && isset($_POST["auth"]) && isset($rAdminSettings["google_2factor"]) && $rAdminSettings["google_2factor"]) {
            $rUserInfo = getRegisteredUserHash($_POST["hash"]);
            $rAuth = $_POST["auth"];
            if ($rUserInfo && $rAuth == md5($rUserInfo["password"])) {
                if ($rGA->verifyCode($rUserInfo["google_2fa_sec"], $_POST["gauth"], 2)) {
                    $rPermissions = getPermissions($rUserInfo["member_group_id"]);
                    if ($rPermissions && ($rPermissions["is_admin"] || $rPermissions["is_reseller"]) && !$rPermissions["is_banned"] && $rUserInfo["status"] == 1) {
                        $db->query("UPDATE `reg_users` SET `last_login` = UNIX_TIMESTAMP(), `ip` = '" . ESC(getIP()) . "' WHERE `id` = " . intval($rUserInfo["id"]) . ";");
                        $_SESSION["hash"] = md5($rUserInfo["username"]);
                        $_SESSION["ip"] = getIP();
                        if ($rPermissions["is_admin"]) {
                            $db->query("INSERT INTO `login_users`(`owner`, `username`, `password`, `date`, `type`) VALUES(" . intval($rUserInfo["id"]) . ", '', '', " . intval(time()) . ", '<b>[UserPanel]</b> -> Admin " . $_["logged_in"] . "');");
                            header("Location: ./dashboard.php");
                        } else {
                            $db->query("INSERT INTO `login_users`(`owner`, `username`, `password`, `date`, `type`) VALUES(" . intval($rUserInfo["id"]) . ", '', '', " . intval(time()) . ", '<b>[UserPanel]</b> -> " . $_["logged_in"] . "');");
                            header("Location: ./reseller.php");
                        }
                    } else {
                        if ($rPermissions && ($rPermissions["is_admin"] || $rPermissions["is_reseller"]) && $rPermissions["is_banned"]) {
                            $_STATUS = 2;
                        } else {
                            if ($rPermissions && ($rPermissions["is_admin"] || $rPermissions["is_reseller"]) && !$rUserInfo["status"]) {
                                $_STATUS = 3;
                            } else {
                                $_STATUS = 4;
                            }
                        }
                    }
                } else {
                    $rQR = $rGA->getQRCodeGoogleUrl("Xtream UI", $rUserInfo["google_2fa_sec"]);
                    $_STATUS = 1;
                }
            } else {
                if (0 < intval($rAdminSettings["login_flood"])) {
                    $db->query("INSERT INTO `login_flood`(`username`, `ip`) VALUES('" . ESC($_POST["username"]) . "', '" . ESC(getIP()) . "');");
                }
                $_STATUS = 0;
            }
        } else {
            if (isset($_POST["newpass"]) && isset($_POST["confirm"]) && isset($_POST["hash"]) && isset($_POST["change"])) {
                $rUserInfo = getRegisteredUserHash($_POST["hash"]);
                $rChangePass = $_POST["change"];
                if ($rUserInfo && $rChangePass == md5($rUserInfo["password"])) {
                    if ($_POST["newpass"] == $_POST["confirm"] && intval($rAdminSettings["pass_length"]) <= strlen($_POST["newpass"])) {
                        $rPermissions = getPermissions($rUserInfo["member_group_id"]);
                        if ($rPermissions && ($rPermissions["is_admin"] || $rPermissions["is_reseller"]) && !$rPermissions["is_banned"] && $rUserInfo["status"] == 1) {
                            $db->query("UPDATE `reg_users` SET `last_login` = UNIX_TIMESTAMP(), `password` = '" . ESC(cryptPassword($_POST["newpass"])) . "', `ip` = '" . ESC(getIP()) . "' WHERE `id` = " . intval($rUserInfo["id"]) . ";");
                            $_SESSION["hash"] = md5($rUserInfo["username"]);
                            $_SESSION["ip"] = getIP();
                            if ($rPermissions["is_admin"]) {
                                $db->query("INSERT INTO `login_users`(`owner`, `username`, `password`, `date`, `type`) VALUES(" . intval($rUserInfo["id"]) . ", '', '', " . intval(time()) . ", '<b>[UserPanel]</b> -> Admin " . $_["logged_in"] . ");");
                                header("Location: ./dashboard.php");
                            } else {
                                $db->query("INSERT INTO `login_users`(`owner`, `username`, `password`, `date`, `type`) VALUES(" . intval($rUserInfo["id"]) . ", '', '', " . intval(time()) . ", '<b>[UserPanel]</b> -> " . $_["logged_in"] . ");");
                                header("Location: ./reseller.php");
                            }
                        } else {
                            if ($rPermissions && ($rPermissions["is_admin"] || $rPermissions["is_reseller"]) && $rPermissions["is_banned"]) {
                                $_STATUS = 2;
                            } else {
                                if ($rPermissions && ($rPermissions["is_admin"] || $rPermissions["is_reseller"]) && !$rUserInfo["status"]) {
                                    $_STATUS = 3;
                                } else {
                                    $_STATUS = 4;
                                }
                            }
                        }
                    } else {
                        $_STATUS = 6;
                    }
                } else {
                    if (0 < intval($rAdminSettings["login_flood"])) {
                        $db->query("INSERT INTO `login_flood`(`username`, `ip`) VALUES('" . ESC($_POST["username"]) . "', '" . ESC(getIP()) . "');");
                    }
                    $_STATUS = 0;
                }
            }
        }
    }
}
echo "<!DOCTYPE html>\n<html lang=\"en\">\n    <head>\n        <meta charset=\"utf-8\" />\n        <title>";
echo htmlspecialchars($rSettings["server_name"]);
echo " - ";
echo $_["login"];
echo "</title>\n        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n        <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\" />\n        <!-- App favicon -->\n        <link rel=\"shortcut icon\" href=\"assets/images/favicon.ico\">\n        <!-- App css -->\n\t\t<link href=\"assets/css/icons.css\" rel=\"stylesheet\" type=\"text/css\" />\n        ";
if ($rAdminSettings["dark_mode_login"]) {
    echo "\t\t<link href=\"assets/css/bootstrap.css\" rel=\"stylesheet\" type=\"text/css\" />\n        <link href=\"assets/css/app.css\" rel=\"stylesheet\" type=\"text/css\" />\n        ";
} else {
    echo "        <link href=\"assets/css/bootstrap.css\" rel=\"stylesheet\" type=\"text/css\" />\n        <link href=\"assets/css/app.css\" rel=\"stylesheet\" type=\"text/css\" />\n        ";
}
echo "\t\t<style>\n\t\t\t.g-recaptcha {\n\t\t\t\tdisplay: inline-block;\n\t\t\t}\n\t\t</style>\n    </head>\n    <body class=\"authentication-bg authentication-bg-pattern\">\n        <div class=\"account-pages mt-5 mb-5\">\n            <div class=\"container\">\n                <div class=\"row justify-content-center\">\n                    <div class=\"col-md-8 col-lg-6 col-xl-5\">\n                        ";
if (file_exists("./.update")) {
    echo "                        <div class=\"alert alert-danger alert-dismissible bg-danger text-white border-0 fade show\" role=\"alert\">\n                            <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>\n                            ";
    echo $_["login_message_1"];
    echo "                        </div>\n                        ";
}
if (isset($_STATUS) && $_STATUS == 0) {
    echo "                        <div class=\"alert alert-danger alert-dismissible bg-danger text-white border-0 fade show\" role=\"alert\">\n                            <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>\n                            ";
    echo $_["login_message_2"];
    echo "                        </div>\n                        ";
} else {
    if (isset($_STATUS) && $_STATUS == 1) {
        echo "                        <div class=\"alert alert-danger alert-dismissible bg-danger text-white border-0 fade show\" role=\"alert\">\n                            <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>\n                            ";
        echo $_["login_message_3"];
        echo "                        </div>\n                        ";
    } else {
        if (isset($_STATUS) && $_STATUS == 2) {
            echo "                        <div class=\"alert alert-danger alert-dismissible bg-danger text-white border-0 fade show\" role=\"alert\">\n                            <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>\n                            ";
            echo $_["login_message_4"];
            echo "                        </div>\n                        ";
        } else {
            if (isset($_STATUS) && $_STATUS == 3) {
                echo "                        <div class=\"alert alert-danger alert-dismissible bg-danger text-white border-0 fade show\" role=\"alert\">\n                            <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>\n                            ";
                echo $_["login_message_5"];
                echo "                        </div>\n                        ";
            } else {
                if (isset($_STATUS) && $_STATUS == 4) {
                    echo "                        <div class=\"alert alert-danger alert-dismissible bg-danger text-white border-0 fade show\" role=\"alert\">\n                            <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>\n                            ";
                    echo $_["login_message_6"];
                    echo "                        </div>\n\t\t\t\t\t\t";
                } else {
                    if (isset($_STATUS) && $_STATUS == 5) {
                        echo "                        <div class=\"alert alert-danger alert-dismissible bg-danger text-white border-0 fade show\" role=\"alert\">\n                            <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>\n                            ";
                        echo $_["login_message_7"];
                        echo "                        </div>\n\t\t\t\t\t\t";
                    } else {
                        if (isset($_STATUS) && $_STATUS == 6) {
                            echo "                        <div class=\"alert alert-danger alert-dismissible bg-danger text-white border-0 fade show\" role=\"alert\">\n                            <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>\n\t\t\t\t\t\t\t";
                            echo str_replace("{num}", $rAdminSettings["pass_length"], $_["login_message_8"]);
                            echo "                        </div>\n                        ";
                        }
                    }
                }
            }
        }
    }
}
echo "\t\t\t\t\t\t<br>\n                        <div class=\"card\">\n                            <div class=\"card-body\">\n                                <h4 class=\"mb-0 text-center\"> WELCOME </h4>\n\t\t\t\t\t\t\t\t<br><br>\n\t\t\t\t\t\t\t\t";
if (!isset($_STATUS) || $_STATUS != 7) {
    echo "                                <form action=\"./login.php\" method=\"POST\" data-parsley-validate=\"\" id=\"login_form\">\n                                    <input type=\"hidden\" name=\"referrer\" value=\"";
    echo ESC($_GET["referrer"]);
    echo "\" />\n                                    ";
    if (!isset($rQR) && !isset($rChangePass)) {
        echo "                                    <div class=\"form-group mb-3\" id=\"username_group\">\n                                        <label for=\"username\">";
        echo $_["username"];
        echo "</label>\n                                        <input class=\"form-control\" autocomplete=\"off\" type=\"text\" id=\"username\" name=\"username\" required=\"\" data-parsley-trigger=\"change\" placeholder=\"";
        echo $_["enter_your_username"];
        echo "\">\n                                    </div>\n                                    <div class=\"form-group mb-2\">\n                                        <label for=\"password\">";
        echo $_["password"];
        echo "</label>\n                                        <input class=\"form-control\" autocomplete=\"off\" type=\"password\" required data-parsley-trigger=\"change\" id=\"password\" name=\"password\" placeholder=\"";
        echo $_["enter_your_password"];
        echo "\">\n                                    </div>\n\t\t\t\t\t\t\t\t\t<div class=\"form-group mb-0\">\n\t\t\t\t\t\t\t\t\t\t<label for=\"show-password\" class=\"field__toggle\">\n\t\t\t\t                            <input type=\"checkbox\" id=\"show-password\" class=\"field__toggle-input\" />\n\t\t\t\t                            Show password\n\t\t\t                            </label>\n                                    </div>\n\t\t\t\t\t\t\t\t\t";
        if ($rAdminSettings["recaptcha_enable"]) {
            echo "\t\t\t\t\t\t\t\t\t<h5 class=\"text-center\">\n                                        <div class=\"g-recaptcha\" id=\"verification\" data-sitekey=\"";
            echo $rAdminSettings["recaptcha_v2_site_key"];
            echo "\"></div>\n                                    </h5>\n\t\t\t\t\t\t\t\t\t";
        }
    } else {
        if (isset($rChangePass)) {
            echo "\t\t\t\t\t\t\t\t\t<input type=\"hidden\" name=\"hash\" value=\"";
            echo md5($rUserInfo["username"]);
            echo "\" />\n                                    <input type=\"hidden\" name=\"change\" value=\"";
            echo $rChangePass;
            echo "\" />\n\t\t\t\t\t\t\t\t\t<div class=\"form-group mb-3 text-center\">\n                                        <p>";
            echo str_replace("{num}", $rAdminSettings["pass_length"], $_["login_message_9"]);
            echo "</p>\n                                    </div>\n\t\t\t\t\t\t\t\t\t<div class=\"form-group mb-3\">\n                                        <label for=\"newpass\">";
            echo $_["new_password"];
            echo "</label>\n                                        <input class=\"form-control\" autocomplete=\"off\" type=\"password\" id=\"newpass\" name=\"newpass\" required data-parsley-trigger=\"change\" placeholder=\"";
            echo $_["enter_a_new_password"];
            echo "\">\n                                    </div>\n                                    <div class=\"form-group mb-3\">\n                                        <label for=\"confirm\">";
            echo $_["confirm_password"];
            echo "</label>\n                                        <input class=\"form-control\" autocomplete=\"off\" type=\"password\" id=\"confirm\" name=\"confirm\" required data-parsley-trigger=\"change\" placeholder=\"";
            echo $_["confirm_your_password"];
            echo "\">\n                                    </div>\n\t\t\t\t\t\t\t\t\t";
        } else {
            echo "                                    <input type=\"hidden\" name=\"hash\" value=\"";
            echo md5($rUserInfo["username"]);
            echo "\" />\n                                    <input type=\"hidden\" name=\"auth\" value=\"";
            echo $rAuth;
            echo "\" />\n                                    ";
            if (isset($rNew2F)) {
                echo "                                    <div class=\"form-group mb-3 text-center\">\n                                        <p>";
                echo $_["login_message_10"];
                echo "</p>\n                                        <img src=\"";
                echo $rQR;
                echo "\">\n                                    </div>\n                                    ";
            }
            echo "                                    <div class=\"form-group mb-3\">\n                                        <label for=\"gauth\">";
            echo $_["google_authenticator_code"];
            echo "</label>\n                                        <input class=\"form-control\" autocomplete=\"off\" type=\"gauth\" required=\"\" id=\"gauth\" name=\"gauth\" placeholder=\"";
            echo $_["enter_your_auth_code"];
            echo "\">\n                                    </div>\n                                    ";
        }
    }
    echo "\t\t\t\t\t\t\t        <br>\n                                    <div class=\"form-group mb-0 text-center\">\n                                        <button class=\"btn_login btn-block btn-success\" type=\"submit\" id=\"login_button\">LOGIN</button>\n                                    </div>\n                                </form>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\t\n\t\t\t\t\t\t\t\t";
} else {
    echo "\t\t\t\t\t\t\t\t<div class=\"form-group mb-0 text-center text-danger\">\n\t\t\t\t\t\t\t\t\t<p>";
    echo $_["login_message_11"];
    echo "</p>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t";
}
echo "                            </div>\n                        </div>\n                    </div>\n                </div>\n            </div>\n        </div>\n        <script src=\"assets/js/vendor.min.js\"></script>\n        <script src=\"assets/libs/parsleyjs/parsley.min.js\"></script>\n        <script src=\"assets/js/app.min.js?rid=";
echo getID();
echo "\"></script>\n\t\t";
if ($rAdminSettings["recaptcha_enable"]) {
    echo "\t\t<script src=\"https://www.google.com/recaptcha/api.js\" async defer></script>\n\t\t";
}
echo "        <script>\n        \$(document).ready(function() {\n            if (window.location.hash.substring(0,1) == \"#\") {\n                \$(\"#username_group\").hide();\n                \$(\"#username\").val(window.location.hash.substring(1));\n                \$(\"#login_form\").attr('action', './login.php#' + window.location.hash.substring(1));\n                \$(\"#login_button\").html(\"";
echo $_["login_as"];
echo " \" + window.location.hash.substring(1).toUpperCase());\n            }\n        });\n        </script>\n\t\t<script type=\"text/javascript\">\n\t\tvar toggle = document.querySelector( \"#show-password\" );\n\t\ttoggle.addEventListener( \"click\", handleToggleClick, false );\n\n\t\tfunction handleToggleClick( event ) {\n \n\t\t\tif ( this.checked ) {\n \n\t\t\t\tconsole.warn( \"Change input 'type' to: text\" );\n\t\t\t\tpassword.type = \"text\";\n \n\t\t\t} else {\n \n\t\t\t\tconsole.warn( \"Change input 'type' to: password\" );\n\t\t\t\tpassword.type = \"password\";\n \n\t\t\t}\n \n\t\t}\n \n\t</script>\n    </body>\n</html>";

?>