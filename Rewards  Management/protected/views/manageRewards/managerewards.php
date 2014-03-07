<?php 
    /**
     * @Description: View for Manage Rewards Tab.
     * @Author: aqdepliyan
     * @DateCreated: 2013-11-19
     */
    $this->pageTitle = Yii::app()->name . ' - Manage Rewards'; 
?>

<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/tinymce/tiny_mce.js" ></script>
<script type="text/javascript">
tinyMCE.init({
        // General options
        mode : "exact",
        elements : "editabout",
        theme : "advanced",
        skin : "o2k7",
        plugins : "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

        // Theme options
        theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,fontselect,fontsizeselect",
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,image,code,|,forecolor,backcolor",
        theme_advanced_buttons3 : "tablecontrols,|,removeformat",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_resizing : true,

        // Example content CSS (should be your site CSS)
//        content_css : "css/example.css",

        // Drop lists for link/image/media/template dialogs
        template_external_list_url : "js/template_list.js",
        external_link_list_url : "js/link_list.js",
        external_image_list_url : "js/image_list.js",
        media_external_list_url : "js/media_list.js"
});
tinyMCE.init({
        // General options
        mode : "exact",
        elements : "editaboutreadonly",
        theme : "advanced",
        skin : "o2k7",
        readonly: true,
        plugins : "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

        // Theme options
        theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,fontselect,fontsizeselect",
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,image,code,|,forecolor,backcolor",
        theme_advanced_buttons3 : "tablecontrols,|,removeformat",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_resizing : true,

        // Example content CSS (should be your site CSS)
//        content_css : "css/example.css",

        // Drop lists for link/image/media/template dialogs
        template_external_list_url : "js/template_list.js",
        external_link_list_url : "js/link_list.js",
        external_image_list_url : "js/image_list.js",
        media_external_list_url : "js/media_list.js"
});
tinyMCE.init({
        // General options
        mode : "exact",
        elements : "addabout",
        theme : "advanced",
        skin : "o2k7",
        plugins : "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

        // Theme options
        theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,fontselect,fontsizeselect",
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,image,code,|,forecolor,backcolor",
        theme_advanced_buttons3 : "tablecontrols,|,removeformat",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_resizing : true,

        // Example content CSS (should be your site CSS)
//        content_css : "css/example.css",

        // Drop lists for link/image/media/template dialogs
        template_external_list_url : "js/template_list.js",
        external_link_list_url : "js/link_list.js",
        external_image_list_url : "js/image_list.js",
        media_external_list_url : "js/media_list.js"
});
tinyMCE.init({
        // General options
        mode : "exact",
        elements : "editterms",
        theme : "advanced",
        skin : "o2k7",
        plugins : "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

        // Theme options
        theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,fontselect,fontsizeselect",
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,image,code,|,forecolor,backcolor",
        theme_advanced_buttons3 : "tablecontrols,|,removeformat",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_resizing : true,

        // Example content CSS (should be your site CSS)
//        content_css : "css/example.css",

        // Drop lists for link/image/media/template dialogs
        template_external_list_url : "js/template_list.js",
        external_link_list_url : "js/link_list.js",
        external_image_list_url : "js/image_list.js",
        media_external_list_url : "js/media_list.js"
});
tinyMCE.init({
        // General options
        mode : "exact",
        elements : "edittermsreadonly",
        theme : "advanced",
        skin : "o2k7",
        readonly: true,
        plugins : "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

        // Theme options
        theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,fontselect,fontsizeselect",
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,image,code,|,forecolor,backcolor",
        theme_advanced_buttons3 : "tablecontrols,|,removeformat",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_resizing : true,

        // Example content CSS (should be your site CSS)
//        content_css : "css/example.css",

        // Drop lists for link/image/media/template dialogs
        template_external_list_url : "js/template_list.js",
        external_link_list_url : "js/link_list.js",
        external_image_list_url : "js/image_list.js",
        media_external_list_url : "js/media_list.js"
});
tinyMCE.init({
        // General options
        mode : "exact",
        elements : "addterms",
        theme : "advanced",
        skin : "o2k7",
        plugins : "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

        // Theme options
        theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,fontselect,fontsizeselect",
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,image,code,|,forecolor,backcolor",
        theme_advanced_buttons3 : "tablecontrols,|,removeformat",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_resizing : true,

        // Example content CSS (should be your site CSS)
//        content_css : "css/example.css",

        // Drop lists for link/image/media/template dialogs
        template_external_list_url : "js/template_list.js",
        external_link_list_url : "js/link_list.js",
        external_image_list_url : "js/image_list.js",
        media_external_list_url : "js/media_list.js"
});
</script>
<script type="text/javascript">
    
    function trimword(word) {
            word = word.replace(/(^\s*)|(\s*$)/gi,"");
            word = word.replace(/[ ]{2,}/gi," "); 
            word = word.replace(/\n /,"\n"); 
            return word;
      }
    
    function validateinputs(form, part, rewardid) {
        if(form == 1){                                                  //Edit Reward Form
            if(part == 1) {                                             //Primary Details
                var partner = $("#editpartner").val();
                var rewarditem = $("#editrewarditem").val();
                var category = $("#editcategory").val();
                var points = $("#editpoints").val();
                var eligibility = $("#editeligibility").val();
                var status = $("#editstatus").val();
                var subtext = $("#editsubtext").val();
                var fromdate = $("#from_date").val();
                var todate = $("#to_date").val();
                var draw_date = $("#editdrawdate").val();
                var date1 = new Date($("#from_date").val());
                var date2 = new Date($("#to_date").val());
                var date0 = new Date($("#editdrawdate").val());
                var datefrom = date1.getTime();
                var dateto = date2.getTime();
                var datedraw = date0.getTime();

                if(rewardid == 2){
                    if(rewarditem == "" || points == "" || eligibility == "" || status == "" || fromdate == "" || todate == "" || subtext == "" || draw_date == "") {
                        var message = "Please fill up all fields.";
                        return message;
                    } else if (/^[a-zA-Z0-9- ]*$/.test(rewarditem) === false) {
                        var message = "Special character/s is/are not allowed in Reward Item";
                        return message;
                    } else if (/^[a-zA-Z0-9 \,\. ]*$/.test(subtext) === false) {
                        var message = "Special character/s is/are not allowed in Subtext";
                        return message;
                    } else if(datefrom > dateto){
                        var message = "Invalid Date Range.";
                        return message;
                    } else if(dateto > datedraw){
                        var message = "Invalid Draw Date.";
                        return message;
                    } else if(points == "0" || points == 0)  {
                        var message = "Zero (0) is not a valid reward points.";
                        return message;
                    } else  {
                        $("#editrewarditem").val(trimword(rewarditem));
                        $("#editsubtext").val(trimword(subtext));
                        $("#editpoints").val(trimword(points));
                        return true;
                    }
                } else {
                    if(partner == "" || rewarditem == "" || category == "" || points == "" || eligibility == "" || status == "" || fromdate == "" || todate == "" || subtext == "") {
                        var message = "Please fill up all fields.";
                        return message;
                    } else if (/^[a-zA-Z0-9- ]*$/.test(rewarditem) === false) {
                        var message = "Special character/s is/are not allowed in Reward Item";
                        return message;
                    } else if (/^[a-zA-Z0-9 \,\. ]*$/.test(subtext) === false) {
                        var message = "Special character/s is/are not allowed in Subtext";
                        return message;
                    } else if(datefrom > dateto){
                        var message = "Invalid Date Range.";
                        return message;
                    } else if(points == "0" || points == 0)  {
                        var message = "Zero (0) is not a valid reward points.";
                        return message;
                    } else  {
                        $("#editrewarditem").val(trimword(rewarditem));
                        $("#editsubtext").val(trimword(subtext));
                        $("#editpoints").val(trimword(points));
                        return true;
                    }
                }
            } else if(part == 2){                                   //About the Reward (part 1)
                var thblimited =$('#thblimitedframe').contents().find('#thbsubmit_limited').attr('ImageName');
                var thboutofstock = $('#thboutofstockframe').contents().find('#thbsubmit_outofstock').attr('ImageName');
                var ecoupon = $('#ecouponframe').contents().find('#ecoupon_submit').attr('ImageName');
                var lmlimited = $('#lmlimitedframe').contents().find('#lmsubmit_limited').attr('ImageName');
                var lmoutofstock = $('#lmoutofstockframe').contents().find('#lmsubmit_outofstock').attr('ImageName');
                var webslider = $('#websliderframe').contents().find('#webslider_submit').attr('ImageName');

                if((thblimited == "" || thblimited == undefined || thblimited == null) && 
                    (thboutofstock == "" || thboutofstock == undefined || thboutofstock == null) && 
                    (ecoupon == ""|| ecoupon == undefined || ecoupon == null) && 
                    (lmlimited == "" || lmlimited == undefined || lmlimited == null) && 
                    (lmoutofstock == "" || lmoutofstock == undefined || lmoutofstock == null) && 
                    (webslider == "" || webslider == undefined || webslider == null)) {
                    var c = confirm("Are you sure no images to be uploaded for this Reward Item?");
                    if (c == true) {
                        return true;
                    } else {
                        return false;
                    }
                } else if((thblimited == "" || thblimited == undefined || thblimited == null) || 
                                    (thboutofstock == "" || thboutofstock == undefined || thboutofstock == null) || 
                                    (ecoupon == ""|| ecoupon == undefined || ecoupon == null) || 
                                    (lmlimited == "" || lmlimited == undefined || lmlimited == null) || 
                                    (lmoutofstock == "" || lmoutofstock == undefined || lmoutofstock == null) || 
                                    (webslider == "" || webslider == undefined || webslider == null)) 
                {
                    var c = confirm("Are you sure you don't want to complete all the images for this Reward Item?");
                    if (c == true) {
                        if(thblimited != "")
                            $("#thblimitedphoto").val(thblimited);
                        if(thboutofstock != "")
                            $("#thboutofstockphoto").val(thboutofstock);
                        if(ecoupon != "")
                            $("#ecouponphoto").val(ecoupon);
                        if(lmlimited != "")
                            $("#lmlimitedphoto").val(lmlimited);
                        if(lmoutofstock != "")
                            $("#lmoutofstockphoto").val(lmoutofstock);
                        if(webslider != "")
                            $("#websliderphoto").val(webslider);
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    $("#thblimitedphoto").val(thblimited);
                    $("#thboutofstockphoto").val(thboutofstock);
                    $("#ecouponphoto").val(ecoupon);
                    $("#lmlimitedphoto").val(lmlimited);
                    $("#lmoutofstockphoto").val(lmoutofstock);
                    $("#websliderphoto").val(webslider);
                    return true;
                }
                
            } else if(part == 3){                                   //About the Reward (part 2)
                var editabout = tinyMCE.get('editabout').getContent();
                if(editabout == "") {
                    var message = "Please fill up About the Reward field.";
                    return message;
                } else {
                    return true;
                }
            } else if(part == 4){                                  //Terms of the Reward
                var editterms = tinyMCE.get('editterms').getContent();
                if(editterms == "") {
                    var message = "Please fill up Terms of the Reward field.";
                    return message;
                } else {
                    var newstatus = $("#editstatus").val();
                    var currentstatus = $("#statusid").val();
                    if(currentstatus != newstatus){
                        var c2 = confirm("Are you sure you want to change the status of this Reward Item?");
                        if (c2 == true) {
                            return true;
                        } else {
                            return false;
                        }
                    } else {
                        return true;
                    }
                }
            } else if(part == 5){                                  //Edit part for Active Reward
                var newstatus = $("#editstatus").val();
                var stats = $("#hdnStatus").val();
                var comparestat = $("#editstatus option[value='"+newstatus+"']").text();
                if(comparestat == stats){
                    var message = "Status Unchanged.";
                    return message;
                } else {
                    var newstatus = $("#editstatus").val();
                    var stats = $("#hdnStatus").val();
                    if(comparestat != stats){
                        var c2 = confirm("Are you sure you want to change the status of this Reward Item?");
                        if (c2 == true) {
                            return true;
                        } else {
                            return false;
                        }
                    } else {
                        return true;
                    }
                    return true;
                }
            } else if(part == 6){                                  //Edit part for Active Reward
                var newstatus = $("#editstatus").val();
                var comparestat = $("#editstatus option[value='"+newstatus+"']").text();
                if(newstatus == ""){
                    var message = "Please Select Reward Status.";
                    return message;
                } else {
                    return true;
                }
            }
        } else if (form == 2) {                                    //Add New Reward Item Form
            if(part == 1) {                                             //Primary Details
                var partner = $("#addpartner").val();
                var rewarditem = $("#addrewarditem").val();
                var category = $("#addcategory").val();
                var points = $("#addpoints").val();
                var itemcount = $("#additemcount").val();
                var eligibility = $("#addeligibility").val();
                var status = $("#addstatus").val();
                var subtext = $("#addsubtext").val();
                var fromdate = $("#add_from_date").val();
                var todate = $("#add_to_date").val();
                var promoname = $("#addpromoname").val();
                var promocode = $("#addpromocode").val();
                var drawdate = $("#drawdate").val();
                var date3 = new Date($("#add_from_date").val());
                var date4 = new Date($("#add_to_date").val());
                var date5 = new Date($("#drawdate").val());
                var adddatefrom = date3.getTime();
                var adddateto = date4.getTime();
                var adddatedraw = date5.getTime();

                if(rewardid == 2){
                    if(rewarditem == "" || points == "" || eligibility == "" || status == "" || fromdate == "" || todate == "" || itemcount == ""
                        || promoname ==  "" || promocode == "" || drawdate == "" || subtext == "") {
                        var message = "Please fill up all fields.";
                        return message;
                    } else if (/^[a-zA-Z0-9- ]*$/.test(rewarditem) === false) {
                        var message = "Special character/s is/are not allowed in Reward Item";
                        return message;
                    } else if (/^[a-zA-Z0-9 \,\. ]*$/.test(subtext) === false) {
                        var message = "Special character/s is/are not allowed in Subtext";
                        return message;
                    } else if(adddatefrom > adddateto){
                        var message = "Invalid Date Range.";
                        return message;
                    } else if(adddateto > adddatedraw){
                        var message = "Invalid Draw Date.";
                        return message;
                    } else if(points == "0" || points == 0)  {
                        var message = "Zero (0) is not a valid reward points.";
                        return message;
                    } else if(itemcount == "0" || itemcount == 0)  {
                        var message = "Zero (0) is not a valid inventory balance.";
                        return message;
                    } else  {
                        $("#addrewarditem").val(trimword(rewarditem));
                        $("#addsubtext").val(trimword(subtext));
                        $("#addpoints").val(trimword(points));
                        return true;
                    }
                } else {
                    if(partner == "" || rewarditem == "" || category == "" || points == "" || eligibility == "" || status == "" || fromdate == "" || todate == "" || itemcount == "" || subtext == "") {
                        var message = "Please fill up all fields.";
                        return message;
                    } else if (/^[a-zA-Z0-9- ]*$/.test(rewarditem) === false) {
                        var message = "Special character/s is/are not allowed in Reward Item";
                        return message;
                    } else if (/^[a-zA-Z0-9 \,\. ]*$/.test(subtext) === false) {
                        var message = "Special character/s is/are not allowed in Subtext";
                        return message;
                    } else if(adddatefrom > adddateto){
                        var message = "Invalid Date Range.";
                        return message;
                    } else if(points == "0" || points == 0)  {
                        var message = "Zero (0) is not a valid reward points.";
                        return message;
                    } else if(itemcount == "0" || itemcount == 0)  {
                        var message = "Zero (0) is not a valid inventory balance.";
                        return message;
                    } else  {
                        $("#addrewarditem").val(trimword(rewarditem));
                        $("#addsubtext").val(trimword(subtext));
                        $("#addpoints").val(trimword(points));
                        return true;
                    }
                }
            } else if(part == 2){                                   //About the Reward (part 1)
                var thblimited =$('#addthblimitedframe').contents().find('#thbsubmit_limited').attr('ImageName');
                var thboutofstock = $('#addthboutofstockframe').contents().find('#thbsubmit_outofstock').attr('ImageName');
                var ecoupon = $('#addecouponframe').contents().find('#ecoupon_submit').attr('ImageName');
                var lmlimited = $('#addlmlimitedframe').contents().find('#lmsubmit_limited').attr('ImageName');
                var lmoutofstock = $('#addlmoutofstockframe').contents().find('#lmsubmit_outofstock').attr('ImageName');
                var webslider = $('#addwebsliderframe').contents().find('#webslider_submit').attr('ImageName');

                if((thblimited == "" || thblimited == undefined || thblimited == null) && 
                    (thboutofstock == "" || thboutofstock == undefined || thboutofstock == null) && 
                    (ecoupon == ""|| ecoupon == undefined || ecoupon == null) && 
                    (lmlimited == "" || lmlimited == undefined || lmlimited == null) && 
                    (lmoutofstock == "" || lmoutofstock == undefined || lmoutofstock == null) && 
                    (webslider == "" || webslider == undefined || webslider == null)) {
                    var c = confirm("Are you sure no images to be uploaded for this Reward Item?");
                    if (c == true) {
                        return true;
                    } else {
                        return false;
                    }
                } else if((thblimited == "" || thblimited == undefined || thblimited == null) || 
                                    (thboutofstock == "" || thboutofstock == undefined || thboutofstock == null) || 
                                    (ecoupon == ""|| ecoupon == undefined || ecoupon == null) || 
                                    (lmlimited == "" || lmlimited == undefined || lmlimited == null) || 
                                    (lmoutofstock == "" || lmoutofstock == undefined || lmoutofstock == null) || 
                                    (webslider == "" || webslider == undefined || webslider == null)) 
                {
                    var c = confirm("Are you sure you don't want to complete all the images for this Reward Item?");
                    if (c == true) {
                        if(thblimited != "")
                            $("#addthblimitedphoto").val(thblimited);
                        if(thboutofstock != "")
                            $("#addthboutofstockphoto").val(thboutofstock);
                        if(ecoupon != "")
                            $("#addecouponphoto").val(ecoupon);
                        if(lmlimited != "")
                            $("#addlmlimitedphoto").val(lmlimited);
                        if(lmoutofstock != "")
                            $("#addlmoutofstockphoto").val(lmoutofstock);
                        if(webslider != "")
                            $("#addwebsliderphoto").val(webslider);
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    $("#addthblimitedphoto").val(thblimited);
                    $("#addthboutofstockphoto").val(thboutofstock);
                    $("#addecouponphoto").val(ecoupon);
                    $("#addlmlimitedphoto").val(lmlimited);
                    $("#addlmoutofstockphoto").val(lmoutofstock);
                    $("#addwebsliderphoto").val(webslider);
                    return true;
                }
                
            } else if(part == 3){                                   //About the Reward (part 2)
                var editabout = tinyMCE.get('addabout').getContent();
                if(editabout == "") {
                    var message = "Please fill up About the Reward field.";
                    return message;
                } else {
                    return true;
                }
            } else if(part == 4){                                  //Terms of the Reward
                var editterms = tinyMCE.get('addterms').getContent();
                if(editterms == "") {
                    var message = "Please fill up Terms of the Reward field.";
                    return message;
                } else {
                    return true;
                }
            } 
        }
        
    }
    
    function reloadRewardsList(viewrewardsby,rewardtype){
        if(rewardtype == 2){
            jQuery('#rewardslist').GridUnload();
            jQuery('#rewardslist').jqGrid({
                    url:'rewardsList',
                    mtype: 'POST',
                    postData: {
                                viewrewardsby : function() {return viewrewardsby; },
                                rewardtype : function() {return rewardtype; }
                              },
                    datatype: 'JSON',
                    colNames:['Partner', 'Reward', 'Description', 'Category', 'Points Required', 'Eligibility', 'Status', 'Promo Period','Action'],
                    colModel:[
                            {name:'PartnerName',index:'ProductName',align: 'center', width: 200, hidden: true},
                            {name:'RewardName',index:'RewardName', align: 'center',width: 250},
                            {name:'Description',index:'Description', align: 'center',width: 350},
                            {name:'Category',index:'Category', align: 'center',width: 220, hidden: true},
                            {name:'Points',index:'Points', align: 'center',width: 80},
                            {name:'Eligibility',index:'Eligibility', align: 'center', width: 120, hidden: true},
                            {name:'Status',index:'Status', align: 'center', width: 120},
                            {name:'PromoPeriod',index:'PromoPeriod', align: 'center', width: 280, hidden: true},
                            {name:'Action',index:'Action', align: 'center', width: 100},
                    ],

                    rowNum:10,
                    rowList:[10,20,30],
                    rowheight: 300,
                    height: 300,
                    autowidth: true,
                    pager: '#rewardslistpager',
                    refresh: true,
                    loadonce: true,
                    viewrecords: true,
                    sortorder: 'asc',
                    onCellSelect: function(rowid, col, content, event) {  
                                                var cm = jQuery('#rewardslist').jqGrid('getGridParam', 'colModel');
                                                var column = cm[col];
                                                if(column['name'] != 'Action'){
                                                    var grid = jQuery('#rewardslist');
                                                    var sel_id = grid.jqGrid('getGridParam', 'selrow');
                                                    //var status = grid.jqGrid('getCell', sel_id, 'Status');

                                                    RewardItemID = rowid;
                                                    var showonly = "showedit";
                                                    $('#hdnRewardItemID-edit').val(RewardItemID);
                                                    getRewardDetails(RewardItemID, showonly);
                                                }
                                            },
                    caption:'Manage Rewards'
            });
            jQuery('#rewardslist').jqGrid('navGrid','#rewardslistpager',
                    { edit:false,add:false,del:false, search:false, refresh: false });
        } else {
            jQuery('#rewardslist').GridUnload();
            jQuery('#rewardslist').jqGrid({
                    url:'rewardsList',
                    mtype: 'POST',
                    postData: {
                                viewrewardsby : function() {return viewrewardsby; },
                                rewardtype : function() {return rewardtype; }
                              },
                    datatype: 'JSON',
                    colNames:['Partner', 'Reward', 'Description', 'Category', 'Points Required', 'Eligibility', 'Status', 'Promo Period','Action'],
                    colModel:[
                            {name:'PartnerName',index:'ProductName',align: 'center', width: 200},
                            {name:'RewardName',index:'RewardName', align: 'center',width: 350},
                            {name:'Description',index:'Description', align: 'center',width: 350,hidden: true},
                            {name:'Category',index:'Category', align: 'center',width: 220},
                            {name:'Points',index:'Points', align: 'center',width: 80},
                            {name:'Eligibility',index:'Eligibility', align: 'center', width: 120},
                            {name:'Status',index:'Status', align: 'center', width: 120},
                            {name:'PromoPeriod',index:'PromoPeriod', align: 'center', width: 280},
                            {name:'Action',index:'Action', align: 'center', width: 200},
                    ],

                    rowNum:10,
                    rowList:[10,20,30],
                    rowheight: 300,
                    height: 300,
                    autowidth: true,
                    pager: '#rewardslistpager',
                    refresh: true,
                    loadonce: true,
                    viewrecords: true,
                    sortorder: 'asc',
                    onCellSelect: function(rowid, col, content, event) {  
                                                var cm = jQuery('#rewardslist').jqGrid('getGridParam', 'colModel');
                                                var column = cm[col];
                                                if(column['name'] != 'Action'){
                                                    var grid = jQuery('#rewardslist');
                                                    var sel_id = grid.jqGrid('getGridParam', 'selrow');
                                                    //var status = grid.jqGrid('getCell', sel_id, 'Status');

                                                    RewardItemID = rowid;
                                                    var showonly = "showedit";
                                                    $('#hdnRewardItemID-edit').val(RewardItemID);
                                                    getRewardDetails(RewardItemID, showonly);
                                                }
                                            },
                    caption:'Manage Rewards'
            });
            jQuery('#rewardslist').jqGrid('navGrid','#rewardslistpager',
                    { edit:false,add:false,del:false, search:false, refresh: true });
        }
        
    }

    function getRewardDetails(RewardItemID,showonly){
        $.ajax({
            url: 'rewardDetails?RewardItemID='+RewardItemID,
            type: 'POST',
            dataType: 'json',
            success: function(data)
            {
                if(data.showdialog == false && data.message == ''){
                    $("#promoperiod").val(data.PromoDate);
                    $("#offerstartdate").val(data.OfferStartDate);
                    $("#from_hour").val(data.OfferStartHour);
                    $("#from_min").val(data.OfferStartMin);
                    $("#from_sec").val(data.OfferStartSec);
                    $("#offerenddate").val(data.OfferEndDate);
                    $("#to_hour").val(data.OfferEndHour);
                    $("#to_min").val(data.OfferEndMin);
                    $("#to_sec").val(data.OfferEndSec);
                    $("#fromhour").val(data.OfferStartHour);
                    $("#frommin").val(data.OfferStartMin);
                    $("#fromsec").val(data.OfferStartSec);
                    $("#tohour").val(data.OfferEndHour);
                    $("#tomin").val(data.OfferEndMin);
                    $("#tosec").val(data.OfferEndSec);
                    $("#partner").val(data.PartnerName);
                    $("#partnerid").val(data.PartnerID);
                    $("#rewarditem").val(data.ItemName);
                    $("#category").val(data.Category);
                    $("#categoryid").val(data.CategoryID);
                    var points = data.Points;
                    $("#points").val(points.toString().replace(/,/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ","));
                    $("#eligibility").val(data.Eligibility);
                    $("#eligibilityid").val(data.PClassID);
                    $("#status").val(data.Status);
                    $("#statusid").val(data.StatusID);
                    var itemcount = data.AvailableItemCount;
                    $("#availableitemcount").val(itemcount.toString().replace(/,/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ","));
                    if(!data.NoDrawDate){
                        $("#editdrawdate").val(data.DrawDate);
                        $("#editdrawdate_hour").val(data.DrawDateHour);
                        $("#editdrawdate_min").val(data.DrawDateMin);
                        $("#editdrawdate_sec").val(data.DrawDateSec);
                    }
                    
                    if(data.thblimited != ""){
                        document.getElementById("thblimitedframe").contentDocument.location.reload(true);
                        $("#thblimitedphoto").val(data.thblimited);
                        $("#thblimitedpicname").val(data.thblimited);
                        $("#thblimitedpath").val(data.ImagePath+data.thblimited);
                    }
                    if(data.thboutofstock != ""){
                        document.getElementById("thboutofstockframe").contentDocument.location.reload(true);
                        $("#thboutofstockphoto").val(data.thboutofstock);
                        $("#thboutofstockpicname").val(data.thboutofstock);
                        $("#thboutofstockpath").val(data.ImagePath+data.thboutofstock);
                    }
                    if(data.ecoupon != ""){
                        document.getElementById("ecouponframe").contentDocument.location.reload(true);
                        $("#ecouponphoto").val(data.ecoupon);
                        $("#ecouponpicname").val(data.ecoupon);
                        $("#ecouponpath").val(data.ImagePath+data.ecoupon);
                    }
                    if(data.lmlimited != ""){
                        document.getElementById("lmlimitedframe").contentDocument.location.reload(true);
                        $("#lmlimitedphoto").val(data.lmlimited);
                        $("#lmlimitedpicname").val(data.lmlimited);
                        $("#lmlimitedpath").val(data.ImagePath+data.lmlimited);
                    }
                    if(data.lmoutofstock != ""){
                        document.getElementById("lmoutofstockframe").contentDocument.location.reload(true);
                        $("#lmoutofstockphoto").val(data.lmoutofstock);
                        $("#lmoutofstockpicname").val(data.lmoutofstock);
                        $("#lmoutofstockpath").val(data.ImagePath+data.lmoutofstock);
                    }
                    if(data.webslider != ""){
                        document.getElementById("websliderframe").contentDocument.location.reload(true);
                        $("#websliderphoto").val(data.webslider);
                        $("#websliderpicname").val(data.webslider);
                        $("#websliderpath").val(data.ImagePath+data.webslider);
                    }

                    $("#subtext").val(data.Subtext);
                    $("#about").html(data.About);
                    tinyMCE.get('editabout').setContent(data.About);
                    tinyMCE.get('editaboutreadonly').setContent(data.About);
                    $("#terms").html(data.Terms);
                    tinyMCE.get('editterms').setContent(data.Terms);
                    tinyMCE.get('edittermsreadonly').setContent(data.Terms);
                    $("#hdnStatus").val(data.Status);
                    if(showonly == "edit"){
                        $("#showrewardsdetails").siblings(".ui-dialog-buttonpane").find("button").eq(0).click();
                    } else {
                        $("#showrewardsdetails").dialog("open");
                    }
                } else {
                    $("#message").html(data.message);
                    $("#messagedialog2").dialog("open");
                }
            }
        });
    }

    function getActivePartners(action){
        $.ajax({
            url: 'activePartners',
            type: 'POST',
            dataType: 'json',
            success: function(data)
            {
                if(action == 2){
                    if(data.showdialog == false && data.message == ''){
                        $("#addpartner").html("");
                        $("#addpartner").append("<option value=''>Select Partner</option>");
                        for(var itr = 0; itr < data.CountofPartners; itr++){
                            $("#addpartner").append("<option value='"+data.ListofPartners[itr].PartnerID+"'>"+data.ListofPartners[itr].PartnerName+"</option>");
                        }
                    } else {
                        $("#message").html(data.message);
                        $("#messagedialog2").dialog("open");
                    }
                } else {
                    if(data.showdialog == false && data.message == ''){
                        $("#editpartner").html("");
                        $("#editpartnerreadonly").html("");
                        $("#editpartner").append("<option value=''>Select Partner</option>");
                        $("#editpartnerreadonly").append("<option value=''>Select Partner</option>");
                        for(var itr = 0; itr < data.CountofPartners; itr++){
                            $("#editpartner").append("<option value='"+data.ListofPartners[itr].PartnerID+"'>"+data.ListofPartners[itr].PartnerName+"</option>");
                            $("#editpartnerreadonly").append("<option value='"+data.ListofPartners[itr].PartnerID+"'>"+data.ListofPartners[itr].PartnerName+"</option>");
                        }
                        var partnerid = $("#partnerid").val()
                        $("#editpartner option[value='"+partnerid+"']").attr("selected", "selected");
                        $("#editpartnerreadonly option[value='"+partnerid+"']").attr("selected", "selected");
                    } else {
                        $("#message").html(data.message);
                        $("#messagedialog2").dialog("open");
                        $("#editrewarddetails").dialog("close");
                    }
                } 
            }
        });
    }

    function getActiveCategories(action){
        $.ajax({
            url: 'categoryList',
            type: 'POST',
            dataType: 'json',
            success: function(data)
            {
                if(action == 2){
                        if(data.showdialog == false && data.message == ''){
                        $("#addcategory").html("");
                        $("#addcategory").append("<option value=''>Select Category</option>");
                        for(var itr = 0; itr < data.CountofCategories; itr++){
                            $("#addcategory").append("<option value='"+data.ListofCategories[itr].CategoryID+"'>"+data.ListofCategories[itr].CategoryName+"</option>");
                        }
                        var today = new Date();
                        var dd = today.getDate();
                        var mm = today.getMonth()+1; //January is 0.
                        var yy = today.getFullYear();
                        //condition for formatting month
                        mm = mm < 10 ? mm = "0"+mm:mm=mm;

                        //condition for formatting day
                        dd = dd < 10 ? dd = "0"+dd:dd=dd;
                        var datetoday = yy+"-"+mm+"-"+dd;
                        $("#add_from_date").val(datetoday);
                    } else {
                        $("#message").html(data.message);
                        $("#messagedialog2").dialog("open");
                        $("#editrewarddetails").dialog("close");
                    }
                } else {
                    if(data.showdialog == false && data.message == ''){
                        $("#editcategory").html("");
                        $("#editcategoryreadonly").html("");
                        $("#editcategory").append("<option value=''>Select Category</option>");
                        $("#editcategoryreadonly").append("<option value=''>Select Category</option>");
                        for(var itr = 0; itr < data.CountofCategories; itr++){
                            $("#editcategory").append("<option value='"+data.ListofCategories[itr].CategoryID+"'>"+data.ListofCategories[itr].CategoryName+"</option>");
                            $("#editcategoryreadonly").append("<option value='"+data.ListofCategories[itr].CategoryID+"'>"+data.ListofCategories[itr].CategoryName+"</option>");
                        }
                        var categoryid = $("#categoryid").val();
                        var eligibilityid = $("#eligibilityid").val();
                        var statusid = $("#statusid").val();
                        $("#editcategory option[value='"+categoryid+"']").attr("selected", "selected");
                        $("#editcategoryreadonly option[value='"+categoryid+"']").attr("selected", "selected");
                        $("#editeligibility option[value='"+eligibilityid+"']").attr("selected", "selected");
                        $("#editeligibilityreadonly option[value='"+eligibilityid+"']").attr("selected", "selected");
                        $("#editstatus option[value='"+statusid+"']").attr("selected", "selected");
                        $("#editsubtext").val($("#subtext").val());
                        $("#from_date").val($("#offerstartdate").val());
                        $("#fromdate").val($("#offerstartdate").val());
                        $("#to_date").val($("#offerenddate").val());
                        $("#todate").val($("#offerenddate").val());
                    } else {
                        $("#message").html(data.message);
                        $("#messagedialog2").dialog("open");
                        $("#editrewarddetails").dialog("close");
                    }
                }
                
            }
        });
    }

     function getCurrentInventory(RewardItemID){
        $.ajax({
            url: 'currentInventory?RewardItemID='+RewardItemID,
            type: 'POST',
            dataType: 'json',
            success: function(data)
            {
                if(data.showdialog == false && data.message == ''){
                    $("#currentinventory").val(data.AvailableItemCount);
                    $("#replenishform").dialog("open");
                } else {
                    $("#message").html(data.message);
                    $("#messagedialog2").dialog("open");
                    $("#replenishform").dialog("close");
                }
            }
        });
    }
    
    $(document).ready(function(){
        $("#displaydetailsform").hide();
        $("#editdetailsform").hide();
        $(".buttonlink-add").val("ADD REWARD");
        $("#addrewardid").val($('#rewardtype1:checked').val());

        $("#accordion-link1").live("mouseover",function () {
            $("#accordion-arrow-icon1").removeAttr("style");
            $("#accordion-arrow-icon1").removeAttr("src");
            $("#accordion-arrow-icon1").attr('src','../../css/redmond/images/accord-down-hover.png');
        });

        $("#accordion-link1").live("mouseout",function () {
            $("#accordion-arrow-icon1").removeAttr("style");
            $("#accordion-arrow-icon1").removeAttr("src");
            $("#accordion-arrow-icon1").attr('src','../../css/redmond/images/accord-down.png');
        });

        $("#accordioneffect1").live("click",function () {
            var show = $("#accordion-link1").attr('done');
            if(show === "yes"){
                $("#about").removeAttr("style");
                $("#about").removeAttr("done");
                $("#about").css('display','none');
                $("#accordion-link1").attr('done','no');
            } else {
                $("#about").removeAttr("style");
                $("#about").removeAttr("done");
                $("#about").css('display','block');
                $("#about").css('padding-top','5px');
                $("#accordion-link1").attr('done','yes');
            }
        });


        $("#accordion-link2").live("mouseover",function () {
            $("#accordion-arrow-icon2").removeAttr("style");
            $("#accordion-arrow-icon2").removeAttr("src");
            $("#accordion-arrow-icon2").attr('src','../../css/redmond/images/accord-down-hover.png');
        });

        $("#accordion-link2").live("mouseout",function () {
            $("#accordion-arrow-icon2").removeAttr("style");
            $("#accordion-arrow-icon2").removeAttr("src");
            $("#accordion-arrow-icon2").attr('src','../../css/redmond/images/accord-down.png');
        });

        $("#accordioneffect2").live("click",function () {
            var show = $("#accordion-link2").attr('done');
            if(show === "yes"){
                $("#terms").removeAttr("style");
                $("#terms").removeAttr("done");
                $("#terms").css('display','none');
                $("#accordion-link2").attr('done','no');
            } else {
                $("#terms").removeAttr("style");
                $("#terms").removeAttr("done");
                $("#terms").css('display','block');
                $("#terms").css('padding-top','5px');
                $("#accordion-link2").attr('done','yes');
            }
        });

        $("#edit-itemcount").live("mouseover",function () {
            $("#edit-itemcount-image").removeAttr("src");
            $("#edit-itemcount-image").attr('src','../../images/ui-icon-refill-hover.png');
        });

        $("#edit-itemcount").live("mouseout",function () {
            $("#edit-itemcount-image").removeAttr("src");
            $("#edit-itemcount-image").attr('src','../../images/ui-icon-refill.png');
        });

        $('#viewrewardsby').live('change', function(){ 
            var viewrewardsby = $('#viewrewardsby').val();
            if($('#rewardtype1:checked').val() != undefined){
                var rewardtype = $('#rewardtype1:checked').val();
            } else {
                var rewardtype = $('#rewardtype2:checked').val();
            }
            reloadRewardsList(viewrewardsby,rewardtype);
        });

        $('#rewardtype1').live('click', function(){ 
            $("#partner-row").removeAttr('style');
            $("#category-row").removeAttr('style');
            $("#editdrawdate-row").css("display", "none");
            var viewrewardsby = $('#viewrewardsby').val();
            var rewardtype = $('#rewardtype1:checked').val();
            reloadRewardsList(viewrewardsby,rewardtype);
            $("#addrewardid").val(rewardtype);
            $(".buttonlink-add").val("ADD REWARD");
            $("#addpartner-row").removeAttr("style");
            $("#addcategory-row").removeAttr("style");
            $("#additemcount-row").removeAttr("style");
            $("#additemcount").val("");
            $("#addpromoname-row").css("display", "none");
            $("#addpromocode-row").css("display", "none");
            $("#adddrawdate-row").css("display", "none");
        });

        $('#rewardtype2').live('click', function(){
            $("#editdrawdate-row").removeAttr("style");
            $("#partner-row").removeAttr('style');
            $("#category-row").removeAttr('style');
            $("#partner-row").attr('style', 'display: none');
            $("#category-row").attr('style', 'display: none');
            var viewrewardsby = $('#viewrewardsby').val();
            var rewardtype = $('#rewardtype2:checked').val();
            reloadRewardsList(viewrewardsby,rewardtype);
            $("#addrewardid").val(rewardtype);
            $(".buttonlink-add").val("ADD RAFFLE");
            $("#addpartner-row").removeAttr("style");
            $("#addcategory-row").removeAttr("style");
            $("#addpromoname-row").removeAttr("style");
            $("#addpromocode-row").removeAttr("style");
            $("#adddrawdate-row").removeAttr("style");
            $("#addpartner-row").css("display", "none");
            $("#addcategory-row").css("display", "none");
            $("#additemcount-row").css("display", "none");
            $("#additemcount").val("1");
        });

        $("#deletebutton").live("click",function(){
            var RewardItemID = $(this).attr("RewardItemID");
            $("#hdnRewardItemID").val(RewardItemID);
            $("#hdnFunctionName").val("DeleteReward");
            if($('#rewardtype1:checked').val() != undefined){
                var rewardtype = $('#rewardtype1:checked').val();
            } else {
                var rewardtype = $('#rewardtype2:checked').val();
            }
            $("#hdnrewardtype").val(rewardtype);
            if(rewardtype == "1"){
                $("#deleterewardconfirmation1").dialog("open");
            } else {
                $("#deleterewardconfirmation2").dialog("open");
            }
            
        });

        $("#editbutton").live("click",function(){
            var RewardItemID = $(this).attr("RewardItemID");
            var Status = $(this).attr("Status");
            $("#hdnRewardItemID-edit").val(RewardItemID);
            var showonly = 'edit';
            getRewardDetails(RewardItemID,showonly, Status);
        });
        
        $("#viewlink").live("click",function(){
            var RewardItemID = $(this).attr("RewardItemID");
            $("#hdnRewardItemID-edit").val(RewardItemID);
            var showonly = 'showedit';
            var Status = '';
            getRewardDetails(RewardItemID,showonly, Status);
        });

        $("#refillbutton").live("click",function(){
            var RewardItemID = $(this).attr("RewardItemID");
            $(" #hdnRewardItemID-replenishform").val(RewardItemID);
            getCurrentInventory(RewardItemID);
        });
        
        defaultquantity = "0";
        $("#additems").live('click', function() {
            if ($("#additems").val() == defaultquantity) {
                $("#additems").val("");
                $("#inventoryupdate").val("");
            }
        });

        $("#additems").live('keyup',function() {
            $("#additems").change();
        });

        $("#additems").live('blur',function() {
            $("#additems").change();
        });

        $("#additems").live('change',function() {
            if ($("#additems").val() == "") {
                $("#additems").val("");
                $("#inventoryupdate").val("");
            } else {
                var additems = $(this).val();
                var inventoryupdate = parseInt($("#currentinventory").val()) + parseInt(additems.toString().replace(/,/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ""));
                $("#inventoryupdate").val(inventoryupdate.toString().replace(/,/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ","));
                $("#additems").val(additems.toString().replace(/,/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            }
        });

        $("#additems").live('keypress',function(event){
            return numberonly(event);
        });
        
        $("#additemcount").live('change',function() {
            if ($("#additemcount").val() == "") {
                $("#additemcount").val("");
            } else {
                var inventoryupdate = parseInt($("#additemcount").val());
                $("#additemcount").val(inventoryupdate.toString().replace(/,/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            }
        });

        $("#additemcount").live('keypress',function(event){
            return numberonly(event);
        });

        $("#editpoints").live('change',function() {
            if ($("#editpoints").val() == "") {
                $("#editpoints").val($("#points").val());
            } else {
                var inventoryupdate = parseInt($("#editpoints").val());
                $("#editpoints").val(inventoryupdate.toString().replace(/,/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            }
        });

        $("#editpoints").live('keypress',function(event){
            return numberonly(event);
        });

        $("#editsubtext").live('keypress',function(event){
            return alphanumericSpaceCommaDot(event);
        });
        
        $("#editrewarditem").live('keypress',function(event){
            return alphanumericSpaceDash(event);
        });
        
        $("#editrewarditem").live("keyup",function(event){
            var rewarditem = $("#editrewarditem").val();
            if (rewarditem.substring(0, 1) === " "){
                alert("Warning: Trailing space/s is/are not allowed");
                $("#editrewarditem").val(trimword($("#editrewarditem").val()));
            } else {
                return true;
            }
        });
        
        $("#editsubtext").live("keyup",function(event){
            var subtext = $("#editsubtext").val();
            if (subtext.substring(0, 1) === " "){
                alert("Warning: Trailing space/s is/are not allowed");
                $("#editsubtext").val(trimword($("#editsubtext").val()));
            } else {
                return true;
            }
        });

        $("#addpoints").live('change',function() {
            if ($("#addpoints").val() == "") {
                $("#addpoints").val();
            } else {
                var inventoryupdate = parseInt($("#addpoints").val());
                $("#addpoints").val(inventoryupdate.toString().replace(/,/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            }
        });
       
        $("#addpoints").live('keypress',function(event){
            return numberonly(event);
        });

        $("#addsubtext").live('keypress',function(event){
            return alphanumericSpaceCommaDot(event);
        });
        
        $("#addrewarditem").live('keypress',function(event){
            return alphanumericSpaceDash(event);
        });
        
        $("#addsubtext").live("keyup",function(event){
            var subtext = $("#addsubtext").val();
            if (subtext.substring(0, 1) === " "){
                alert("Warning: Trailing space/s is/are not allowed");
                $("#addsubtext").val(trimword($("#addsubtext").val()));
            } else {
                return true;
            }
        });
        
        $("#addrewarditem").live("keyup",function(event){
            var rewarditem = $("#addrewarditem").val();
            if (rewarditem.substring(0, 1) === " "){
                alert("Warning: Trailing space/s is/are not allowed");
                $("#addrewarditem").val(trimword($("#addrewarditem").val()));
            } else {
                return true;
            }
        });

        $("#addpoints").live('change',function() {
            if ($("#addpoints").val() == "") {
                $("#addpoints").val($("#points").val());
            } else {
                var points = $(this).val();
                $("#addpoints").val(points.toString().replace(/,/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            }
        });

    });
    
    
     $("#addpromoname").live('keypress',function(event){
            return alphanumericSpaceDash(event);
    });
    
     $("#addpromocode").live('keypress',function(event){
            return numberandletter1(event);
    });

    $("#linkButton").live("mouseover",function () {
           $(this).css("cursor", "pointer");
    });
    
    $("#linkButton").live("mouseout",function () {
        $(this).css("cursor", "default");
    });
     
</script>

<?php $urlrefresh = Yii::app()->createUrl('manageRewards/managerewards'); ?>

<?php
/** -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * @Description: Popup Dialog For Messages such as Error Message and Success Message.
 * @Author: aqdepliyan
 */
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'messagedialog1',
    'options'=>array(
        'autoOpen'=>$this->showdialog,
        'modal'=>true,
        'resizable'=>false,
        'draggable'=>false,
        'show'=>'fade',
        'hide'=>'fade',
        'width'=>350,
        'height'=>200,
        'close' => 'js:function(event, ui){
            window.location.href = "'.$urlrefresh.'"; 
        }',
        'buttons' => array
        (
            'OK'=>'js:function(){
                $(this).dialog("close");
                window.location = "managerewards";
            }',
        ),
    ),
));

echo "<center>";
echo "<br/>";
echo $this->message;
echo "<br/>";
echo "</center>";

    
$this->endWidget('zii.widgets.jui.CJuiDialog');
/** ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
?>

<?php 

//Render Delete Reward Window
echo $this->renderPartial('delete', array('model'=>$model)); 

//Render Replenishment Reward Window
echo $this->renderPartial('replenish', array('model'=>$model)); 

//Render Edit Reward Window
echo $this->renderPartial('update', array('model'=>$model)); 

//Render Add Reward Window
echo $this->renderPartial('add', array('model'=>$model)); 

?>

<?php
/** ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
/**
 * @Description: For loading Initial Data Grid
 * @Author: aqdepliyan
 */
?>
<?php
    $this->widget('application.components.widgets.JqGridWidget', array('tableID' => 'rewardslist', 'pagerID' => 'rewardslistpager',
        'jqGridParam' => array(
            'url' => $this->createUrl('managerewards'),
            'loadonce' => true,
            'caption' => 'Manage Rewards',
            'height' => '50',
            'colNames' => array('Partner', 'Reward', 'Description', 'Category', 'Points Required', "Eligibility", 'Status', 'Promo Period','Action'),
            'colModel' => array(
                array('name' => 'PartnerName', 'sortable' => false, 'width' => '200px', 'resizable' => true, 'align' => 'center'),
                array('name' => 'RewardName', 'sortable' => false, 'width' => '350px', 'resizable' => true, 'align' => 'center'),
                array('name' => 'Description', 'sortable' => false, 'width' => '220px', 'resizable' => true, 'align' => 'center', 'hidden' => true),
                array('name' => 'Category', 'sortable' => false, 'width' => '220px', 'resizable' => true, 'align' => 'center'),
                array('name' => 'Points', 'sortable' => false, 'width' => '80px', 'resizable' => true, 'align' => 'center'),
                array('name' => 'Eligibility', 'sortable' => false, 'width' => '120px', 'resizable' => true, 'align' => 'center'),
                array('name' => 'Status', 'sortable' => false, 'width' => '120px', 'resizable' => true, 'align' => 'center'),
                array('name' => 'PromoPeriod', 'sortable' => false, 'width' => '280px', 'resizable' => true, 'align' => 'center', 'cellattr' => 'style=white-space: normal;' ),
                array('name' => 'Action', 'sortable' => false, 'width' => '200px', 'resizable' => false, 'align' => 'center'),
            ),
            'onCellSelect' =>"function(rowid, col, content, e) {  
                                            var column = $('#rewardslist')[0].p.colModel[col].name;
                                            if(column!= 'Action'){
                                                var grid = jQuery('#rewardslist');
                                                var sel_id = grid.jqGrid('getGridParam', 'selrow');
                                                //var status = grid.jqGrid('getCell', sel_id, 'Status');

                                                RewardItemID = rowid;
                                                var showonly = 'showedit';
                                                $('#hdnRewardItemID-edit').val(RewardItemID);
                                                getRewardDetails(RewardItemID, showonly);
                                            }
                                        }",
    )));
?>
<?php
/** ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
?>
