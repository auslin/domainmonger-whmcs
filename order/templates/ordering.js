var loadinghtml = '';
var displaynum = 1;

function loadproducts(gid,pid) {
    jQuery("#productslist").html(loadinghtml);
    jQuery.post("order/index.php", { a: "getproducts", gid: gid },
    function(data){
        jQuery("#productconfig1").hide();
        jQuery("#productconfig2").hide();
        jQuery("#productslist").html(data);
        jQuery("#productslist").slideDown();
        if (pid) {
            jQuery("#pid"+pid).attr('checked', true);
            loadproductconfig(pid);
        }
    });
}

function loadproductconfig(pid) {
    if (pid) var displaynum = 1; else var displaynum = 2;
    jQuery("#productconfig"+displaynum).html(loadinghtml);
    jQuery.post("order/index.php", 'a=getproduct&displaynum='+displaynum+'&billingcycle='+jQuery("#billingcycle").val()+'&'+jQuery("#orderfrm").serialize(),
    function(data){
        jQuery("#productconfig2").hide();
        jQuery("#productconfig"+displaynum).html(data);
        jQuery("#productconfig"+displaynum).slideDown();
        jQuery("#checkouterrormsg").slideUp();
    });
    recalctotals();
}

function validatedomain() {
    jQuery("#domainresults").html(loadinghtml);
    jQuery("#productconfig2").slideUp();
    jQuery.post("order/index.php", { a: "getdomainoptions", domain: jQuery("#domain").val() },
    function(data){
        jQuery("#domainresults").html(data);
        jQuery("#domainresults").slideDown();
    });
}

function cyclechange() {
    jQuery.post("order/index.php", 'a=getproduct&'+jQuery("#orderfrm").serialize(),
    function(data){
        if (jQuery("#domain").val()) jQuery("#productconfig2").html(data); else jQuery("#productconfig1").html(data);
    });
    recalctotals();
}

function recalctotals() {
    jQuery("#loader").show();
    jQuery.post("order/index.php", 'a=cartsummary&'+jQuery("#orderfrm").serialize(),
    function(data){
        jQuery("#cartsummary").html(data);
        jQuery("#loader").hide();
    });
}

function signupnew() {
    jQuery("#newsignup").slideDown();
    jQuery("#existinglogin").slideUp();
    jQuery("#loginemail").rules("remove");
    jQuery("#loginpw").rules("remove");
    jQuery("#firstname").rules("add","required");
    jQuery("#lastname").rules("add","required");
    jQuery("#email").rules("add","required");
    jQuery("#email").rules("add","email");
    jQuery("#address1").rules("add","required");
    jQuery("#city").rules("add","required");
    jQuery("#state").rules("add","required");
    jQuery("#postcode").rules("add","required");
    jQuery("#phonenumber").rules("add","required");
    jQuery("#phonenumber").rules("add","phonenumber");
    jQuery("#password1").rules("add","required");
    jQuery("#password2").rules("add",{
     required: true,
     equalTo: "#password1"
    });
}

function signupexisting() {
    jQuery("#existinglogin").slideDown();
    jQuery("#newsignup").slideUp();
    jQuery("#firstname").rules("remove");
    jQuery("#lastname").rules("remove");
    jQuery("#email").rules("remove");
    jQuery("#address1").rules("remove");
    jQuery("#city").rules("remove");
    jQuery("#state").rules("remove");
    jQuery("#postcode").rules("remove");
    jQuery("#phonenumber").rules("remove");
    jQuery("#password1").rules("remove");
    jQuery("#password2").rules("remove");
    jQuery("#loginemail").rules("add",{
     required: true,
     email: true
    });
    jQuery("#loginemail").rules("add","email");
    jQuery("#loginpw").rules("add","required");
}

function currencychange() {
    jQuery("#loader").show();
    jQuery.post("order/index.php", 'a=cartsummary&currency='+jQuery("#currency").val()+'&'+jQuery("#orderfrm").serialize(),
    function(data){
        jQuery("#cartsummary").html(data);
        jQuery("#loader").hide();
    });
}

function applypromo() {
    jQuery.post("order/index.php", { a: "applypromo", promocode: jQuery("#promocode").val() },
    function(data){
        if (data) alert(data);
        else recalctotals();
    });
}

function removepromo() {
    jQuery.post("order/index.php", { a: "removepromo", promocode: jQuery("#promocode").val() },
    function(data){
        recalctotals();
    });
}

function selectbox(id) {
    jQuery("#"+id).attr('checked', true);
}

function toggleaccepttos() {
    if (jQuery("#accepttos").is(":checked")) jQuery("#checkoutbtn").removeAttr("disabled");
    else jQuery("#checkoutbtn").attr("disabled","disabled");
}

function checkoutvalidate() {
    jQuery("#checkoutbtn").hide();
    jQuery("#checkoutloading").show();
    jQuery.post("order/index.php", 'a=validatecheckout&'+jQuery("#orderfrm").serialize(),
    function(data){
        if (data) {
            jQuery("#checkouterrormsg").html(data);
            jQuery("#checkouterrormsg").slideDown();
            jQuery('html, body').animate({scrollTop: jQuery("#checkouterrormsg").offset().top-10}, 1000);
        } else {
            document.orderfrm.submit();
        }
    });
    jQuery("#checkoutbtn").show();
    jQuery("#checkoutloading").hide();
}