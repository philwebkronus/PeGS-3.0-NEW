<?php 
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
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,image,code,|,forecolor,backcolor,|,ltr,rt",
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
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,image,code,|,forecolor,backcolor,|,ltr,rt",
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
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,image,code,|,forecolor,backcolor,|,ltr,rt",
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
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,image,code,|,forecolor,backcolor,|,ltr,rt",
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
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,image,code,|,forecolor,backcolor,|,ltr,rt",
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
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,image,code,|,forecolor,backcolor,|,ltr,rt",
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
                
                if(rewardid == 2){
                    if(rewarditem == "" || points == "" || eligibility == "" || status == "" || fromdate == "" || todate == "" || subtext == "") {
                        var message = "Please fill up all fields.";
                        return message;
                    } else if (/^[a-zA-Z0-9- ]*$/.test(rewarditem) === false) {
                        var message = "Special character/s is/are not allowed in Reward Item";
                        return message;
                    } else  {
                        rewarditem = trimword(rewarditem);
                        if(subtext != '')
                            subtext = trimword(subtext);
                        points = trimword(points);
                        return true;
                    }
                } else {
                    if(partner == "" || rewarditem == "" || category == "" || points == "" || eligibility == "" || status == "" || fromdate == "" || todate == "" || subtext == "") {
                        var message = "Please fill up all fields.";
                        return message;
                    } else if (/^[a-zA-Z0-9- ]*$/.test(rewarditem) === false) {
                        var message = "Special character/s is/are not allowed in Reward Item";
                        return message;
                    } else  {
                        rewarditem = trimword(rewarditem);
                        if(subtext != '')
                            subtext = trimword(subtext);
                        points = trimword(points);
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

                if(rewardid == 2){
                    if(rewarditem == "" || points == "" || eligibility == "" || status == "" || fromdate == "" || todate == "" || itemcount == ""
                        || promoname ==  "" || promocode == "" || drawdate == "" || subtext == "") {
                        var message = "Please fill up all fields.";
                        return message;
                    } else if (/^[a-zA-Z0-9- ]*$/.test(rewarditem) === false) {
                        var message = "Special character/s is/are not allowed in Reward Item";
                        return message;
                    } else  {
                        rewarditem = trimword(rewarditem);
                        if(subtext != '')
                            subtext = trimword(subtext);
                        points = trimword(points);
                        return true;
                    }
                } else {
                    if(partner == "" || rewarditem == "" || category == "" || points == "" || eligibility == "" || status == "" || fromdate == "" || todate == "" || itemcount == "" || subtext == "") {
                        var message = "Please fill up all fields.";
                        return message;
                    } else if (/^[a-zA-Z0-9- ]*$/.test(rewarditem) === false) {
                        var message = "Special character/s is/are not allowed in Reward Item";
                        return message;
                    } else  {
                        rewarditem = trimword(rewarditem);
                        if(subtext != '')
                            subtext = trimword(subtext);
                        points = trimword(points);
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
                    { edit:false,add:false,del:false, search:false, refresh: true });
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
<?php
/** -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * @Description: Form for Deleting of Reward Item/Coupon
 * @Author: aqdepliyan
 */
        $urlrefresh = Yii::app()->createUrl('manageRewards/managerewards');
        
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'delete-item-form',
            'enableClientValidation' => true,
            'enableAjaxValidation' => true,
            'clientOptions' => array(
                'validateOnSubmit' => true,
            ),
            'action' => $this->createUrl('manipulateReward')
        ));
?>
<table style="font-size: 12px;">
    <tr>
        <!----- Controls for filtering rewards list data ----->
        <td style="text-align: right; width: 50%;">
            <?php echo CHtml::label("VIEW REWARDS BY: ", "viewrewardsby", array('style' => 'vertical-align:middle;'));?>
            <?php echo $form->dropDownList($model, 'viewrewardsby', array("0" => "All", "1" => "Active", "2" => "Inactive","3" => "Out-Of-Stock"), array('id' => 'viewrewardsby')) ?>
            &nbsp;&nbsp;&nbsp;&nbsp;
        </td>
        <td>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <?php echo $form->radioButton($model,'rewardtype',array('value'=>'1','uncheckValue'=>null, 'id' => 'rewardtype1',  'checked'=>'checked')); ?>
            <?php echo CHtml::label("Rewards e-Coupons", "rewardsecoupons", array('For'=>'rewardtype1'));?>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <?php echo $form->radioButton($model,'rewardtype',array('value'=>'2','uncheckValue'=>null, 'id' => 'rewardtype2')); ?>
            <?php echo CHtml::label("Raffle e-Coupons", "raffleecoupons",array('For'=>'rewardtype2'));?>
        </td>
        <!---------------------------------------------------------->
    </tr>
</table>
<?php echo CHtml::hiddenField('hdnFunctionName' , '', array('id' => 'hdnFunctionName')); ?>
<?php echo CHtml::hiddenField('hdnRewardItemID' , '', array('id'=>'hdnRewardItemID')); ?>
<table id="rewardslist"></table>
<div id="rewardslistpager"></div>
<div id="main" style="position:relative;">
    <center>
        <br>
        <?php echo CHtml::button('ADD REWARD', array('class'=>'buttonlink-add','id'=>'linkButton', 'style'=>'color:white;', 'onclick' => '$("#addnewreward").dialog("open"); return false;')); ?>
    </center>
</div>
<?php echo CHtml::beginForm(array('manageRewards/managereward'), 'POST', array(
        'id'=>'ManageRewardsForm',
        'name'=>'ManageRewardsForm')); ?>
        
<?php echo CHtml::endForm(); ?>   
<?php $this->endWidget();
/** ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
?>


<?php
/** -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * @Description: Popup Confirmation Dialog box for delete reward item/coupon function
 * @Author: aqdepliyan
 */
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'deleterewardconfirmation1',
    'options'=>array(
        'title'=>'DELETE REWARD',
        'autoOpen'=>false,
        'modal'=>true,
        'closeOnEscape' => false,
        'resizable'=>false,
        'draggable'=>false,
        'show'=>'fade',
        'hide'=>'fade',
        'buttons' => array
        (
            'YES'=>'js:function(){
                $("#delete-item-form").submit();
                $(this).dialog("close");
            }',
            'NO'=>'js:function(){ $(this).dialog("close"); }',
        ),
    ),
));
echo "<center>";
echo 'Do you really want to delete this reward?';
echo "<br/>";
echo "</center>";
    
$this->endWidget('zii.widgets.jui.CJuiDialog');

$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'deleterewardconfirmation2',
    'options'=>array(
        'title'=>'DELETE REWARD',
        'autoOpen'=>false,
        'modal'=>true,
        'closeOnEscape' => false,
        'resizable'=>false,
        'draggable'=>false,
        'show'=>'fade',
        'hide'=>'fade',
        'close' => 'js:function(event, ui){
            window.location.href = "'.$urlrefresh.'"; 
        }',
        'buttons' => array
        (
            'YES'=>'js:function(){
                $("#delete-item-form").submit();
                $(this).dialog("close");
            }',
            'NO'=>'js:function(){ $(this).dialog("close"); window.location.href = "'.$urlrefresh.'"; }',
        ),
    ),
));
echo "<center>";
echo 'Do you really want to delete this raffle?';
echo "<br/>";
echo "</center>";
    
$this->endWidget('zii.widgets.jui.CJuiDialog');
/** ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
?>


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
/** -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * @Description: Form for Replenishing of Reward Item/Coupon Inventory
 * @Author: aqdepliyan
 */ 
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'messagedialog5',
    'options'=>array(
        'title' => 'REPLENISH MESSAGE',
        'autoOpen'=>false,
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
                $("#additems").val("");
                $("#additems").change();
                $(this).dialog("close");
                window.location.href = "'.$urlrefresh.'"; 
            }',
        ),
    ),
));

echo "<center>";
echo "<br/>";
echo "<span id='message3'></span>";
echo "<br/>";
echo "</center>";
    
$this->endWidget('zii.widgets.jui.CJuiDialog');
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'replenishform',
    'options'=>array(
        'title'=>'REPLENISHING INVENTORY',
        'autoOpen'=>false,
        'modal'=>true,
        'resizable'=>false,
        'draggable'=>true,
        'width'=>500,
        'show'=>'fade',
        'hide'=>'fade',
        'open' => 'js:function(event,ui){
                        $("#replenishingform").show();
        }',
        'close' => 'js:function(event,ui){
                        $("#additems").val("");
                        $("#additems").change();
                        window.location.href = "'.$urlrefresh.'"; 
        }',
        'buttons' => array
        (
            'SAVE'=>'js:function(){
                var additems = $("#additems").val();
                if(additems == "0"){
                    $("#message3").html("0 amount is not valid. <br/> Please enter amount to add.");
                    $("#messagedialog5").dialog("open");
                } else if(additems == ""){
                    $("#message3").html("Please enter amount to add.");
                    $("#messagedialog5").dialog("open");
                } else {
                    $("#replenish-form").submit();
                    $(this).dialog("close");
                }
            }'
        ),
    ),
));
?>
 <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'replenish-form',
        'enableClientValidation' => true,
        'enableAjaxValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
        'action' => $this->createUrl('manipulateReward')
    ));
    ?>
<?php echo CHtml::hiddenField('hdnFunctionName' , 'ReplenishItem', array('id' => 'hdnFunctionName')); ?>
<?php echo CHtml::hiddenField('hdnRewardItemID-replenishform' , '', array('id'=>'hdnRewardItemID-replenishform')); ?>
<table id="replenishingform" style="display: none">
    <tr>
        <td>Current Inventory Level</td>
        <td>
            <?php echo $form->textField($model, 'currentinventory', array('id'=>'currentinventory', 'style' => 'width: 200px;', 'readonly' => 'readonly')) ?>
        </td>
    </tr>
    <tr>
        <td>Add Items</td>
        <td>
            <?php echo $form->textField($model, 'additems', array('id'=>'additems', 'style' => 'width: 200px;')) ?>
            <?php echo CHtml::hiddenField('hdnadditems' , '', array('id'=>'hdnadditems')); ?>
        </td>
    </tr>
        <td>Total Inventory Level</td>
        <td>
            <?php echo $form->textField($model, 'inventoryupdate', array('id'=>'inventoryupdate', 'style' => 'width: 200px;', 'readonly' => 'readonly')) ?>
        </td>
    </tr>
</table>
<?php $this->endWidget();?>
<?php $this->endWidget('zii.widgets.jui.CJuiDialog');
/** ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
?>


<?php
/** -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * @Description: Form for Editing of Reward Item/Coupon Details
 * @Author: aqdepliyan
 */
?>
<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'messagedialog2',
    'options'=>array(
        'autoOpen'=>false,
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
echo "<span id='message'></span>";
echo "<br/>";
echo "</center>";

    
$this->endWidget('zii.widgets.jui.CJuiDialog');?>

<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'showrewardsdetails',
    'options'=>array(
        'title'=>'REWARD DETAILS',
        'autoOpen'=>false,
        'modal'=>true,
        'resizable'=>false,
        'draggable'=>false,
        'position'=>array("middle", 100),
        'width'=>'700',
        'show'=>'fade',
        'hide'=>'fade',
        'open' => 'js:function(event,ui){
                        $("#displaydetailsform").show();
        }',
        'buttons' => array
        (
            'EDIT'=>'js:function(){
                $("#editrewarddetails").dialog("open");
                $(this).dialog("close");
            }'
        ),
    ),
));
?>
    <table id="displaydetailsform" style=" display: none;">
        <tr>
            <td id="title-row">Promo Period</td>
            <td id="promoperiodtd">
                <input type="text" name="promoperiod" id="promoperiod" style="width: 435px;" readonly>
                <input type="hidden" name="offerstartdate" id="offerstartdate" />
                <input type="hidden" name="offerenddate" id="offerenddate" />
            </td>
        </tr>
        <tr>
            <td id="title-row">Partner</td>
            <td id="partnertd">
                <input type="text" name="partner" id="partner" style="width: 435px;"readonly />
                <input type="hidden" name="partnerid" id="partnerid" />
            </td>
        </tr>
        <tr>
            <td id="title-row">Reward Item</td>
            <td id="rewarditemtd">
                <input type="text" name="rewarditem" id="rewarditem" style="width: 435px;" readonly>
            </td>
        </tr>
        <tr>
            <td id="title-row">Category</td>
            <td id="categorytd">
                <input type="text" name="category" id="category"style=" width: 435px;" readonly>
                <input type="hidden" name="categoryid" id="categoryid" />
            </td>
        </tr>
        <tr>
            <td id="title-row">Points Required</td>
            <td id="pointstd">
                <input type="text" name="points" id="points" style="width: 435px;" readonly>
            </td>
        </tr>
        <tr>
            <td id="title-row">Eligibility</td>
            <td id="eligibilitytd">
                <input type="text" name="eligibility" id="eligibility" style="width: 435px;" readonly>
                <input type="hidden" name="eligibilityid" id="eligibilityid" />
            </td>
        </tr>
        <tr>
            <td id="title-row">Status</td>
            <td id="statustd">
                <input type="text" name="status" id="status" style="width: 435px;" readonly>
                <input type="hidden" name="statusid" id="statusid" />
            </td>
        </tr>
        <tr>
            <td id="title-row">Inventory Balance</td>
            <td id="inventorybalancetd">
                <input type="text" name="availableitemcount" id="availableitemcount" style="width: 435px;" readonly>
                <input type="hidden" name="subtext" id="subtext" />
            </td>
        </tr>
        <tr>
            <td id="abouttherewardtd" colspan="2" >
                <div id="aboutrewards">
                    <div id="accordioneffect1">
                        <a id="accordion-link1" href="javascript:void(0)"  done="no">
                            <img id="accordion-arrow-icon1" src="../../css/redmond/images/accord-down.png">
                        </a>
                        <span id="accordion-title-row1">About the Reward</span>
                    </div><br>
                    <div id="about" style=" display: none;"></div>
                </div>
            </td>
        </tr>
        <tr>
            <td id="termsoftherewardtd" colspan="2">
                <div id="termsrewards">
                    <div id="accordioneffect2">
                        <a id="accordion-link2" href="javascript:void(0)"  done="no">
                            <img id="accordion-arrow-icon2" src="../../css/redmond/images/accord-down.png">
                        </a>
                        <span id="accordion-title-row2">Terms of the Reward</span>
                    </div><br>
                    <div id="terms" style=" display: none;"></div>
                </div>
            </td>
        </tr>
    </table>
<?php $this->endWidget('zii.widgets.jui.CJuiDialog');?>
<?php 
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'messagedialog3',
    'options'=>array(
        'title' => 'EDIT REWARD MESSAGE',
        'autoOpen'=>false,
        'modal'=>true,
        'resizable'=>false,
        'draggable'=>false,
        'show'=>'fade',
        'hide'=>'fade',
        'width'=>'350',
        'height'=>'200',
        'buttons' => array
        (
            'OK'=>'js:function(){
                $(this).dialog("close");
            }',
        ),
    ),
));

echo "<center>";
echo "<br/>";
echo "<span id='message1'></span>";
echo "<br/>";
echo "</center>";
    
$this->endWidget('zii.widgets.jui.CJuiDialog');
?>
<?php 
    $this->beginWidget('zii.widgets.jui.CJuiDialog',array(
        'id'=>'editrewarddetails',
        'options'=>array(
            'title'=>'Rewards Management',
            'autoOpen'=>false,
            'modal'=>true,
            'resizable'=>false,
            'draggable'=>false,
            'position'=>array("middle",30),
            'width'=>650,
            'show'=>'fade',
            'hide'=>'fade',
            'open' => 'js:function(event,ui){
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();
                            
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).show();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).show();
                            
                            //For Checking whether Item or Coupon
                            if($("#rewardtype1:checked").val() == undefined){
                                var rewardtype = $("#rewardtype2:checked").val();
                            } else {
                                var rewardtype = $("#rewardtype1:checked").val();
                            }
                            
                            var stats = $("#hdnStatus").val();
                            if(stats == "Active"){
                                $("#thblimitedframe").contents().find("#thbsubmit_limited").attr("disabled","disabled");
                                $("#thboutofstockframe").contents().find("#thbsubmit_outofstock").attr("disabled","disabled");
                                $("#lmlimitedframe").contents().find("#lmsubmit_limited").attr("disabled","disabled");
                                $("#lmoutofstockframe").contents().find("#lmsubmit_outofstock").attr("disabled","disabled");
                                $("#ecouponframe").contents().find("#ecoupon_submit").attr("disabled","disabled");
                                $("#websliderframe").contents().find("#webslider_submit").attr("disabled","disabled");
                                $("#editrewarditem").attr("readonly", "readonly");
                                $("#editpoints").attr("readonly", "readonly");
                                $("#editsubtext").attr("readonly", "readonly");
                                $("#editaboutdiv").removeAttr("style");
                                $("#editaboutdiv").attr("style","display: none;");
                                $("#editaboutrodiv").removeAttr("style");
                                $("#edittermsdiv").removeAttr("style");
                                $("#edittermsdiv").attr("style","display: none;");
                                $("#edittermsrodiv").removeAttr("style");
                                
                                $("#promoperioddiv").removeAttr("style");
                                $("#promoperioddiv").attr("style", "display: none");
                                $("#readonlypromoperioddiv").removeAttr("style");
                                
                                $("#eligibilitydiv").removeAttr("style");
                                $("#eligibilitydiv").attr("style", "display: none");
                                $("#readonlyeligibilitydiv").removeAttr("style");
                                $("#partnerdiv").removeAttr("style");
                                $("#partnerdiv").attr("style", "display: none");
                                $("#readonlypartnerdiv").removeAttr("style");
                                $("#categorydiv").removeAttr("style");
                                $("#categorydiv").attr("style", "display: none");
                                $("#readonlycategorydiv").removeAttr("style");
                            } else {
                                $("#thblimitedframe").contents().find("#thbsubmit_limited").removeAttr("disabled");
                                $("#thboutofstockframe").contents().find("#thbsubmit_outofstock").removeAttr("disabled");
                                $("#lmlimitedframe").contents().find("#lmsubmit_limited").removeAttr("disabled");
                                $("#lmoutofstockframe").contents().find("#lmsubmit_outofstock").removeAttr("disabled");
                                $("#ecouponframe").contents().find("#ecoupon_submit").removeAttr("disabled");
                                $("#websliderframe").contents().find("#webslider_submit").removeAttr("disabled");
                                $("#editrewarditem").removeAttr("readonly");
                                $("#editpoints").removeAttr("readonly");
                                $("#editsubtext").removeAttr("readonly");
                                $("#readonlypromoperioddiv").removeAttr("style");
                                $("#promoperioddiv").removeAttr("style");
                                $("#readonlypromoperioddiv").attr("style", "display: none");
                                $("#editaboutrodiv").removeAttr("style");
                                $("#editaboutrodiv").attr("style","display: none;");
                                $("#editaboutdiv").removeAttr("style");
                                $("#edittermsrodiv").removeAttr("style");
                                $("#edittermsrodiv").attr("style","display: none;");
                                $("#edittermsdiv").removeAttr("style");
                                
                                $("#eligibilitydiv").removeAttr("style");
                                $("#readonlyeligibilitydiv").removeAttr("style");
                                $("#readonlyeligibilitydiv").attr("style", "display: none");
                                $("#partnerdiv").removeAttr("style");
                                $("#readonlypartnerdiv").removeAttr("style");
                                $("#readonlypartnerdiv").attr("style", "display: none");
                                $("#categorydiv").removeAttr("style");
                                $("#readonlycategorydiv").removeAttr("style");
                                $("#readonlycategorydiv").attr("style", "display: none");
                            }
                            
                            if(rewardtype == 2){
                                $("#hdnRewardID-edit").val(rewardtype);
                                var statusid = $("#statusid").val();
                                var eligibilityid = $("#eligibilityid").val();
                                $("#editstatus option[value=\'"+statusid+"\']").attr("selected", "selected");
                                $("#editsubtext").val($("#subtext").val());
                                $("#from_date").val($("#offerstartdate").val());
                                $("#fromdate").val($("#offerstartdate").val());
                                $("#to_date").val($("#offerenddate").val());
                                $("#todate").val($("#offerenddate").val());
                                $("#editrewarditem").val($("#rewarditem").val());
                                $("#editeligibility option[value=\'"+eligibilityid+"\']").attr("selected", "selected");
                                $("#editeligibilityreadonly option[value=\'"+eligibilityid+"\']").attr("selected", "selected");
                                var points = $("#points").val();
                                $("#editpoints").val(points.toString().replace(/,/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ","));
                                $("#primarydetails").show();
                                $("#aboutthereward").hide();
                                $("#editaboutreward").hide();
                                $("#edittermsreward").hide();
                            } else {
                                getActivePartners(1);
                                getActiveCategories(1);
                                $("#editrewarditem").val($("#rewarditem").val());
                                var points = $("#points").val();
                                $("#editpoints").val(points.toString().replace(/,/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ","));
                                $("#primarydetails").show();
                                $("#aboutthereward").hide();
                                $("#editaboutreward").hide();
                                $("#edittermsreward").hide();
                            }
            }',
            'close' => 'js:function(event,ui){
                            window.location.href = "'.$urlrefresh.'";
            }',
            'buttons' => array
            (
                array('id' => 'firstback','text'=>'BACK',
                            'click'=> 'js:function(){
                                        $(this).dialog("close");
                                        window.location.href = "'.$urlrefresh.'";
                            }'),
                array('id' => 'secondback','text'=>'BACK','click'=> 'js:function(){
                                        $("#primarydetails").show();
                                        $("#aboutthereward").hide();
                                        $("#editaboutreward").hide();
                                        $("#edittermsreward").hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();

                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).show();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).show();
                            }'),
                array('id' => 'thirdback','text'=>'BACK','click'=> 'js:function(){
                                        $("#primarydetails").hide();
                                        $("#aboutthereward").show();
                                        $("#editaboutreward").hide();
                                        $("#edittermsreward").hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();

                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).show();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).show();
                            }'),
                array('id' => 'fourthback','text'=>'BACK','click'=> 'js:function(){
                                        $("#primarydetails").hide();
                                        $("#aboutthereward").hide();
                                        $("#editaboutreward").show();
                                        $("#edittermsreward").hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();

                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).show();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).show();
                            }'),
                array('id' => 'firstnext','text'=>'NEXT','click'=> 'js:function(){
                                        var rewardid = $("#hdnRewardID-edit").val();
                                        var stats = $("#hdnStatus").val();
                                        
                                        if(stats == "Active"){
                                            var results = validateinputs(1,6, rewardid);

                                            if(results == true){
                                                    $("#primarydetails").hide();
                                                    $("#aboutthereward").show();
                                                    $("#editaboutreward").hide();
                                                    $("#edittermsreward").hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();

                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).show();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).show();
                                            } else {
                                                    $("#message1").html(results);
                                                    $("#messagedialog3").dialog("open");
                                            }
                                        } else {
                                            var results = validateinputs(1,1, rewardid);

                                            if(results == true){
                                                    $("#primarydetails").hide();
                                                    $("#aboutthereward").show();
                                                    $("#editaboutreward").hide();
                                                    $("#edittermsreward").hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();

                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).show();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).show();
                                            } else {
                                                    $("#message1").html(results);
                                                    $("#messagedialog3").dialog("open");
                                            }
                                        }
                                               
                            }'),
                array('id' => 'secondnext','text'=>'NEXT','click'=> 'js:function(){
                                        var stats = $("#hdnStatus").val();
                                        
                                        if(stats == "Active"){
                                            $("#primarydetails").hide();
                                            $("#aboutthereward").hide();
                                            $("#editaboutreward").show();
                                            $("#edittermsreward").hide();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();

                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).show();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).show();
                                        } else {
                                            var rewardid = $("#hdnRewardID-edit").val();
                                            var results = validateinputs(1,2, rewardid);
                                            if(results == true){
                                                    $("#primarydetails").hide();
                                                    $("#aboutthereward").hide();
                                                    $("#editaboutreward").show();
                                                    $("#edittermsreward").hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();

                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).show();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).show();
                                            } 
                                        }
                                        
                            }'),
                array('id' => 'thirdnext','text'=>'NEXT','click'=> 'js:function(){
                                        var stats = $("#hdnStatus").val();
                                        
                                        if(stats == "Active"){
                                            $("#primarydetails").hide();
                                            $("#aboutthereward").hide();
                                            $("#editaboutreward").hide();
                                            $("#edittermsreward").show();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();

                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).show();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).show();
                                        } else {
                                            var rewardid = $("#hdnRewardID-edit").val();
                                            var results = validateinputs(1,3, rewardid);
                                            if(results == true){
                                                    $("#primarydetails").hide();
                                                    $("#aboutthereward").hide();
                                                    $("#editaboutreward").hide();
                                                    $("#edittermsreward").show();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();

                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).show();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).show();
                                            } 
                                        }
                                        
                                        
                            }'),
                array('id' => 'save','text'=>'SAVE','click'=> 'js:function(){
                                        var stats = $("#hdnStatus").val();
                                        var rewardid = $("#hdnRewardID-edit").val();

                                        if(stats == "Active"){
                                            var results = validateinputs(1,5,rewardid);
                                            if(results == true){
                                                $("#edit-item-form").submit();
                                                $(this).dialog("close");
                                            } else {
                                                $("#message1").html(results);
                                                $("#messagedialog3").dialog("open");
                                                $(this).dialog("close");
                                            }
                                        } else {
                                            var results = validateinputs(1,4,rewardid);
                                            if(results == true){
                                                    $("#edit-item-form").submit();
                                                    $(this).dialog("close");
                                            }
                                        }
                                        
                            }')
            ),
        ),
    ));
    
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'edit-item-form',
        'enableClientValidation' => true,
        'enableAjaxValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
        'action' => $this->createUrl('manipulateReward')
    ));
?>
<?php echo CHtml::hiddenField('hdnFunctionName' , 'EditReward', array('id' => 'hdnFunctionName')); ?>
<?php echo CHtml::hiddenField('hdnRewardItemID-edit' , '', array('id'=>'hdnRewardItemID-edit')); ?>
<?php echo CHtml::hiddenField('hdnRewardID-edit' , '', array('id'=>'hdnRewardID-edit')); ?>
<?php echo CHtml::hiddenField('hdnStatus' , '', array('id'=>'hdnStatus')); ?>
<table id="primarydetails" style="display: none;">
    <tr>
        <td colspan="2">
            <div id="edit-breadcrumbs">
                <span id="title-breadcrumbs-row">Edit Reward Details</span>
                <img id="edit-breadcrumbs-image" src="../../images/primarydetails.png">
            </div>
        </td>
    </tr>
    <tr><td colspan="2"></td></tr>
    <tr id="partner-row">
        <td style="width: 250px;">e-Games Partner &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <div id="partnerdiv">
                <?php echo $form->dropDownList($model, 'editpartner', array("" => "Select Partner"), array('id'=>'editpartner', 'style' => 'width: 313px; padding: 2px;')) ?>
            </div>
            <div id="readonlypartnerdiv" style="display: none;">
                <?php echo $form->dropDownList($model, 'editpartnerreadonly', array("" => "Select Partner"), array('id'=>'editpartnerreadonly', 'style' => 'width: 313px; padding: 2px;', 'disabled' => 'disabled')) ?>
            </div>
        </td>
    </tr>
    <tr>
        <td>Reward Item &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php echo $form->textField($model, 'editrewarditem', array('id'=>'editrewarditem', 'style' => 'width: 307px;')) ?>
        </td>
    </tr>
    <tr id="category-row">
        <td>Reward Category &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <div id="categorydiv">
                <?php echo $form->dropDownList($model, 'editcategory', array("" => "Select Category"), array('id'=>'editcategory', 'style' => 'width: 313px; padding: 2px;')) ?>
            </div>
            <div id="readonlycategorydiv" style="display: none;">
                <?php echo $form->dropDownList($model, 'editcategoryreadonly', array("" => "Select Category"), array('id'=>'editcategoryreadonly', 'style' => 'width: 313px; padding: 2px;', 'disabled' => 'disabled')) ?>
            </div>
        </td>
    </tr>
    <tr>
        <td>Reward Points Requirement &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php echo $form->textField($model, 'editpoints', array('id'=>'editpoints', 'style' => 'width: 307px;')) ?>
        </td>
    </tr>
    <tr>
        <td>Member Eligibility &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <div id="eligibilitydiv">
                <?php echo $form->dropDownList($model, 'editeligibility', array("" => "Select Eligibility", "2" => "Regular", "3" => "VIP"), array('id'=>'editeligibility', 'style' => 'width: 313px; padding: 2px;')) ?>
            </div>
            <div id="readonlyeligibilitydiv" style="display: none;">
                <?php echo $form->dropDownList($model, 'editeligibilityreadonly', array("" => "Select Eligibility", "2" => "Regular", "3" => "VIP"), array('id'=>'editeligibilityreadonly', 'style' => 'width: 313px; padding: 2px;', 'disabled' => 'disabled')) ?>
            </div>
        </td>
    </tr>
    <tr>
        <td>Status &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php echo $form->dropDownList($model, 'editstatus', array("" => "Select Status", "1" => "Active", "2" => "Inactive"), array('id'=>'editstatus', 'style' => 'width: 313px; padding: 2px;')) ?>
        </td>
    </tr>
    <tr>
        <td style="vertical-align: top;">Subtexts &nbsp;<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php echo $form->textArea($model, 'editsubtext', array('id'=>'editsubtext', 'rows' => 3, 'cols' => 31, 'maxlength' => 200)); ?>
        </td>
    </tr>
    <tr>
        <td style="vertical-align: top;">Promo Period &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <div id="promoperioddiv">
            <?php 
                    $yeartodate = (string)date('Y'); 
                    $maxyear = Yii::app()->params['maximum_datepicker_year']; 
                    $yearrange =  $yeartodate.':'.$maxyear;
                    $dateformat= Yii::app()->params['dateformat']; 
            ?>
            <b>From :</b>
            <?php
            $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                'name'=>'from_date',  
                'id'=>'from_date',  
                'value'=>  '', 
                 'options'=>array(
                    'showAnim'=>'fade',
                    'dateFormat'=>$dateformat,
                    'changeYear' => true,           // can change year
                    'changeMonth' => true,          // can change month
                     'yearRange' => $yearrange,
                     'minDate' => '0',
                ),
                'htmlOptions'=>array(
                    'style'=>'height:20px; width: 255px;'
                ),
            ));
            ?>
            <?php echo CHtml::dropdownlist('from_hour', '00', array("00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04",
                                                                                                                                "05" => "05", "06" => "06", "07" => "07", "08" => "08", "09" => "09",
                                                                                                                                "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14",
                                                                                                                                "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19",
                                                                                                                                "20" => "20", "21" => "21", "22" => "22", "23" => "23"),
                                                                                                                                array('style'=>'padding:3px; display: none;'));?>
            <?php echo CHtml::dropdownlist('from_min', '00', array("00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04",
                                                                                                                                "05" => "05", "06" => "06", "07" => "07", "08" => "08", "09" => "09",
                                                                                                                                "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14",
                                                                                                                                "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19",
                                                                                                                                "20" => "20", "21" => "21", "22" => "22", "23" => "23", "24" => "24",
                                                                                                                                "25" => "25", "26" => "26", "27" => "27", "28" => "28", "29" => "29",
                                                                                                                                "30" => "30", "31" => "31", "32" => "32", "33" => "33", "34" => "34",
                                                                                                                                "35" => "35", "36" => "36", "37" => "37", "38" => "38", "39" => "39",
                                                                                                                                "40" => "40", "41" => "41", "42" => "42", "43" => "43", "44" => "44",
                                                                                                                                "45" => "45", "46" => "46", "47" => "47", "48" => "48", "49" => "49",
                                                                                                                                "50" => "50", "51" => "51", "52" => "52", "53" => "53", "54" => "54",
                                                                                                                                "55" => "55", "56" => "56", "57" => "57", "58" => "58", "59" => "59"), array('style'=>'padding:3px; display: none;', ));?>
            <?php echo CHtml::dropdownlist('from_sec', '00', array("00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04",
                                                                                                                                "05" => "05", "06" => "06", "07" => "07", "08" => "08", "09" => "09",
                                                                                                                                "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14",
                                                                                                                                "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19",
                                                                                                                                "20" => "20", "21" => "21", "22" => "22", "23" => "23", "24" => "24",
                                                                                                                                "25" => "25", "26" => "26", "27" => "27", "28" => "28", "29" => "29",
                                                                                                                                "30" => "30", "31" => "31", "32" => "32", "33" => "33", "34" => "34",
                                                                                                                                "35" => "35", "36" => "36", "37" => "37", "38" => "38", "39" => "39",
                                                                                                                                "40" => "40", "41" => "41", "42" => "42", "43" => "43", "44" => "44",
                                                                                                                                "45" => "45", "46" => "46", "47" => "47", "48" => "48", "49" => "49",
                                                                                                                                "50" => "50", "51" => "51", "52" => "52", "53" => "53", "54" => "54",
                                                                                                                                "55" => "55", "56" => "56", "57" => "57", "58" => "58", "59" => "59"), array('style'=>'padding:3px; display: none;'));?>
            &nbsp;<br/>
            <b>To :</b>
            <?php
            $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                'name'=>'to_date',  
                'id'=>'to_date',  
                'value'=>  '', 
                 'options'=>array(
                    'showAnim'=>'fade',
                    'dateFormat'=>$dateformat,
                    'changeYear' => true,           // can change year
                    'changeMonth' => true,          // can change month
                     'yearRange' => $yearrange,
                     'minDate' => '0',
                ),
                'htmlOptions'=>array(
                    'style'=>'height:20px; width: 255px; margin-top: 5px; margin-left: 18px;'
                ),
            ));
            ?>
            <?php echo CHtml::dropdownlist('to_hour', '00', array("00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04",
                                                                                                                                "05" => "05", "06" => "06", "07" => "07", "08" => "08", "09" => "09",
                                                                                                                                "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14",
                                                                                                                                "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19",
                                                                                                                                "20" => "20", "21" => "21", "22" => "22", "23" => "23"), array('style'=>'padding:3px; display: none;'));?>
            <?php echo CHtml::dropdownlist('to_min', '00', array("00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04",
                                                                                                                                "05" => "05", "06" => "06", "07" => "07", "08" => "08", "09" => "09",
                                                                                                                                "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14",
                                                                                                                                "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19",
                                                                                                                                "20" => "20", "21" => "21", "22" => "22", "23" => "23", "24" => "24",
                                                                                                                                "25" => "25", "26" => "26", "27" => "27", "28" => "28", "29" => "29",
                                                                                                                                "30" => "30", "31" => "31", "32" => "32", "33" => "33", "34" => "34",
                                                                                                                                "35" => "35", "36" => "36", "37" => "37", "38" => "38", "39" => "39",
                                                                                                                                "40" => "40", "41" => "41", "42" => "42", "43" => "43", "44" => "44",
                                                                                                                                "45" => "45", "46" => "46", "47" => "47", "48" => "48", "49" => "49",
                                                                                                                                "50" => "50", "51" => "51", "52" => "52", "53" => "53", "54" => "54",
                                                                                                                                "55" => "55", "56" => "56", "57" => "57", "58" => "58", "59" => "59"), array('style'=>'padding:3px; display: none;'));?>
            <?php echo CHtml::dropdownlist('to_sec', '00', array("00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04",
                                                                                                                                "05" => "05", "06" => "06", "07" => "07", "08" => "08", "09" => "09",
                                                                                                                                "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14",
                                                                                                                                "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19",
                                                                                                                                "20" => "20", "21" => "21", "22" => "22", "23" => "23", "24" => "24",
                                                                                                                                "25" => "25", "26" => "26", "27" => "27", "28" => "28", "29" => "29",
                                                                                                                                "30" => "30", "31" => "31", "32" => "32", "33" => "33", "34" => "34",
                                                                                                                                "35" => "35", "36" => "36", "37" => "37", "38" => "38", "39" => "39",
                                                                                                                                "40" => "40", "41" => "41", "42" => "42", "43" => "43", "44" => "44",
                                                                                                                                "45" => "45", "46" => "46", "47" => "47", "48" => "48", "49" => "49",
                                                                                                                                "50" => "50", "51" => "51", "52" => "52", "53" => "53", "54" => "54",
                                                                                                                                "55" => "55", "56" => "56", "57" => "57", "58" => "58", "59" => "59"), array('style'=>'padding:3px; display: none;'));?>
        </div>
        <div id="readonlypromoperioddiv" style="display: none;">
            <b>From :</b>
            <?php echo CHtml::textField('fromdate' , '', array('id' => 'fromdate', 'style' => 'height:20px; width: 255px;', 'disabled' => 'disabled')); ?>
            &nbsp;<br/>
            <b>To :</b>
            <?php echo CHtml::textField('todate' , '', array('id' => 'todate', 'style' => 'height:20px; width: 255px; margin-top: 5px; margin-left: 18px;', 'disabled' => 'disabled')); ?>
        </div>
        </td>
    </tr>
</table>

<table id="aboutthereward" style="display: none; ">
      <tr>
        <td colspan="3">
            <div id="edit-breadcrumbs">
                <span id="title-breadcrumbs-row">Edit Reward Details</span>
                <img id="edit-breadcrumbs-image" src="../../images/aboutreward.png">
            </div>
        </td>
    </tr>
    <tr><td colspan="3"></td></tr>
    <tr>
        <td colspan="3">
            <span style="font-size: 12px; height: 10px; width: 100%;"><b>Allowed Image Size :</b> <i> 300 KB and Below.</i></span><br/>
            <div id="balancer"></div>
            <iframe id="thblimitedframe"
                    src="<?php echo Yii::app()->createUrl('manageRewards/thumbnailLimited'); ?>" 
                    style="height: 248px; width: 160px; overflow: hidden; border: none; background: #FFFFFF;" >
            </iframe>
            <?php echo CHtml::hiddenField('thblimitedphoto', '', array('id' => 'thblimitedphoto')); ?>
            <iframe id="thboutofstockframe"
                    src="<?php echo Yii::app()->createUrl('manageRewards/thumbnailOutofstock'); ?>" 
                    style="height: 248px; width: 160px; overflow: hidden; border: none; background: #FFFFFF;">
            </iframe>
            <?php echo CHtml::hiddenField('thboutofstockphoto', '',array('id' => 'thboutofstockphoto')); ?>
            <iframe id="ecouponframe"
                    src="<?php echo Yii::app()->createUrl('manageRewards/eCoupon'); ?>" 
                    style="height: 248px; width: 160px; overflow: hidden; border: none; background: #FFFFFF;">
            </iframe>
            <?php echo CHtml::hiddenField('ecouponphoto', '',array('id' => 'ecouponphoto')); ?>
        </td>
    </tr>
    <tr>
        <td colspan="3">
            <div id="balancer"></div>
            <iframe id="lmlimitedframe"
                    src="<?php echo Yii::app()->createUrl('manageRewards/learnMoreLimited'); ?>" 
                    style="height: 248px; width: 160px; overflow: hidden; border: none; background: #FFFFFF;">
            </iframe>
            <?php echo CHtml::hiddenField('lmlimitedphoto', '', array('id' => 'lmlimitedphoto')); ?>
            <iframe id="lmoutofstockframe"
                    src="<?php echo Yii::app()->createUrl('manageRewards/learnMoreOutofstock'); ?>" 
                    style="height: 248px; width: 160px; overflow: hidden; border: none; background: #FFFFFF;">
            </iframe>
            <?php echo CHtml::hiddenField('lmoutofstockphoto', '', array('id' => 'lmoutofstockphoto')); ?>
            <iframe id="websliderframe"
                    src="<?php echo Yii::app()->createUrl('manageRewards/websiteSlider'); ?>" 
                    style="height: 248px; width: 160px; overflow: hidden; border: none; background: #FFFFFF;">
            </iframe>
            <?php echo CHtml::hiddenField('websliderphoto', '',array('id' => 'websliderphoto')); ?>
        </td>
    </tr>
</table>
<table id="editaboutreward" style="display: none;">
    <tr>
        <td colspan="3">
            <div id="edit-breadcrumbs">
                <span id="title-breadcrumbs-row">Edit Reward Details</span>
                <img id="edit-breadcrumbs-image" src="../../images/aboutreward.png">
            </div>
        </td>
    </tr>
    <tr><td colspan="3"></td></tr>
    <tr>
        <td style="vertical-align: top; width: 200px;">About the Reward</td>
        <td>
            <div id="editaboutdiv"><?php echo $form->textArea($model, 'editabout', array('id' => 'editabout','style' => 'width:100%; height: 400px;')); ?></div>
            <div id="editaboutrodiv" style="display: none;"><?php echo CHtml::textArea('editaboutreadonly','', array('id' => 'editaboutreadonly','style' => 'width:100%; height: 400px;')); ?></div>
        </td>
    </tr>
</table>
<table id="edittermsreward" style="display: none;">
    <tr>
        <td colspan="3">
            <div id="edit-breadcrumbs">
                <span id="title-breadcrumbs-row">Edit Reward Details</span>
                <img id="edit-breadcrumbs-image" src="../../images/termsreward.png">
            </div>
        </td>
    </tr>
    <tr><td colspan="3"></td></tr>
    <tr>
        <td style="vertical-align: top; width: 200px;">Terms of the Reward</td>
        <td>
            <div id="edittermsdiv"><?php echo $form->textArea($model, 'editterms', array('id' => 'editterms','style' => 'width:100%; height: 400px;')); ?></div>
            <div id="edittermsrodiv"><?php echo CHtml::textArea('edittermsreadonly','', array('id' => 'edittermsreadonly','style' => 'width:100%; height: 400px;')); ?></div>
        </td>
    </tr>
</table>
<?php $this->endWidget(); ?>
<?php $this->endWidget('zii.widgets.jui.CJuiDialog');?>
<?php
/** ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
?>


<?php
/** -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * @Description: Form for Adding of New Reward Item/Coupon Details
 * @Author: aqdepliyan
 */
?>
<?php 
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'messagedialog4',
    'options'=>array(
        'title' => 'ADD REWARD MESSAGE',
        'autoOpen'=>false,
        'modal'=>true,
        'resizable'=>false,
        'draggable'=>false,
        'show'=>'fade',
        'hide'=>'fade',
        'width'=>350,
        'height'=>200,
        'buttons' => array
        (
            'OK'=>'js:function(){
                $(this).dialog("close");
            }',
        ),
    ),
));

echo "<center>";
echo "<br/>";
echo "<span id='message2'></span>";
echo "<br/>";
echo "</center>";
    
$this->endWidget('zii.widgets.jui.CJuiDialog');
?>
<?php 
    $this->beginWidget('zii.widgets.jui.CJuiDialog',array(
        'id'=>'addnewreward',
        'options'=>array(
            'title'=>'Rewards Management',
            'autoOpen'=>false,
            'modal'=>true,
            'resizable'=>false,
            'draggable'=>false,
            'position'=>array("middle",30),
            'width'=>650,
            'show'=>'fade',
            'hide'=>'fade',
            'open' => 'js:function(event,ui){
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();
                            
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).show();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).show();
                            
                            //For Checking whether Item or Coupon
                            var rewardtype = $("#addrewardid").val();
                            
                            if(rewardtype == 2){
                                var today = new Date();
                                var dd = today.getDate();
                                var mm = today.getMonth()+1; //January is 0.
                                var yy = today.getFullYear();
                                var datetoday = yy+"-"+mm+"-"+dd;
                                $("#add_from_date").val(datetoday);
                                $("#addprimarydetails").show();
                                $("#addaboutthereward").hide();
                                $("#addaboutreward").hide();
                                $("#addtermsreward").hide();
                            } else {
                                getActivePartners(2);
                                getActiveCategories(2);
                                $("#addprimarydetails").show();
                                $("#addaboutthereward").hide();
                                $("#addaboutreward").hide();
                                $("#addtermsreward").hide();
                            }
            }',
            'close' => 'js:function(event,ui){
                            window.location.href = "'.$urlrefresh.'";
            }',
            'buttons' => array
            (
                array('id' => 'firstback','text'=>'BACK',
                            'click'=> 'js:function(){
                                        $(this).dialog("close");
                                        window.location.href = "'.$urlrefresh.'";
                            }'),
                array('id' => 'secondback','text'=>'BACK','click'=> 'js:function(){
                                        $("#addprimarydetails").show();
                                        $("#addaboutthereward").hide();
                                        $("#addaboutreward").hide();
                                        $("#addtermsreward").hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();

                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).show();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).show();
                            }'),
                array('id' => 'thirdback','text'=>'BACK','click'=> 'js:function(){
                                        $("#addprimarydetails").hide();
                                        $("#addaboutthereward").show();
                                        $("#addaboutreward").hide();
                                        $("#addtermsreward").hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();

                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).show();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).show();
                            }'),
                array('id' => 'fourthback','text'=>'BACK','click'=> 'js:function(){
                                        $("#addprimarydetails").hide();
                                        $("#addaboutthereward").hide();
                                        $("#addaboutreward").show();
                                        $("#addtermsreward").hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();

                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).show();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).show();
                            }'),
                array('id' => 'firstnext','text'=>'NEXT','click'=> 'js:function(){
                                        var rewardid = $("#addrewardid").val();
                                        var results = validateinputs(2,1, rewardid);
                                        
                                        if(results == true){
                                                $("#addprimarydetails").hide();
                                                $("#addaboutthereward").show();
                                                $("#addaboutreward").hide();
                                                $("#addtermsreward").hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();

                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).show();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).show();
                                        } else {
                                                $("#message2").html(results);
                                                $("#messagedialog4").dialog("open");
                                        }      
                            }'),
                array('id' => 'secondnext','text'=>'NEXT','click'=> 'js:function(){
                                        var rewardid = $("#addrewardid").val();
                                        var results = validateinputs(2,2,rewardid);
                                        if(results == true){
                                                $("#addprimarydetails").hide();
                                                $("#addaboutthereward").hide();
                                                $("#addaboutreward").show();
                                                $("#edittermsreward").hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();

                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).show();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).show();
                                        } 
                            }'),
                array('id' => 'thirdnext','text'=>'NEXT','click'=> 'js:function(){
                                        var rewardid = $("#addrewardid").val();
                                        var results = validateinputs(2,3,rewardid);
                                        if(results == true){
                                                $("#addprimarydetails").hide();
                                                $("#addaboutthereward").hide();
                                                $("#addaboutreward").hide();
                                                $("#addtermsreward").show();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();

                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).show();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).show();
                                        }
                                        
                            }'),
                array('id' => 'save','text'=>'SAVE','click'=> 'js:function(){
                                        var rewardid = $("#addrewardid").val();
                                        var results = validateinputs(2,4,rewardid);
                                        if(results == true){
                                                $("#add-item-form").submit();
                                                $(this).dialog("close");
                                        }
                            }')
            ),
        ),
    ));
    
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'add-item-form',
        'enableClientValidation' => true,
        'enableAjaxValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
        'action' => $this->createUrl('manipulateReward')
    ));
?>
<?php echo CHtml::hiddenField('hdnFunctionName' , 'AddReward', array('id' => 'hdnFunctionName')); ?>
<?php echo CHtml::hiddenField('hdnRewardItemID-add' , '', array('id'=>'hdnRewardItemID-add')); ?>
<?php echo CHtml::hiddenField('addrewardid' , '', array('id'=>'addrewardid')); ?>
<table id="addprimarydetails" style="display: none;">
    <tr>
        <td colspan="2">
            <div id="add-breadcrumbs">
                <span id="title-breadcrumbs-row">Add New Reward Item</span>
                <img id="add-breadcrumbs-image" src="../../images/primarydetails.png">
            </div>
        </td>
    </tr>
    <tr><td colspan="2"></td></tr>
<!--    <tr id="rewardid-row">
        <td style="width: 250px;">Reward Type &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php // echo $form->dropDownList($model, 'addrewardid', array("1" => "Rewards e-Coupon","2" => "Raffle e-Coupon"), array('id'=>'addrewardid', 'style' => 'width: 313px; padding: 2px;')) ?>
        </td>
    </tr>-->
    <tr id="addpartner-row">
        <td style="width: 250px;">e-Games Partner &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php echo $form->dropDownList($model, 'addpartner', array("" => "Select Partner"), array('id'=>'addpartner', 'style' => 'width: 313px; padding: 2px;')) ?>
        </td>
    </tr>
    <tr>
        <td>Reward Item &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php echo $form->textField($model, 'addrewarditem', array('id'=>'addrewarditem', 'style' => 'width: 307px;')) ?>
        </td>
    </tr>
    <tr id="addcategory-row">
        <td>Reward Category &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php echo $form->dropDownList($model, 'addcategory', array("" => "Select Category"), array('id'=>'addcategory', 'style' => 'width: 313px; padding: 2px;')) ?>
        </td>
    </tr>
    <tr>
        <td>Reward Points Requirement &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php echo $form->textField($model, 'addpoints', array('id'=>'addpoints', 'style' => 'width: 307px;')) ?>
        </td>
    </tr>
    <tr>
        <td>Member Eligibility &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php echo $form->dropDownList($model, 'addeligibility', array("" => "Select Eligibility", "2" => "Regular", "3" => "VIP"), array('id'=>'addeligibility', 'style' => 'width: 313px; padding: 2px;')) ?>
        </td>
    </tr>
    <tr>
        <td>Status &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php echo $form->dropDownList($model, 'addstatus', array("" => "Select Status", "1" => "Active", "2" => "Inactive"), array('id'=>'addstatus', 'style' => 'width: 313px; padding: 2px;')) ?>
        </td>
    </tr>
    <tr>
        <td style="vertical-align: top;">Subtexts &nbsp;<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php echo $form->textArea($model, 'addsubtext', array('id'=>'addsubtext', 'rows' => 3, 'cols' => 31, 'maxlength' => 200)); ?>
        </td>
    </tr>
    <tr>
        <td style="vertical-align: top;">Promo Period &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php 
                    $yeartodate = (string)date('Y'); 
                    $maxyear = Yii::app()->params['maximum_datepicker_year']; 
                    $yearrange =  $yeartodate.':'.$maxyear;
                    $dateformat= Yii::app()->params['dateformat']; 
            ?>
            <b>From :</b>
            <?php
            $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                'name'=>'add_from_date',  
                'id'=>'add_from_date',  
                'value'=>  '', 
                 'options'=>array(
                    'showAnim'=>'fade',
                    'dateFormat'=>$dateformat,
                    'changeYear' => true,           // can change year
                    'changeMonth' => true,          // can change month
                     'yearRange' => $yearrange,
                     'minDate' => '0',
                ),
                'htmlOptions'=>array(
                    'style'=>'height:20px; width: 255px;'
                ),
            ));
            ?>
            <!--  DROPDOWNLIST FOR HRS:MINS:SECS FOR PROMO PERIOD (from)  -->
            <?php echo CHtml::dropdownlist('from_hour', '00', array("00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04",
                                                                                                                                "05" => "05", "06" => "06", "07" => "07", "08" => "08", "09" => "09",
                                                                                                                                "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14",
                                                                                                                                "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19",
                                                                                                                                "20" => "20", "21" => "21", "22" => "22", "23" => "23"),
                                                                                                                                array('style'=>'padding:3px; display: none;'));?>
            <?php echo CHtml::dropdownlist('from_min', '00', array("00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04",
                                                                                                                                "05" => "05", "06" => "06", "07" => "07", "08" => "08", "09" => "09",
                                                                                                                                "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14",
                                                                                                                                "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19",
                                                                                                                                "20" => "20", "21" => "21", "22" => "22", "23" => "23", "24" => "24",
                                                                                                                                "25" => "25", "26" => "26", "27" => "27", "28" => "28", "29" => "29",
                                                                                                                                "30" => "30", "31" => "31", "32" => "32", "33" => "33", "34" => "34",
                                                                                                                                "35" => "35", "36" => "36", "37" => "37", "38" => "38", "39" => "39",
                                                                                                                                "40" => "40", "41" => "41", "42" => "42", "43" => "43", "44" => "44",
                                                                                                                                "45" => "45", "46" => "46", "47" => "47", "48" => "48", "49" => "49",
                                                                                                                                "50" => "50", "51" => "51", "52" => "52", "53" => "53", "54" => "54",
                                                                                                                                "55" => "55", "56" => "56", "57" => "57", "58" => "58", "59" => "59"), array('style'=>'padding:3px; display: none;'));?>
            <?php echo CHtml::dropdownlist('from_sec', '00', array("00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04",
                                                                                                                                "05" => "05", "06" => "06", "07" => "07", "08" => "08", "09" => "09",
                                                                                                                                "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14",
                                                                                                                                "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19",
                                                                                                                                "20" => "20", "21" => "21", "22" => "22", "23" => "23", "24" => "24",
                                                                                                                                "25" => "25", "26" => "26", "27" => "27", "28" => "28", "29" => "29",
                                                                                                                                "30" => "30", "31" => "31", "32" => "32", "33" => "33", "34" => "34",
                                                                                                                                "35" => "35", "36" => "36", "37" => "37", "38" => "38", "39" => "39",
                                                                                                                                "40" => "40", "41" => "41", "42" => "42", "43" => "43", "44" => "44",
                                                                                                                                "45" => "45", "46" => "46", "47" => "47", "48" => "48", "49" => "49",
                                                                                                                                "50" => "50", "51" => "51", "52" => "52", "53" => "53", "54" => "54",
                                                                                                                                "55" => "55", "56" => "56", "57" => "57", "58" => "58", "59" => "59"), array('style'=>'padding:3px; display: none;'));?>
            &nbsp;<br/>
            <b>To :</b>
            <?php
            $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                'name'=>'add_to_date',  
                'id'=>'add_to_date',  
                'value'=>  '', 
                 'options'=>array(
                    'showAnim'=>'fade',
                    'dateFormat'=>$dateformat,
                    'changeYear' => true,           // can change year
                    'changeMonth' => true,          // can change month
                     'yearRange' => $yearrange,
                     'minDate' => '0',
                ),
                'htmlOptions'=>array(
                    'style'=>'disabled: true; height:20px; width: 255px; margin-top: 5px; margin-left: 18px;'
                ),
            ));
            ?>
            <!--  DROPDOWNLIST FOR HRS:MINS:SECS FOR PROMO PERIOD (to)  -->
            <?php echo CHtml::dropdownlist('to_hour', '00', array("00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04",
                                                                                                                                "05" => "05", "06" => "06", "07" => "07", "08" => "08", "09" => "09",
                                                                                                                                "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14",
                                                                                                                                "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19",
                                                                                                                                "20" => "20", "21" => "21", "22" => "22", "23" => "23"), array('style'=>'padding:3px; display: none;'));?>
            <?php echo CHtml::dropdownlist('to_min', '00', array("00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04",
                                                                                                                                "05" => "05", "06" => "06", "07" => "07", "08" => "08", "09" => "09",
                                                                                                                                "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14",
                                                                                                                                "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19",
                                                                                                                                "20" => "20", "21" => "21", "22" => "22", "23" => "23", "24" => "24",
                                                                                                                                "25" => "25", "26" => "26", "27" => "27", "28" => "28", "29" => "29",
                                                                                                                                "30" => "30", "31" => "31", "32" => "32", "33" => "33", "34" => "34",
                                                                                                                                "35" => "35", "36" => "36", "37" => "37", "38" => "38", "39" => "39",
                                                                                                                                "40" => "40", "41" => "41", "42" => "42", "43" => "43", "44" => "44",
                                                                                                                                "45" => "45", "46" => "46", "47" => "47", "48" => "48", "49" => "49",
                                                                                                                                "50" => "50", "51" => "51", "52" => "52", "53" => "53", "54" => "54",
                                                                                                                                "55" => "55", "56" => "56", "57" => "57", "58" => "58", "59" => "59"), array('style'=>'padding:3px; display: none;'));?>
            <?php echo CHtml::dropdownlist('to_sec', '00', array("00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04",
                                                                                                                                "05" => "05", "06" => "06", "07" => "07", "08" => "08", "09" => "09",
                                                                                                                                "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14",
                                                                                                                                "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19",
                                                                                                                                "20" => "20", "21" => "21", "22" => "22", "23" => "23", "24" => "24",
                                                                                                                                "25" => "25", "26" => "26", "27" => "27", "28" => "28", "29" => "29",
                                                                                                                                "30" => "30", "31" => "31", "32" => "32", "33" => "33", "34" => "34",
                                                                                                                                "35" => "35", "36" => "36", "37" => "37", "38" => "38", "39" => "39",
                                                                                                                                "40" => "40", "41" => "41", "42" => "42", "43" => "43", "44" => "44",
                                                                                                                                "45" => "45", "46" => "46", "47" => "47", "48" => "48", "49" => "49",
                                                                                                                                "50" => "50", "51" => "51", "52" => "52", "53" => "53", "54" => "54",
                                                                                                                                "55" => "55", "56" => "56", "57" => "57", "58" => "58", "59" => "59"), array('style'=>'padding:3px; display: none;'));?>
        </td>
    </tr>
     <tr id="additemcount-row">
        <td>Inventory Balance &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php echo $form->textField($model, 'additemcount', array('id'=>'additemcount', 'style' => 'width: 307px;')) ?>
        </td>
    </tr>
     <tr id="addpromoname-row"style="display: none;" >
        <td>Promo Name &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php echo $form->textField($model, 'addpromoname', array('id'=>'addpromoname', 'style' => 'width: 307px;')) ?>
        </td>
    </tr>
     <tr id="addpromocode-row"style="display: none;" >
        <td>Promo Code &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php echo $form->textField($model, 'addpromocode', array('id'=>'addpromocode', 'style' => 'width: 307px;')) ?>
        </td>
    </tr>
    <tr id="adddrawdate-row" style="display: none;">
        <td>Draw Date &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
     <?php
            $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                'name'=>'drawdate',  
                'id'=>'drawdate',  
                'value'=>  '', 
                 'options'=>array(
                    'showAnim'=>'fade',
                    'dateFormat'=>$dateformat,
                    'changeYear' => true,           // can change year
                    'changeMonth' => true,          // can change month
                     'yearRange' => $yearrange,
                     'minDate' => '0',
                ),
                'htmlOptions'=>array(
                    'style'=>'height:20px; width: 150px; margin-top: 5px;'
                ),
            ));
            ?>
            <!--  DROPDOWNLIST FOR HRS:MINS:SECS FOR DRAWDATE  -->
            <?php echo CHtml::dropdownlist('drawdate_hour', '00', array("00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04",
                                                                                                                                "05" => "05", "06" => "06", "07" => "07", "08" => "08", "09" => "09",
                                                                                                                                "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14",
                                                                                                                                "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19",
                                                                                                                                "20" => "20", "21" => "21", "22" => "22", "23" => "23"), array('style'=>'padding:3px; display: none;'));?>
            <?php echo CHtml::dropdownlist('drawdate_min', '00', array("00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04",
                                                                                                                                "05" => "05", "06" => "06", "07" => "07", "08" => "08", "09" => "09",
                                                                                                                                "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14",
                                                                                                                                "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19",
                                                                                                                                "20" => "20", "21" => "21", "22" => "22", "23" => "23", "24" => "24",
                                                                                                                                "25" => "25", "26" => "26", "27" => "27", "28" => "28", "29" => "29",
                                                                                                                                "30" => "30", "31" => "31", "32" => "32", "33" => "33", "34" => "34",
                                                                                                                                "35" => "35", "36" => "36", "37" => "37", "38" => "38", "39" => "39",
                                                                                                                                "40" => "40", "41" => "41", "42" => "42", "43" => "43", "44" => "44",
                                                                                                                                "45" => "45", "46" => "46", "47" => "47", "48" => "48", "49" => "49",
                                                                                                                                "50" => "50", "51" => "51", "52" => "52", "53" => "53", "54" => "54",
                                                                                                                                "55" => "55", "56" => "56", "57" => "57", "58" => "58", "59" => "59"), array('style'=>'padding:3px; display: none;'));?>
            <?php echo CHtml::dropdownlist('drawdate_sec', '00', array("00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04",
                                                                                                                                "05" => "05", "06" => "06", "07" => "07", "08" => "08", "09" => "09",
                                                                                                                                "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14",
                                                                                                                                "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19",
                                                                                                                                "20" => "20", "21" => "21", "22" => "22", "23" => "23", "24" => "24",
                                                                                                                                "25" => "25", "26" => "26", "27" => "27", "28" => "28", "29" => "29",
                                                                                                                                "30" => "30", "31" => "31", "32" => "32", "33" => "33", "34" => "34",
                                                                                                                                "35" => "35", "36" => "36", "37" => "37", "38" => "38", "39" => "39",
                                                                                                                                "40" => "40", "41" => "41", "42" => "42", "43" => "43", "44" => "44",
                                                                                                                                "45" => "45", "46" => "46", "47" => "47", "48" => "48", "49" => "49",
                                                                                                                                "50" => "50", "51" => "51", "52" => "52", "53" => "53", "54" => "54",
                                                                                                                                "55" => "55", "56" => "56", "57" => "57", "58" => "58", "59" => "59"), array('style'=>'padding:3px; display: none;'));?>
        </td>
    </tr>
</table>

<table id="addaboutthereward" style="display: none; ">
      <tr>
        <td colspan="3">
            <div id="add-breadcrumbs">
                <span id="title-breadcrumbs-row">Add Reward Details</span>
                <img id="add-breadcrumbs-image" src="../../images/aboutreward.png">
            </div>
        </td>
    </tr>
    <tr><td colspan="3"></td></tr>
    <tr>
        <td colspan="3">
            <div id="balancer"></div>
            <iframe id="addthblimitedframe"
                    src="<?php echo Yii::app()->createUrl('manageRewards/thumbnailLimited'); ?>" 
                    style="height: 248px; width: 160px; overflow: hidden; border: none; background: #FFFFFF;" >
            </iframe>
            <?php echo CHtml::hiddenField('thblimitedphoto', '', array('id' => 'thblimitedphoto')); ?>
            <iframe id="addthboutofstockframe"
                    src="<?php echo Yii::app()->createUrl('manageRewards/thumbnailOutofstock'); ?>" 
                    style="height: 248px; width: 160px; overflow: hidden; border: none; background: #FFFFFF;">
            </iframe>
            <?php echo CHtml::hiddenField('thboutofstockphoto', '',array('id' => 'thboutofstockphoto')); ?>
            <iframe id="addecouponframe"
                    src="<?php echo Yii::app()->createUrl('manageRewards/eCoupon'); ?>" 
                    style="height: 248px; width: 160px; overflow: hidden; border: none; background: #FFFFFF;">
            </iframe>
            <?php echo CHtml::hiddenField('ecouponphoto', '',array('id' => 'ecouponphoto')); ?>
        </td>
    </tr>
    <tr>
        <td colspan="3">
            <div id="balancer"></div>
            <iframe id="addlmlimitedframe"
                    src="<?php echo Yii::app()->createUrl('manageRewards/learnMoreLimited'); ?>" 
                    style="height: 248px; width: 160px; overflow: hidden; border: none; background: #FFFFFF;">
            </iframe>
            <?php echo CHtml::hiddenField('lmlimitedphoto', '', array('id' => 'lmlimitedphoto')); ?>
            <iframe id="addlmoutofstockframe"
                    src="<?php echo Yii::app()->createUrl('manageRewards/learnMoreOutofstock'); ?>" 
                    style="height: 248px; width: 160px; overflow: hidden; border: none; background: #FFFFFF;">
            </iframe>
            <?php echo CHtml::hiddenField('lmoutofstockphoto', '', array('id' => 'lmoutofstockphoto')); ?>
            <iframe id="addwebsliderframe"
                    src="<?php echo Yii::app()->createUrl('manageRewards/websiteSlider'); ?>" 
                    style="height: 248px; width: 160px; overflow: hidden; border: none; background: #FFFFFF;">
            </iframe>
            <?php echo CHtml::hiddenField('websliderphoto', '',array('id' => 'websliderphoto')); ?>
        </td>
    </tr>
</table>
<table id="addaboutreward" style="display: none;">
    <tr>
        <td colspan="3">
            <div id="add-breadcrumbs">
                <span id="title-breadcrumbs-row">Add Reward Details</span>
                <img id="add-breadcrumbs-image" src="../../images/aboutreward.png">
            </div>
        </td>
    </tr>
    <tr><td colspan="3"></td></tr>
    <tr>
        <td style="vertical-align: top; width: 200px;">About the Reward</td>
        <td>
            <?php echo $form->textArea($model, 'addabout', array('id' => 'addabout','style' => 'width:100%; height: 400px;')); ?>
        </td>
    </tr>
</table>
<table id="addtermsreward" style="display: none;">
    <tr>
        <td colspan="3">
            <div id="add-breadcrumbs">
                <span id="title-breadcrumbs-row">Add Reward Details</span>
                <img id="add-breadcrumbs-image" src="../../images/termsreward.png">
            </div>
        </td>
    </tr>
    <tr><td colspan="3"></td></tr>
    <tr>
        <td style="vertical-align: top; width: 200px;">Terms of the Reward</td>
        <td>
             <?php echo $form->textArea($model, 'addterms', array('id' => 'addterms','style' => 'width:100%; height: 400px;')); ?>
        </td>
    </tr>
</table>
<?php $this->endWidget(); ?>
<?php $this->endWidget('zii.widgets.jui.CJuiDialog');?>
<?php
/** ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
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
