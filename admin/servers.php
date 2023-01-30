<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2
 * @ Decoder version: 1.0.4
 * @ Release: 01/09/2021
 */

include "session.php";
include "functions.php";
if (!$rPermissions["is_admin"] || !hasPermissions("adv", "servers")) {
    exit;
}
if ($rSettings["sidebar"]) {
    include "header_sidebar.php";
} else {
    include "header.php";
}
if ($rSettings["sidebar"]) {
    echo "        <div class=\"content-page\"><div class=\"content\"><div class=\"container-fluid\">\n        ";
} else {
    echo "        <div class=\"wrapper\"><div class=\"container-fluid\">\n        ";
}
echo "                <!-- start page title -->\n                <div class=\"row\">\n                    <div class=\"col-12\">\n                        <div class=\"page-title-box\">\n                            <div class=\"page-title-right\">\n                                <ol class=\"breadcrumb m-0\">\n                                    <li>\n                                        <a href=\"javascript:location.reload();\">\n                                            <button type=\"button\" class=\"btn btn-dark waves-effect waves-light btn-sm\">\n                                                <i class=\"mdi mdi-refresh\"></i> ";
echo $_["refresh"];
echo " \n                                            </button>\n                                        </a>\n\t\t\t\t\t\t\t\t\t\t";
if (hasPermissions("adv", "add_server")) {
    echo "                                        <a href=\"server.php\">\n                                            <button type=\"button\" class=\"btn btn-success waves-effect waves-light btn-sm\">\n                                                <i class=\"mdi mdi-plus\"></i> ";
    echo $_["add_server"];
    echo " \n                                            </button>\n                                        </a>\n                                        <a href=\"install_server.php\">\n                                            <button type=\"button\" class=\"btn btn-info waves-effect waves-light btn-sm\">\n                                                <i class=\"mdi mdi-creation\"></i> ";
    echo $_["install_lb"];
    echo " \n                                            </button>\n                                        </a>\n\t\t\t\t\t\t\t\t\t\t";
}
echo "                                    </li>\n                                </ol>\n                            </div>\n                            <h4 class=\"page-title\">";
echo $_["servers"];
echo " </h4>\n                        </div>\n                    </div>\n                </div>     \n                <!-- end page title --> \n\n                <div class=\"row\">\n                    <div class=\"col-12\">\n                        <div class=\"card\">\n                            <div class=\"card-body\" style=\"overflow-x:auto;\">\n                                <table id=\"datatable\" class=\"table table-hover dt-responsive nowrap\">\n                                    <thead>\n                                        <tr>\n                                            <th class=\"text-center\">";
echo $_["id"];
echo "</th>\n\t\t\t\t\t\t\t\t\t\t\t<th class=\"text-center\">";
echo $_["actions"];
echo "</th>\n                                            <th class=\"text-center\">";
echo $_["server_name"];
echo "</th>\n                                            <th class=\"text-center\">";
echo $_["status"];
echo "</th>\n\t\t\t\t\t\t\t\t\t\t\t<th class=\"text-center\">Healt</th>\n                                            <th class=\"text-center\">Server Info</th>\n                                            <th class=\"text-center\">";
echo $_["domaine_name"];
echo "</th>\n                                            <th class=\"text-center\">";
echo $_["server_ip"];
echo "</th>\n\t\t\t\t\t\t\t\t\t\t\t<th class=\"text-center\">Ports </th>\n                                            <th class=\"text-center\">Client</th>\n                                            <th class=\"text-center\">";
echo $_["cpu_%"];
echo "</th>\n                                            <th class=\"text-center\">";
echo $_["mem_%"];
echo "</th>\n\t\t\t\t\t\t\t\t\t\t\t<th class=\"text-center\">In</th>\n\t\t\t\t\t\t\t\t\t\t\t<th class=\"text-center\">Out</th>\n\t\t\t\t\t\t\t\t\t\t\t<th class=\"text-center\">Network / Speed / Guaranteed</th>\n                                            \n                                        </tr>\n                                    </thead>\n                                    <tbody>\n                                        ";
foreach ($rServers as $rServer) {
    if (360 < time() - $rServer["last_check_ago"] && $rServer["can_delete"] == 1 && $rServer["status"] != 3) {
        $rServer["status"] = 2;
    }
    if (in_array($rServer["status"], [0, 1])) {
        $rServerText = ["Disabled", "Online"][$rServer["status"]];
    } else {
        if ($rServer["status"] == 2) {
            if (0 < $rServer["last_check_ago"]) {
                $rServerText = "Offline for " . intval((time() - $rServer["last_check_ago"]) / 60) . " minutes";
            } else {
                $rServerText = "Offline";
            }
        } else {
            if ($rServer["status"] == 3) {
                $rServerText = "Installing...";
            }
        }
    }
    $rLatency = $rServer["latency"] * 1000;
    if (0 < $rLatency) {
        $rLatency = $rLatency . " ms";
    } else {
        $rLatency = "- ms";
    }
    $rWatchDog = json_decode($rServer["watchdog_data"], true);
    $rServerHardware = json_decode($rServer["server_hardware"], true);
    $total_disk = $rWatchDog["total_disk_space"] / 1000000000;
    $gbtotal_used = $rServerHardware["total_used"] / 1000000;
    $gbtotal_ram = $rServerHardware["total_ram"] / 1000000;
    $gbitnetworkspeed = $rServerHardware["network_speed"] / 1000;
    $inNetworkLoad = $rWatchDog["bytes_received"] / $rServer["network_guaranteed_speed"] * 100;
    $outNetworkLoad = $rWatchDog["bytes_sent"] / $rServer["network_guaranteed_speed"] * 100;
    $healt_server_ram = $rWatchDog["total_mem_used_percent"];
    if (90 < $healt_server_ram) {
        $healt_server = "<span class='text-danger'</span>High RAM";
    } else {
        if (75 < $healt_server_ram) {
            $healt_server = "<span class='text-warning'</span>High RAM";
        } else {
            if (90 < $rWatchDog["cpu_avg"]) {
                $healt_server = "<span class='text-danger'</span>High CPU";
            } else {
                if (75 < $rWatchDog["cpu_avg"]) {
                    $healt_server = "<span class='text-warning'</span>High CPU";
                } else {
                    if (90 < $outNetworkLoad) {
                        $healt_server = "<span class='text-danger'</span>Overloading OUT bandwidth";
                    } else {
                        if (75 < $outNetworkLoad) {
                            $healt_server = "<span class='text-warning'</span>Overloading OUT bandwidth";
                        } else {
                            if (90 < $inNetworkLoad) {
                                $healt_server = "<span class='text-danger'</span>Overloading IN bandwidth";
                            } else {
                                if (75 < $inNetworkLoad) {
                                    $healt_server = "<span class='text-warning'</span>Overloading IN bandwidth";
                                } else {
                                    if ($rServer["status"] == 2) {
                                        $healt_server = "<span class='text-danger'</span>Offline";
                                    } else {
                                        if ($rServer["status"] == 3) {
                                            $healt_server = "<span class='text-info'</span>Installing...";
                                        } else {
                                            $healt_server = "<span class='text-success'</span>OK";
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    echo "                                        <tr id=\"server-";
    echo $rServer["id"];
    echo "\">\n\n                                            <td class=\"text-center\"><a href=\"./server.php?id=";
    echo $rServer["id"];
    echo "\">";
    echo $rServer["id"];
    echo "</td>\n\t\t\t\t\t\t\t\t\t\t\t\n\t\t\t\t\t\t\t\t\t\t\t<td class=\"text-center\">\n\t\t\t\t\t\t\t\t\t\t\t\t";
    if (hasPermissions("adv", "edit_server")) {
        echo "                                                <div class=\"btn-group\">\n\n\t\t\t\t\t\t\t\t\t\t\t\t    ";
        if ($rServer["can_delete"] == 0) {
            echo "                                                    <button type=\"button\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"\" data-original-title=\"";
            echo $_["advanced_functions_for_servers"];
            echo "\" class=\"btn btn-danger waves-effect waves-light btn-xs btn-functions-server\" data-ssh1=\"";
            echo $rServer["ssh_port"];
            echo "\" data-ssh2=\"";
            echo base64_decode(base64_decode($rServer["ssh_password"]));
            echo "\" data-id=\"";
            echo $rServer["id"];
            echo "\">Options</button>\n\t\t\t\t\t\t\t\t\t\t\t\t\t";
        } else {
            echo "\t\t\t\t\t\t\t\t\t\t\t\t\t<button type=\"button\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"\" data-original-title=\"";
            echo $_["advanced_functions_for_balance"];
            echo "\" class=\"btn btn-secondary waves-effect waves-light btn-xs btn-functions-balancer\" data-ssh1=\"";
            echo $rServer["ssh_port"];
            echo "\" data-ssh2=\"";
            echo base64_decode(base64_decode($rServer["ssh_password"]));
            echo "\" data-id=\"";
            echo $rServer["id"];
            echo "\">Options</button>\n\t\t\t\t\t\t\t\t\t\t\t\t\t";
        }
        echo "\t\t\t\t\t\t\t\t\t\t\t\t\n\t\t\t\t\t\t\t\t\t\t\t\t    <button type=\"button\" class=\"btn btn-dark waves-effect waves-light btn-xs\" data-toggle=\"dropdown\" aria-expanded=\"true\"><i class=\"fas fa-caret-down\" style=\"font-size:15px\"></i></button>\n\t\t\t\t\t                                <div class=\"dropdown-menu dropdown-menu-dark dropright\" aria-labelledby=\"dropdownMenu1\">\n\n\t\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"./ip_change.php\" class=\"dropdown-item\">IP change</button></a>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\n                                                    <a class=\"dropdown-item\" href=\"javascript: void(0);\" onClick=\"api(";
        echo $rServer["id"];
        echo ", 'start');\">";
        echo $_["start_all_servers"];
        echo "</i></a>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\n                                                    <a class=\"dropdown-item\" href=\"javascript: void(0);\" onClick=\"api(";
        echo $rServer["id"];
        echo ", 'stop');\">";
        echo $_["stop_all_streams"];
        echo "</a>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\n                                                    <a class=\"dropdown-item\" href=\"javascript: void(0);\" onClick=\"api(";
        echo $rServer["id"];
        echo ", 'kill');\">";
        echo $_["kill_all_connections"];
        echo "</a>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\n                                                    <a href=\"./server.php?id=";
        echo $rServer["id"];
        echo "\" class=\"dropdown-item\">";
        echo $_["edit_server"];
        echo "</a>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\n                                                    ";
        if ($rServer["can_delete"] == 1) {
            echo "                                                    <a class=\"dropdown-item text-danger\" href=\"javascript: void(0);\" onClick=\"api(";
            echo $rServer["id"];
            echo ", 'delete');\">";
            echo $_["delete_server"];
            echo "</a>\n                                                    ";
        } else {
            echo "                                                    ";
        }
        echo "                                                </div>\n\t\t\t\t\t\t\t\t\t\t\t\t";
    } else {
        echo "--";
    }
    echo "                                            </td>\n\t\t\t\t\t\t\t\t\t\t\t\n                                            <td><a href=\"./server.php?id=";
    echo $rServer["id"];
    echo "\">";
    echo $rServer["server_name"];
    echo "</td>\n                                            <td data-toggle=\"tooltip\" data-placement=\"top\" title=\"\" data-original-title=\"";
    echo $rServerText;
    echo "\" ><i class=\"";
    if ($rServer["status"] == 1) {
        echo "text-success";
    } else {
        if ($rServer["status"] == "3") {
            echo "text-info";
        } else {
            echo "text-danger";
        }
    }
    echo " fas fa-";
    echo ["square", "square text-success", "square text-danger", "square text-info"][$rServer["status"]];
    echo "\"></i> ";
    echo $rLatency;
    echo "</td>\n\t\t\t\t\t\t\t\t\t\t\t<td class=\"text-center\">";
    echo $healt_server;
    echo "</td>\n                                            <td>";
    echo $rServerHardware["cpu_name"];
    echo "<br>";
    echo $rWatchDog["cpu_cores"];
    echo " Cores / Load Average - ";
    echo $rWatchDog["cpu_load_average"];
    echo "<br> Mem. Usage ";
    echo intval($gbtotal_used);
    echo "G of ";
    echo intval($gbtotal_ram);
    echo "G / Disk ";
    echo intval($total_disk);
    echo "G</td>\n                                            <td>";
    echo $rServer["domain_name"];
    echo "</td>\n                                            <td>";
    echo $rServer["server_ip"];
    echo "</td>\n\t\t\t\t\t\t\t\t\t\t\t<td class=\"text-center\">";
    echo $rServer["http_broadcast_port"];
    echo "<br>";
    echo $rServer["https_broadcast_port"];
    echo "</td>\n\t\t\t\t\t\t\t\t\t\t\t";
    if (hasPermissions("adv", "live_connections")) {
        echo "                                            <td class=\"text-center\"><a href=\"./live_connections.php?server_id=";
        echo $rServer["id"];
        echo "\"><button type=\"button\" class=\"btn btn-secondary btn-xs waves-effect waves-light\">";
        echo count(getConnections($rServer["id"]));
        echo "</button></a></td>\n\t\t\t\t\t\t\t\t\t\t\t";
    } else {
        echo "\t\t\t\t\t\t\t\t\t\t\t<td class=\"text-center\">";
        echo count(getConnections($rServer["id"]));
        echo " / ";
        echo $rServer["total_clients"];
        echo "</td>\n\t\t\t\t\t\t\t\t\t\t\t";
    }
    echo "                                            <td class=\"text-center\"><button class=\"btn btn-pink waves-effect waves-light btn-xs\">";
    echo intval($rWatchDog["cpu_avg"]);
    echo "%</a></td></button>\n                                            <td class=\"text-center\"><button class=\"btn btn-pink waves-effect waves-light btn-xs\">";
    echo intval($rWatchDog["total_mem_used_percent"]);
    echo "%</a></td></button>\n\t\t\t\t\t\t\t\t\t\t\t<td class=\"text-center\"><button class=\"btn btn-info waves-effect waves-light btn-xs\">";
    echo intval($inNetworkLoad);
    echo "%</td></button>\n\t\t\t\t\t\t\t\t\t\t\t<td class=\"text-center\"><button class=\"btn btn-info waves-effect waves-light btn-xs\">";
    echo intval($outNetworkLoad);
    echo "%</td></button>\n\t\t\t\t\t\t\t\t\t\t\t<td>";
    echo $rServer["network_interface"];
    echo " = ";
    echo $gbitnetworkspeed;
    echo " Gbit/s - ";
    echo $rServer["network_guaranteed_speed"];
    echo " Mbit/s</td>\n                                            \n                                        </tr>\n                                        ";
}
echo "                                    </tbody>\n                                </table>\n                            </div> <!-- end card body-->\n                        </div> <!-- end card -->\n                    </div><!-- end col-->\n                </div>\n                <!-- end row-->\n            </div> <!-- end container -->\n        </div>\n        <div class=\"modal fade bs-server-modal-center\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"MainLabel\" aria-hidden=\"true\" style=\"display: none;\" data-id=\"\">\n            <div class=\"modal-dialog modal-dialog-centered\">\n                <div class=\"modal-content\">\n                    <div class=\"modal-header\">\n                        <h6 class=\"modal-title w-100 text-center\" id=\"MainLabel\">";
echo $_["advanced_functions_for_servers"];
echo "</h6>\n                        <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-hidden=\"true\">×</button>\n                    </div>\n                    <div class=\"modal-body\">\n                        <div class=\"form-group row mb-4\">\n\t\t\t\t\t\t\t<div class=\"col-md-6\">\n\t\t\t\t\t\t\t\t<input id=\"restart_server_ssh\" type=\"submit\" class=\"btn btn-info\" value=\"";
echo $_["restart_services"];
echo "\" style=\"width:100%\" /></p>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"col-md-6\">\n\t\t\t\t\t\t\t\t<input id=\"reboot_server_ssh\" type=\"submit\" class=\"btn btn-info\" value=\"";
echo $_["reboot_server"];
echo "\" style=\"width:100%\" /></p>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"form-group row mb-4\">\n\t\t\t\t\t\t\t<div class=\"col-md-6\">\n\t\t\t\t\t\t\t\t<input id=\"remake_server_ssh\" type=\"submit\" class=\"btn btn-dark\" value=\"";
echo $_["remake_server"];
echo "\" style=\"width:100%\" /></p>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"col-md-6\">\n\t\t\t\t\t\t\t    <input id=\"fremake_server_ssh\" type=\"submit\" class=\"btn btn-dark\" value=\"";
echo $_["fremake_server"];
echo "\" style=\"width:100%\" /></p>\n                            </div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<!--<div class=\"form-group row mb-4\">\n\t\t\t\t\t\t\t<div class=\"col-md-6 mx-auto\">\n\t\t\t\t\t\t\t\t<input id=\"update_release_ssh\" type=\"submit\" class=\"btn btn-danger\" value=\"";
echo $_["update_release"];
echo "\" style=\"width:100%\" /></p>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>-->\n                    </div>\n                </div><!-- /.modal-content -->\n            </div><!-- /.modal-dialog -->\n        </div><!-- /.modal -->\n\t\t<div class=\"modal fade bs-balancer-modal-center\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"BalancerLabel\" aria-hidden=\"true\" style=\"display: none;\" data-id=\"\">\n            <div class=\"modal-dialog modal-dialog-centered\">\n                <div class=\"modal-content\">\n                    <div class=\"modal-header\">\n                        <h6 class=\"modal-title w-100 text-center\" id=\"BalancerLabel\">";
echo $_["advanced_functions_for_balance"];
echo "</h6>\n                        <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-hidden=\"true\">×</button>\n                    </div>\n                    <div class=\"modal-body\">\n\t\t\t\t\t    <div class=\"form-group row mb-4\">\n\t\t\t\t\t\t\t<div class=\"col-md-6\">\n\t\t\t\t\t\t\t\t<input id=\"restart_balancer_ssh\" type=\"submit\" class=\"btn btn-info\" value=\"";
echo $_["restart_services"];
echo "\" style=\"width:100%\" /></p>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"col-md-6\">\n\t\t\t\t\t\t\t\t<input id=\"reboot_balancer_ssh\" type=\"submit\" class=\"btn btn-info\" value=\"";
echo $_["reboot_server"];
echo "\" style=\"width:100%\" /></p>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t    <div class=\"form-group row mb-4\">\n\t\t\t\t\t\t\t<div class=\"col-md-6\">\n\t\t\t\t\t\t\t\t<input id=\"remake_balancer_ssh\" type=\"submit\" class=\"btn btn-secondary\" value=\"";
echo $_["remake_balancer"];
echo "\" style=\"width:100%\" /></p>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"col-md-6\">\n\t\t\t\t\t\t\t    <input id=\"fremake_balancer_ssh\" type=\"submit\" class=\"btn btn-secondary\" value=\"";
echo $_["fremake_balancer"];
echo "\" style=\"width:100%\" /></p>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"form-group row mb-4\">\n\t\t\t\t\t\t\t<div class=\"col-md-6\">\n\t\t\t\t\t\t\t\t<input id=\"update_geolite_ssh\" type=\"submit\" class=\"btn btn-secondary\" value=\"";
echo $_["update_geolite"];
echo "\" style=\"width:100%\" /></p>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"col-md-6\">\n\t\t\t\t\t\t\t\t<input id=\"update_youtube_ssh\" type=\"submit\" class=\"btn btn-secondary\" value=\"";
echo $_["update_youtube"];
echo "\" style=\"width:100%\" /></p>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n                </div><!-- /.modal-content -->\n            </div><!-- /.modal-dialog -->\n        </div><!-- /.modal -->\n        <!-- end wrapper -->\n        ";
if ($rSettings["sidebar"]) {
    echo "</div>";
}
echo "        <!-- Footer Start -->\n        <footer class=\"footer\">\n            <div class=\"container-fluid\">\n                <div class=\"row\">\n                    <div class=\"col-md-12 copyright text-center\">";
echo getFooter();
echo "</div>\n                </div>\n            </div>\n        </footer>\n        <!-- end Footer -->\n\n        <script src=\"assets/js/vendor.min.js\"></script>\n        <script src=\"assets/libs/jquery-toast/jquery.toast.min.js\"></script>\n        <script src=\"assets/libs/datatables/jquery.dataTables.min.js\"></script>\n        <script src=\"assets/libs/datatables/dataTables.bootstrap4.js\"></script>\n        <script src=\"assets/libs/datatables/dataTables.responsive.min.js\"></script>\n        <script src=\"assets/libs/datatables/responsive.bootstrap4.min.js\"></script>\n        <script src=\"assets/libs/datatables/dataTables.buttons.min.js\"></script>\n        <script src=\"assets/libs/datatables/buttons.bootstrap4.min.js\"></script>\n        <script src=\"assets/libs/datatables/buttons.html5.min.js\"></script>\n        <script src=\"assets/libs/datatables/buttons.flash.min.js\"></script>\n        <script src=\"assets/libs/datatables/buttons.print.min.js\"></script>\n        <script src=\"assets/libs/datatables/dataTables.keyTable.min.js\"></script>\n        <script src=\"assets/libs/datatables/dataTables.select.min.js\"></script>\n\t\t<script src=\"assets/libs/jquery-knob/jquery.knob.min.js\"></script>\t\n        <script src=\"assets/js/app.min.js\"></script>\n\n        <script>\n        function api(rID, rType) {\n            if (rType == \"delete\") {\n                if (confirm('";
echo $_["are_you_sure_you_want_to_delete_this_server"];
echo "') == false) {\n                    return;\n                }\n            } else if (rType == \"kill\") {\n                if (confirm('";
echo $_["are_you_sure_you_want_to_kill_all_servers"];
echo "') == false) {\n                    return;\n                }\n            } else if (rType == \"start\") {\n                if (confirm('";
echo $_["are_you_sure_you_want_to_start_all_severs"];
echo "') == false) {\n                    return;\n                }\n            } else if (rType == \"stop\") {\n                if (confirm('";
echo $_["are_you_sure_you_want_to_stop_all_streams"];
echo "') == false) {\n                    return;\n                }\n            }\n            \$.getJSON(\"./api.php?action=server&sub=\" + rType + \"&server_id=\" + rID, function(data) {\n                if (data.result === true) {\n                    if (rType == \"delete\") {\n                        \$(\"#server-\" + rID).remove();\n                        \$.each(\$('.tooltip'), function (index, element) {\n                            \$(this).remove();\n                        });\n                        \$('[data-toggle=\"tooltip\"]').tooltip();\n                        \$.toast(\"";
echo $_["server_successfully_deleted"];
echo "\");\n                    } else if (rType == \"kill\") {\n                        \$.toast(\"";
echo $_["all_server_connections_have_been_killed"];
echo "\");\n                    } else if (rType == \"start\") {\n                        \$.toast(\"";
echo $_["all_server_connections_have_been_started"];
echo "\");\n                    } else if (rType == \"stop\") {\n                        \$.toast(\"";
echo $_["all_server_connections_have_been_stopped"];
echo "\");\n                    }\n                } else {\n                    \$.toast(\"";
echo $_["an_error_occured_while_processing_your_request"];
echo "\");\n                }\n            });\n        }\n        \$(\"#restart_server_ssh\").click(function() {\n            \$(\".bs-server-modal-center\").modal(\"hide\");\n            \$.getJSON(\"./api.php?action=restart_services&ssh_port=\" + \$(\".bs-server-modal-center\").data(\"ssh1\") + \"&server_id=\" + \$(\".bs-server-modal-center\").data(\"id\") + \"&password=\" + \$(\".bs-server-modal-center\").data(\"ssh2\"), function(data) {\n                if (data.result === true) {\n                    \$.toast(\"";
echo $_["services_will_be_restarted_shortly"];
echo "\");\n                } else {\n                    \$.toast(\"";
echo $_["an_error_occured_while_processing_your_request"];
echo "\");\n                }\n                \$(\".bs-server-modal-center\").data(\"id\", \"\");\n\t\t\t\t\$(\".bs-server-modal-center\").data(\"ssh1\", \"\");\n\t\t\t\t\$(\".bs-server-modal-center\").data(\"ssh2\", \"\");\n\n            });\n        });\n\t\t\$(\"#reboot_server_ssh\").click(function() {\n            \$(\".bs-server-modal-center\").modal(\"hide\");\n            \$.getJSON(\"./api.php?action=reboot_server&ssh_port=\" + \$(\".bs-server-modal-center\").data(\"ssh1\") + \"&server_id=\" + \$(\".bs-server-modal-center\").data(\"id\") + \"&password=\" + \$(\".bs-server-modal-center\").data(\"ssh2\"), function(data) {\n                if (data.result === true) {\n                    \$.toast(\"";
echo $_["server_will_be_restarted_shortly"];
echo "\");\n                } else {\n                    \$.toast(\"";
echo $_["an_error_occured_while_processing_your_request"];
echo "\");\n                }\n                \$(\".bs-server-modal-center\").data(\"id\", \"\");\n\t\t\t\t\$(\".bs-server-modal-center\").data(\"ssh1\", \"\");\n\t\t\t\t\$(\".bs-server-modal-center\").data(\"ssh2\", \"\");\n\n            });\n        });\n\t\t\$(\"#remake_server_ssh\").click(function() {\n            \$(\".bs-server-modal-center\").modal(\"hide\");\n            \$.getJSON(\"./api.php?action=remake_server&ssh_port=\" + \$(\".bs-server-modal-center\").data(\"ssh1\") + \"&server_id=\" + \$(\".bs-server-modal-center\").data(\"id\") + \"&password=\" + \$(\".bs-server-modal-center\").data(\"ssh2\"), function(data) {\n                if (data.result === true) {\n                    \$.toast(\"main will be remaked shortly.\");\n                } else {\n                    \$.toast(\"An error occured while processing your request.\");\n                }\n                \$(\".bs-server-modal-center\").data(\"id\", \"\");\n\t\t\t\t\$(\".bs-server-modal-center\").data(\"ssh1\", \"\");\n\t\t\t\t\$(\".bs-server-modal-center\").data(\"ssh2\", \"\");\n\n            });\n        });\n\t\t\$(\"#fremake_server_ssh\").click(function() {\n            \$(\".bs-server-modal-center\").modal(\"hide\");\n            \$.getJSON(\"./api.php?action=fremake_server&ssh_port=\" + \$(\".bs-server-modal-center\").data(\"ssh1\") + \"&server_id=\" + \$(\".bs-server-modal-center\").data(\"id\") + \"&password=\" + \$(\".bs-server-modal-center\").data(\"ssh2\"), function(data) {\n                if (data.result === true) {\n                    \$.toast(\"Server will be full remaked shortly.\");\n                } else {\n                    \$.toast(\"An error occured while processing your request.\");\n                }\n                \$(\".bs-server-modal-center\").data(\"id\", \"\");\n\t\t\t\t\$(\".bs-server-modal-center\").data(\"ssh1\", \"\");\n\t\t\t\t\$(\".bs-server-modal-center\").data(\"ssh2\", \"\");\n\n            });\n        });\n\t\t\$(\"#update_release_ssh\").click(function() {\n            \$(\".bs-server-modal-center\").modal(\"hide\");\n            \$.getJSON(\"./api.php?action=update_release&ssh_port=\" + \$(\".bs-server-modal-center\").data(\"ssh1\") + \"&server_id=\" + \$(\".bs-server-modal-center\").data(\"id\") + \"&password=\" + \$(\".bs-server-modal-center\").data(\"ssh2\"), function(data) {\n                if (data.result === true) {\n                    \$.toast(\"Release will be updated shortly.\");\n                } else {\n                    \$.toast(\"An error occured while processing your request.\");\n                }\t\t\t\t\t \n                \$(\".bs-server-modal-center\").data(\"id\", \"\");\n\t\t\t\t\$(\".bs-server-modal-center\").data(\"ssh1\", \"\");\n\t\t\t\t\$(\".bs-server-modal-center\").data(\"ssh2\", \"\");\n\n            });\n        });\n\t\t\$(\"#restart_balancer_ssh\").click(function() {\n            \$(\".bs-balancer-modal-center\").modal(\"hide\");\n            \$.getJSON(\"./api.php?action=restart_services&ssh_port=\" + \$(\".bs-balancer-modal-center\").data(\"ssh1\") + \"&server_id=\" + \$(\".bs-balancer-modal-center\").data(\"id\") + \"&password=\" + \$(\".bs-balancer-modal-center\").data(\"ssh2\"), function(data) {\n                if (data.result === true) {\n                    \$.toast(\"";
echo $_["services_will_be_restarted_shortly"];
echo "\");\n                } else {\n                    \$.toast(\"";
echo $_["an_error_occured_while_processing_your_request"];
echo "\");\n                }\n                \$(\".bs-balancer-modal-center\").data(\"id\", \"\");\n\t\t\t\t\$(\".bs-balancer-modal-center\").data(\"ssh1\", \"\");\n\t\t\t\t\$(\".bs-balancer-modal-center\").data(\"ssh2\", \"\");\n\n            });\n        });\n\t\t\$(\"#reboot_balancer_ssh\").click(function() {\n            \$(\".bs-balancer-modal-center\").modal(\"hide\");\n            \$.getJSON(\"./api.php?action=reboot_server&ssh_port=\" + \$(\".bs-balancer-modal-center\").data(\"ssh1\") + \"&server_id=\" + \$(\".bs-balancer-modal-center\").data(\"id\") + \"&password=\" + \$(\".bs-balancer-modal-center\").data(\"ssh2\"), function(data) {\n                if (data.result === true) {\n                    \$.toast(\"";
echo $_["server_will_be_restarted_shortly"];
echo "\");\n                } else {\n                    \$.toast(\"";
echo $_["an_error_occured_while_processing_your_request"];
echo "\");\n                }\n                \$(\".bs-balancer-modal-center\").data(\"id\", \"\");\n\t\t\t\t\$(\".bs-balancer-modal-center\").data(\"ssh1\", \"\");\n\t\t\t\t\$(\".bs-balancer-modal-center\").data(\"ssh2\", \"\");\n\n            });\n        });\n\t\t\$(\"#remake_balancer_ssh\").click(function() {\n            \$(\".bs-balancer-modal-center\").modal(\"hide\");\n            \$.getJSON(\"./api.php?action=remake_balancer&ssh_port=\" + \$(\".bs-balancer-modal-center\").data(\"ssh1\") + \"&server_id=\" + \$(\".bs-balancer-modal-center\").data(\"id\") + \"&password=\" + \$(\".bs-balancer-modal-center\").data(\"ssh2\"), function(data) {\n                if (data.result === true) {\n                    \$.toast(\"balancer will be remaked shortly.\");\n                } else {\n                    \$.toast(\"An error occured while processing your request.\");\n                }\n                \$(\".bs-balancer-modal-center\").data(\"id\", \"\");\n\t\t\t\t\$(\".bs-balancer-modal-center\").data(\"ssh1\", \"\");\n\t\t\t\t\$(\".bs-balancer-modal-center\").data(\"ssh2\", \"\");\n\n            });\n        });\n\t\t\$(\"#fremake_balancer_ssh\").click(function() {\n            \$(\".bs-balancer-modal-center\").modal(\"hide\");\n            \$.getJSON(\"./api.php?action=fremake_balancer&ssh_port=\" + \$(\".bs-balancer-modal-center\").data(\"ssh1\") + \"&server_id=\" + \$(\".bs-balancer-modal-center\").data(\"id\") + \"&password=\" + \$(\".bs-balancer-modal-center\").data(\"ssh2\"), function(data) {\n                if (data.result === true) {\n                    \$.toast(\"Balancer will be full remaked shortly.\");\n                } else {\n                    \$.toast(\"An error occured while processing your request.\");\n                }\n                \$(\".bs-balancer-modal-center\").data(\"id\", \"\");\n\t\t\t\t\$(\".bs-balancer-modal-center\").data(\"ssh1\", \"\");\n\t\t\t\t\$(\".bs-balancer-modal-center\").data(\"ssh2\", \"\");\n\n            });\n        });\n\t\t\$(\"#update_geolite_ssh\").click(function() {\n            \$(\".bs-balancer-modal-center\").modal(\"hide\");\n            \$.getJSON(\"./api.php?action=update_geolite&ssh_port=\" + \$(\".bs-balancer-modal-center\").data(\"ssh1\") + \"&server_id=\" + \$(\".bs-balancer-modal-center\").data(\"id\") + \"&password=\" + \$(\".bs-balancer-modal-center\").data(\"ssh2\"), function(data) {\n                if (data.result === true) {\n                    \$.toast(\"Balancer will be update Geolite shortly.\");\n                } else {\n                    \$.toast(\"An error occured while processing your request.\");\n                }\n                \$(\".bs-balancer-modal-center\").data(\"id\", \"\");\n\t\t\t\t\$(\".bs-balancer-modal-center\").data(\"ssh1\", \"\");\n\t\t\t\t\$(\".bs-balancer-modal-center\").data(\"ssh2\", \"\");\n\n            });\n        });\n\t\t\$(\"#update_youtube_ssh\").click(function() {\n            \$(\".bs-balancer-modal-center\").modal(\"hide\");\n            \$.getJSON(\"./api.php?action=update_youtube&ssh_port=\" + \$(\".bs-balancer-modal-center\").data(\"ssh1\") + \"&server_id=\" + \$(\".bs-balancer-modal-center\").data(\"id\") + \"&password=\" + \$(\".bs-balancer-modal-center\").data(\"ssh2\"), function(data) {\n                if (data.result === true) {\n                    \$.toast(\"Balancer will be update Youtube-dl shortly.\");\n                } else {\n                    \$.toast(\"An error occured while processing your request.\");\n                }\n                \$(\".bs-balancer-modal-center\").data(\"id\", \"\");\n\t\t\t\t\$(\".bs-balancer-modal-center\").data(\"ssh1\", \"\");\n\t\t\t\t\$(\".bs-balancer-modal-center\").data(\"ssh2\", \"\");\n\n            });\n        });\n        \$(\".btn-functions-server\").click(function() {\n            \$(\".bs-server-modal-center\").data(\"id\", \$(this).data(\"id\"));\n\t\t\t\$(\".bs-server-modal-center\").data(\"ssh1\", \$(this).data(\"ssh1\"));\n\t\t\t\$(\".bs-server-modal-center\").data(\"ssh2\", \$(this).data(\"ssh2\"));\n            \$(\".bs-server-modal-center\").modal(\"show\");\n        });\n\t\t\$(\".btn-functions-balancer\").click(function() {\n            \$(\".bs-balancer-modal-center\").data(\"id\", \$(this).data(\"id\"));\n\t\t\t\$(\".bs-balancer-modal-center\").data(\"ssh1\", \$(this).data(\"ssh1\"));\n\t\t\t\$(\".bs-balancer-modal-center\").data(\"ssh2\", \$(this).data(\"ssh2\"));\n            \$(\".bs-balancer-modal-center\").modal(\"show\");\n        });\n        \$(document).ready(function() {\n            \$(\"#datatable\").DataTable({\n                language: {\n                    paginate: {\n                        previous: \"<i class='mdi mdi-chevron-left'>\",\n                        next: \"<i class='mdi mdi-chevron-right'>\"\n                    }\n                },\n                drawCallback: function() {\n                    \$(\".dataTables_paginate > .pagination\").addClass(\"pagination\");\n                },\n                pageLength: 50,\n                lengthMenu: [10, 25, 50, 100, 250],\n                responsive: false,\n\t\t\t\tcolumnDefs: [\n                    ";
if ($rPermissions["is_admin"]) {
    echo "                    {\"orderable\": false, \"targets\": [1,5,8,10,11,12,13,14]}\n                    ";
} else {
    echo "                    {\"className\": \"dt-center\", \"targets\": []}\n                    ";
}
echo "                ],\n\t\t\t\tstateSave: true\n\t\t\t\t\n\t\t\t\t});\n            \$(\"#datatable\").css(\"width\", \"100%\");\n        });\t\t\t\t  \n        </script>\n    </body>\n</html>";

?>