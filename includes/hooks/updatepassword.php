<?php
 

add_hook('AdminAreaPage', 1, function($vars) {
    //Inject javascript code in the users tab page since it is not supported by the WHMCS hooks.
    try{

        if( $vars['helplink'] == "Clients:Users_Tab" ){
            $userId = isset($_REQUEST['user-id'])?$_REQUEST['user-id']:$_REQUEST['userid'];
            $client = \WHMCS\Database\Capsule::table('tblusers')->where('id',$userId)->first();
            $js = '
            <script>

                window.addEventListener("load", function(){
                    $("#userTable .dropdown-menu").each(function( index ) {
                        var userId = $(this).find("li a").data("user-id");
                        $(this).append("<li><a href=\"#\" data-toggle=\"modal\" data-user-id=\"" + userId + "\"  onClick=\"updateId(" + userId + ")\" data-target=\"#change-password\">Update password</a></li>"); 
                    });


                    var myModal = \'<div class="modal" tabindex="-1" role="dialog" id="change-password" style="display:none;"><div class="modal-dialog" role="document">                    <div class="modal-content">                      <form  action="index.php?rp=' . $_GET['rp'] . '">                        <input type="hidden" id="user-id" name="user-id" value="' . $_REQUEST['userid']. '">                        <input type="hidden" name="update-password" value="1">                        <div class="modal-header">                            <h5 class="modal-title">Update password</h5>                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">                                <span aria-hidden="true">&times;</span>                            </button>                        </div>                        <div class="modal-body">                            <div class="alert alert-danger">Warning, the password will be stored in MD5 format!</div>                            <div>                                <label>New password</label>                                <input id="new-password" class="form-control" type="text" required name="new-password">                            </div>                        </div>                        <div class="modal-footer">                            <button type="button" onClick="submitPassword();" class="btn btn-danger">Update password</button>                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>                        </div>                     </form>                   </div>                </div></div>\';

                    $(document.body).append(myModal);

                },false);

                function updateId(userId){
                    $("#user-id").val(userId);
                }

                function submitPassword(){
                    if( $("#new-password").val() != ""){
                    var userId = $("#user-id").val();
                    var newPassword = $("#new-password").val();
                    var url = "index.php?new-password=" + newPassword + "&update-password=1&user-id=" + userId + "&rp=' . $_GET["rp"] . '";
                    window.location = url;
                    }
                }

            
            </script>



            ';
            echo $js;

            if( isset( $_REQUEST['update-password'])){
                $rawSql = "UPDATE tblusers set password = MD5('" . $_REQUEST['new-password'] . "') WHERE email='{$client->email}'";
                \WHMCS\Database\Capsule::update($rawSql);
            }
        }
    }catch(Exception $e){
        logActivity($e->getMessage());
    }
});

