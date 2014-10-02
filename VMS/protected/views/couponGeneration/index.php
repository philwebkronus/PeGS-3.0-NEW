<?php
/**
 * Coupon Generation View
 */
$this->pageTitle = Yii::app()->name." - Coupon Generation Tool";
?>
<script type="text/javascript">
    //this will disable the right click
   var isNS = (navigator.appName == "Netscape") ? 1 : 0;
   if(navigator.appName == "Netscape") document.captureEvents(Event.MOUSEDOWN||Event.MOUSEUP);
   function mischandler(){
        return false;
   }
   function mousehandler(e){
         var myevent = (isNS) ? e : event;
         var eventbutton = (isNS) ? myevent.which : myevent.button;
         if((eventbutton==2)||(eventbutton==3)) return false;
   }
   document.oncontextmenu = mischandler;
   //document.onmousedown = mousehandler;
   //document.onmouseup = mousehandler;

    $(document).ready(function(){
        var batchID = "";
        var amount = "";
        var distributiontag = "";
        var creditable = "";
        var generatedfrom = "";
        var generatedto = "";
        var generatedby = "";
        var validfrom = "";
        var validto = "";
        var status = "";
        var promoname = "";

        loadGrid(batchID, amount, distributiontag, creditable, generatedfrom, generatedto, generatedby, validfrom, validto, status, promoname);

        $("#hour-slider").slider({
            orientation: "horizontal",
            range: "min",
            min: 01,
            max: 12,
            slide: refreshTime,
            change: refreshTime

        });
        $("#minute-slider").slider({
            orientation: "horizontal",
            range: "min",
            min: 00,
            max: 59,
            slide: refreshTime,
            change: refreshTime
        });

        $("#link-edit").live('click', function(e){
            e.preventDefault();
            var batchID = $(this).attr("BatchID");

            $.ajax({
               url: "getBatchDetails",
               type: "post",
               dataType: "json",
               data: {batchID : batchID},
               success: function(data){
                   if (data.ErrorCode == 0){
                       $("#batch-id").html(data.BatchID);
                       $("#count").html(data.Count);
                       $("#amount").html(data.Amount);
                       $("#promo-name").html(data.PromoName);
                       $("#distrib-type").html(data.DistributionType);
                       $("#creditable").html(data.Creditable);
                       $("#e_status").val(data.Status);
                       $("#e_validfrom").val(data.ValidFrom);
                       $("#e_validto").val(data.ValidTo);

                       $("#dialog-edit").dialog('open');
                   }
               }
            });

        });

        $("#btnsearch").live("click", function(){
            $("#s_validfrom").val('');
            $("#s_validto").val('');
            $("#s_generatedfrom").val('');
            $("#s_generatedto").val('');

            $("#dialog-search").dialog('open');
        });

        $("#btnsearchcoupon").live("click", function(){
            $("#dialog-search-coupon").dialog('open');
        });

        $("#link-list").live("click", function(e){
           e.preventDefault();

           var sc_batchID = $(this).attr('BatchID');
           var sc_couponcode = "";
           var sc_status = "";
           var sc_transdatefrom = "";
           var sc_transdateto = "";
           var sc_site = "";
           var sc_terminal = "";
           var sc_source = "";
           var sc_promoname = "";

           $("#hdnbatchID").val(sc_batchID);
           loadCouponGrid(sc_batchID, sc_couponcode, sc_status, sc_transdatefrom, sc_transdateto, sc_site, sc_terminal, sc_source, sc_promoname);

           $("#dialog-coupon-list").dialog('open');
        });

        $("#btngenerate").live("click", function(){
            $("#g_count").val('');
            $("#g_amount").val('');
            $("#g_promoname").val('');
            $("#g_distributiontag").val('');
            $("#g_creditable").val('');
            $("#g_status").val('');
            $("#g_validfrom").val('');
            $("#g_validto").val('');

            $("#dialog-generate").dialog("open");
        });

        $("#link-export").live("click", function(e){
           e.preventDefault();
           var batchID = $(this).attr("BatchID");

           $.ajax({
               url: "getBatchDetails",
               type: "post",
               dataType: "json",
               data: {batchID : batchID},
               success: function(data){
                   if (data.ErrorCode == 0){
                       $("#exp-batch-id").html(data.BatchID);
                       $("#exp-count").html(data.Count);
                       $("#exp-amount").html(data.Amount);
                       $("#exp-promo-name").html(data.PromoName);
                       $("#exp-distrib-type").html(data.DistributionType);
                       $("#exp-creditable").html(data.Creditable);
                       $("#exp-status").html(data.StringedStatus);
                       $("#exp-validfrom").html(data.ValidFromFormatted);
                       $("#exp-validto").html(data.ValidToFormatted);

                       $("#dialog-export").dialog('open');
                   }
               }
            });
        });
    });
    function loadGrid(batchID, amount, distributiontag, creditable, generatedfrom, generatedto, generatedby, validfrom, validto, status, promoname) {
        jQuery("#list1").jqGrid("GridUnload");
        jQuery("#list1").jqGrid({
             url:'getCouponBatches',
             mtype: 'POST',
             datatype: "json",
             postData: {
                batchID : function(){
                    return batchID;
                },
                amount : function(){
                    return amount;
                },
                distributiontag : function(){
                    return distributiontag;
                },
                creditable :  function(){
                    return creditable;
                },
                generatedfrom :  function(){
                    return generatedfrom;
                },
                generatedto : function(){
                    return generatedto;
                },
                generatedby : function(){
                    return generatedby;
                },
                validfrom :  function(){
                    return validfrom;
                },
                validto : function(){
                    return validto;
                },
                status : function(){
                    return status;
                },
                promoname:  function(){
                    return promoname;
                }
             },
             colNames:['Batch ID', 'Count','Amount', 'Distribution Type', 'Creditable?', 'Date Generated', 'Generated By',  'Valid From Date', 'Valid To Date', 'Status', 'Promo Name',  'Management', 'Date Updated', 'Updated By'],
             colModel:[
                 {name:'BatchID',index:'BatchID', width:100, align:"center"},
                 {name:'Count',index:'Count', width:120, align:"center"},
                 {name:'Amount',index:'Amount', width:150, align:"right"},
                 {name:'DistributionType',index:'DistributionType', width:130, align:"center"},
                 {name:'Creditable',index:'Creditable', width:140, align:"center"},
                 {name:'DateGenerated',index:'DateGenerated', width:160, align:"center"},
                 {name:'GeneratedBy',index:'GeneratedBy', width:160, align:"center"},
                 {name:'ValidFromDate',index:'ValidFromDate', width:160, align:"center"},
                 {name:'ValidToDate',index:'ValidToDate', width:160, align:"center"},
                 {name:'Status',index:'Status', width:160, align:"center"},
                 {name:'PromoName',index:'PromoName', width:170, align:"center"},
                 {name:'Management',index:'Management', width:240, align:"center"},
                 {name:'DateUpdated',index:'DateUpdated', width:170, align:"center"},
                 {name:'UpdatedByAID',index:'UpdatedByAID', width:160, align:"center"}
             ],
             rowNum:10,
             rowList:[10,20,30],
             height: '300',
             pager: '#pager1',
             shrinkToFit: false,
             scrollerbar : true, 
             sortname: 'id', 
             viewrecords: true, 
             loadonce: false, 
             sortorder: "desc", 
             hidegrid : false,
             caption: "Coupon Batch List"
        });
        jQuery("#list1").jqGrid('setGridWidth', '900');
        jQuery("#list1").jqGrid('navGrid','#pager1',{edit:false,add:false,del:false,search:false});
    }
    function loadCouponGrid(batchID, couponcode, status, transdatefrom, transdateto, site, terminal, source, promoname) {
        jQuery("#list2").jqGrid("GridUnload");
        jQuery("#list2").jqGrid({
             url:'getCoupons',
             mtype: 'POST',
             datatype: "json",
             postData: {
                batchID : function(){
                    return batchID;
                },
                couponcode : function(){
                    return couponcode;
                },
                status : function(){
                    return status;
                },
                transdatefrom : function(){
                    return transdatefrom;
                },
                transdateto : function(){
                    return transdateto;
                },
                site : function(){
                    return site;
                },
                terminal : function(){
                    return terminal;
                },
                source : function(){
                    return source;
                },
                promoname : function(){
                    return promoname;
                }
             },
             colNames:['Batch ID', 'Coupon ID', 'Coupon Code', 'Amount', 'Distribution Type', 'Creditable?', 'Date Generated', 'Generated By',  'Valid From Date', 'Valid To Date', 'Status', 'Site',  'Terminal', 'Source', 'Transaction Date', 'Promo Name', 'Date Reimbursed', 'Reimbursed By'],
             colModel:[
                 {name:'BatchID',index:'Count', width:120, align:"center"},
                 {name:'CouponID',index:'BatchID', width:100, align:"center"},
                 {name:'CouponCount',index:'Amount', width:150, align:"center"},
                 {name:'Amount',index:'Management', width:140, align:"right"},
                 {name:'DistributionType',index:'DistributionType', width:130, align:"center"},
                 {name:'Creditable',index:'Creditable', width:140, align:"center"},
                 {name:'DateGenerated',index:'DateGenerated', width:160, align:"center"},
                 {name:'GeneratedBy',index:'GeneratedBy', width:160, align:"center"},
                 {name:'ValidFromDate',index:'ValidFromDate', width:160, align:"center"},
                 {name:'ValidToDate',index:'ValidToDate', width:160, align:"center"},
                 {name:'Status',index:'Status', width:160, align:"center"},
                 {name:'PromoName',index:'PromoName', width:170, align:"center"},
                 {name:'DateUpdated',index:'DateUpdated', width:170, align:"center"},
                 {name:'UpdatedByAID',index:'UpdatedByAID', width:160, align:"center"},
                 {name:'UpdatedByAID',index:'UpdatedByAID', width:160, align:"center"},
                 {name:'UpdatedByAID',index:'UpdatedByAID', width:160, align:"center"},
                 {name:'UpdatedByAID',index:'UpdatedByAID', width:160, align:"center"},
                 {name:'UpdatedByAID',index:'UpdatedByAID', width:160, align:"center"}
             ],
             rowNum:10,
             rowList:[10,20,30],
             height: '300',
             pager: '#pager2',
             shrinkToFit: false,
             scrollerbar : true, 
             sortname: 'id', 
             viewrecords: true, 
             loadonce: false, 
             sortorder: "desc", 
             caption: "Coupon Listing"
        });
        jQuery("#list2").jqGrid('setGridWidth', '940');
        jQuery("#list2").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false,search:false});
    }
    function refreshTime(){
        var hour = $("#hour-slider").slider("value");
        var minute = $("#minute-slider").slider("value");

        $("#time").html(hour+":"+minute);
    }

    function checkDateRange(from, to, field) {
        if (field == "validity") {
            var d = "Valid ";
        }
        else if (field == "generated") {
            var d = "Generated ";
        }
        if (from <= to) {
            if (from != "" && to == "") {
                $("#alert-box").dialog("open");
                $("dlgmessage").html("Please enter" + d + "To Date.");
            }
            else if (from == "" && to != "") {
                $("#alert-box").dialog("open");
                $("dlgmessage").html("Please enter" + d + "From Date.");
            }
            else {
                return true;
            }
        }
        else {
            $("#alert-box").dialog("open");
            $("#dlgmessage").html("Invalid Date range.");
            return false;
        }
    }

    function generateCoupons(count, amount, promoname, distribtype, creditable, status, validfrom, validto, confirmed) {
        $("#tblmessage").html("");

        $(".modal_load").show();
        $.ajax({
            url : "generateCoupons",
            type : "post",
            dataType : "json",
            data: {
                count : count,
                amount : amount,
                promoname : promoname,
                distribtype : distribtype,
                creditable : creditable,
                status : status,
                validfrom : validfrom,
                validto : validto,
                confirmed : confirmed
            },
            success : function(data){
                if (data.Confirmed == 0) {
                    if (data.ErrorCode == 0){
                        $("#confirm-generation").dialog("open");

                        $("#confirm_count").html(data.Count);
                        $("#confirm_amount").html(data.Amount);
                        $("#confirm_distribtag").html(data.DistributionType);
                        $("#confirm_creditable").html(data.Creditable);
                        $("#confirm_promoname").html(data.PromoName);
                        $("#confirm_status").html(data.Status);
                        $("#confirm_validfrom").html(data.ValidFrom);
                        $("#confirm_validto").html(data.ValidTo);
                        $("#orig_validfrom").val(data.ValidFromDate);
                        $("#orig_validto").val(data.ValidToDate);
                    }
                    else {
                        $("#alert-box").dialog("open");
                        $("#dlgmessage").html(data.Message);
                    }
                }
                else {
                    if (data.ErrorCode == 0) {
                        $("#alert-box").dialog("option", "buttons", {
                           "OK" : function(){
                               $("#alert-box").dialog("close");
                               $("#dialog-generate").dialog("close");
                               $("#confirm-generation").dialog("close");
                               $("#list1").setGridParam({datatype:"json", page:1}).trigger("reloadGrid");

                               location.reload();
                           }
                        });
                        $("#alert-box").dialog("option", "width", 400);
                        $("#alert-box").dialog("option", "title", "Coupon Generation");
                        $("#dlgmessage").html("Coupon has been generated with the following info:");
                        $("#tblmessage").append("<table>" +
                                            "<tr>" +
                                                "<td class='edit-lbl'>" +
                                                    "<b>Count: </b>" +
                                                "</td>" +
                                                "<td>" +
                                                    data.Count +
                                                "</td>" +
                                            "</tr><tr>" +
                                                "<td class='edit-lbl'>" +
                                                    "<b>Amount: </b>" +
                                                "</td>" +
                                                "<td>" +
                                                    data.Amount +
                                                "</td>" +
                                            "</tr><tr>" +
                                                "<td class='edit-lbl'>" +
                                                    "<b>Promo Name: </b>" +
                                                "</td>" +
                                                "<td>" +
                                                    data.PromoName +
                                                "</td>" +
                                            "</tr><tr>" +
                                                "<td class='edit-lbl'>" +
                                                    "<b>Distribution Type: </b>" +
                                                "</td>" +
                                                "<td>" +
                                                    data.DistributionType +
                                                "</td>" +
                                            "</tr><tr>" +
                                                "<td class='edit-lbl'>" +
                                                    "<b>Creditable? </b>" +
                                                "</td>" +
                                                "<td>" +
                                                    data.Creditable +
                                                "</td>" +
                                            "</tr><tr>" +
                                                "<td class='edit-lbl'>" +
                                                    "<b>Status: </b>" +
                                                "</td>" +
                                                "<td>" +
                                                    data.Status +
                                                "</td>" +
                                            "</tr><tr>" +
                                                "<td class='edit-lbl'>" +
                                                    "<b>Valid From: </b>" +
                                                    "</td>" +
                                                "<td>" +
                                                    data.ValidFrom +
                                                "</td>" +
                                            "</tr><tr>" +
                                                "<td class='edit-lbl'>" +
                                                    "<b>Valid To: </b>" +
                                                "</td>" +
                                                "<td>" +
                                                    data.ValidTo +
                                                "</td>" +
                                            "</tr><tr>" +
                                      "</table>");
                        $("#alert-box").dialog("open");
                    }
                    else if (data.ErrorCode == 2) {
                        $("#alert-box").dialog("option", "buttons", {
                           "Continue" : function(){
                               var remainingcount = data.RemainingCount;
                               var amount = data.Amount;
                               var batchID = data.CouponBatchID;
                               var creditable = data.Creditable;
                               var status = data.Status;
                               var validfrom = data.ValidFromDate;
                               var validto = data.ValidToDate;
                               
                               regenerate(remainingcount, amount, batchID, creditable, status, validfrom, validto);

                               $(this).dialog("close");
                           }
                        });
                        $("#dlgmessage").html(data.Message);
                    }
                    else {
                        $("#alert-box").dialog("option", "buttons", {
                           "OK" : function(){
                               $(this).dialog("close");
                           }
                        });
                        $("#dlgmessage").html(data.Message);
                    }
                    $("#alert-box").dialog("open");
                }
                $(".modal_load").hide();
            }
         });
    }
    function regenerate(remainingcount, amount, batchID, creditable, status, validfrom, validto) {
         $(".modal_load").show();
         
         $.ajax({
            url : 'regenerateCoupons',
            type : 'post',
            dataType : 'json',
            data : {remainingcount : remainingcount,
                    batchID : batchID,
                    amount : amount,
                    creditable : creditable,
                    status : status,
                    validfrom : validfrom,
                    validto : validto
            },
            success : function(data) {
                if (data.ErrorCode == 0) {
                     $("#alert-box").dialog("option", "buttons", {
                        "OK" : function(){
                            $(this).dialog("close");
                            $("#dialog-generate").dialog("close");
                            $("#confirm-generation").dialog("close");
                            $("#list1").setGridParam({datatype:"json", page:1}).trigger("reloadGrid");
                        }
                     });
                     $("#alert-box").dialog("option", "width", 400);
                     $("#dlgmessage").html("Coupon has been generated with the following info:");
                     $("#tblmessage").append("<table>" +
                                            "<tr>" +
                                                "<td class='edit-lbl'>" +
                                                    "<b>Batch ID: </b>" +
                                                "</td>" +
                                                "<td>" +
                                                    data.CouponBatchID +
                                                "</td>" +
                                            "</tr><tr>" +
                                                "<td class='edit-lbl'>" +
                                                    "<b>Count: </b>" +
                                                "</td>" +
                                                "<td>" +
                                                    data.Count +
                                                "</td>" +
                                            "</tr><tr>" +
                                                "<td class='edit-lbl'>" +
                                                    "<b>Amount: </b>" +
                                                "</td>" +
                                                "<td>" +
                                                    data.Amount +
                                                "</td>" +
                                            "</tr><tr>" +
                                                "<td class='edit-lbl'>" +
                                                    "<b>Promo Name: </b>" +
                                                "</td>" +
                                                "<td>" +
                                                    data.PromoName +
                                                "</td>" +
                                            "</tr><tr>" +
                                                "<td class='edit-lbl'>" +
                                                    "<b>Distribution Type: </b>" +
                                                "</td>" +
                                                "<td>" +
                                                    data.DistributionType +
                                                "</td>" +
                                            "</tr><tr>" +
                                                "<td class='edit-lbl'>" +
                                                    "<b>Creditable? </b>" +
                                                "</td>" +
                                                "<td>" +
                                                    data.Creditable +
                                                "</td>" +
                                            "</tr><tr>" +
                                                "<td class='edit-lbl' colspan='2'>" +
                                                    "<br />" +
                                                    "Has been updated with the following info: <br /><br />" +
                                                "</td>" +
                                            "</tr><tr>" +
                                            "<td class='edit-lbl'>" +
                                                    "<b>Status: </b>" +
                                                "</td>" +
                                                "<td>" +
                                                    data.Status +
                                                "</td>" +
                                            "</tr><tr>" +
                                                "<td class='edit-lbl'>" +
                                                    "<b>Valid From: </b>" +
                                                "</td>" +
                                                "<td>" +
                                                    data.ValidFromDate +
                                                "</td>" +
                                            "</tr><tr>" +
                                                "<td class='edit-lbl'>" +
                                                    "<b>Valid To: </b>" +
                                                "</td>" +
                                                "<td>" +
                                                    data.ValidToDate +
                                                "</td>" +
                                            "</tr><tr>" +
                                      "</table>");
                 }
                 else if (data.ErrorCode == 2) {
                     $("#alert-box").dialog("option", "buttons", {
                        "Continue" : function(){
                            var remainingcount = data.RemainingCount;
                            var amount = data.Amount;
                            var batchID = data.CouponBatchID;
                            var creditable = data.Creditable;
                            var status = data.Status;
                            var validfrom = data.ValidFromDate;
                            var validto = data.ValidToDate;

                            regenerate(remainingcount, amount, batchID, creditable, status, validfrom, validto);

                            $(this).dialog("close");
                        }
                     });
                     $("#dlgmessage").html(data.Message);
                 }
                 else {
                     $("#alert-box").dialog("option", "buttons", {
                        "OK" : function(){
                            $(this).dialog("close");
                            $("#dialog-generate").dialog("close");
                            $("#confirm-generation").dialog("close");

                            location.reload();
                        }
                     });
                     $("#dlgmessage").html(data.Message);
                 }
                 $("#alert-box").dialog("open");
                 $(".modal_load").hide();
            }
        });
     }
     function updateCouponBatch(batchID, status, validfrom, validto) {
        $.ajax({
           url : "updateCouponBatch",
           type : "post",
           dataType : "json",
           data : {batchID : batchID,
                   status : status,
                   validfrom : validfrom,
                   validto : validto},
           success:  function (data){
               if (data.ErrorCode == 0) {
                   $("#alert-box").dialog("option", "buttons", {
                      "OK" : function(){
                          $("#alert-box").dialog("close");
                          $("#dialog-edit").dialog("close");
                          $("#dialog-edit-confirm").dialog("close");

                          location.reload();
                      }
                   });
                   $("#alert-box").dialog("option", "title", "Coupon Change Status AND/OR Validity");
                   $("#alert-box").dialog("option", "width", 450);
                   $("#alert-box").dialog("option", "height", 500);

                   $("#alert-box").dialog("open");
                   $("#dlgmessage").html("Coupon with the following info: ");
                   $("#alert-box").append("<table>" +
                                                "<tr>" +
                                                    "<td class='edit-lbl'>" +
                                                        "<b>Batch ID: </b>" +
                                                    "</td>" +
                                                    "<td>" +
                                                        data.CouponBatchID +
                                                    "</td>" +
                                                "</tr><tr>" +
                                                    "<td class='edit-lbl'>" +
                                                        "<b>Count: </b>" +
                                                    "</td>" +
                                                    "<td>" +
                                                        data.Count +
                                                    "</td>" +
                                                "</tr><tr>" +
                                                    "<td class='edit-lbl'>" +
                                                        "<b>Amount: </b>" +
                                                    "</td>" +
                                                    "<td>" +
                                                        data.Amount +
                                                    "</td>" +
                                                "</tr><tr>" +
                                                    "<td class='edit-lbl'>" +
                                                        "<b>Promo Name: </b>" +
                                                    "</td>" +
                                                    "<td>" +
                                                        data.PromoName +
                                                    "</td>" +
                                                "</tr><tr>" +
                                                    "<td class='edit-lbl'>" +
                                                        "<b>Distribution Type: </b>" +
                                                    "</td>" +
                                                    "<td>" +
                                                        data.DistributionTagID +
                                                    "</td>" +
                                                "</tr><tr>" +
                                                    "<td class='edit-lbl'>" +
                                                        "<b>Creditable? </b>" +
                                                    "</td>" +
                                                    "<td>" +
                                                        data.Creditable +
                                                    "</td>" +
                                                "</tr><tr>" +
                                                    "<td class='edit-lbl' colspan='2'>" +
                                                        "<br />" +
                                                        "Has been updated with the following info: <br /><br />" +
                                                    "</td>" +
                                                "</tr><tr>" +
                                                "<td class='edit-lbl'>" +
                                                        "<b>Status: </b>" +
                                                    "</td>" +
                                                    "<td>" +
                                                        data.Status +
                                                    "</td>" +
                                                "</tr><tr>" +
                                                    "<td class='edit-lbl'>" +
                                                        "<b>Valid From: </b>" +
                                                    "</td>" +
                                                    "<td>" +
                                                        data.ValidFromDate +
                                                    "</td>" +
                                                "</tr><tr>" +
                                                    "<td class='edit-lbl'>" +
                                                        "<b>Valid To: </b>" +
                                                    "</td>" +
                                                    "<td>" +
                                                        data.ValidToDate +
                                                    "</td>" +
                                                "</tr><tr>" +
                                          "</table>");
               }
               else {
                   $("#alert-box").html("");
                   $("#alert-box").dialog("open");
                   $("#alert-box").dialog("option", "title", "Coupon Change Status AND/OR Validity");
                   $("#alert-box").dialog("option", "width", 400);
                   $("#alert-box").dialog("option", "height", 200);
                   $("#alert-box").append(data.Message);
                   $("#alert-box").dialog("option", "buttons", {
                      "OK" : function(){
                          location.reload();
                      }
                   });
               }

           }
        });
     }
     function searchCouponBatch() {
        var batchID = $("#s_batchid").val();
        var amount = $("#s_amount").val();
        var distributiontag = $("#s_distributiontag").val();
        var creditable = $("#s_creditable").val();
        var generatedfrom = $("#s_generatedfrom").val();
        var generatedto = $("#s_generatedto").val();
        var generatedby = $("#s_generatedby").val();
        var validfrom = $("#s_validfrom").val();
        var validto = $("#s_validto").val();
        var status = $("#s_status").val();
        var promoname = $("#s_promoname").val();

        var result1 = checkDateRange(generatedfrom, generatedto);
        var result2 = checkDateRange(validfrom, validto);
        if (result1 == false) {
            generatedfrom = "";
            generatedto = "";
        }
        if (result2 == false) {
            validfrom = "";
            validto = "";
        }
        if (result1 && result2) {
            $.ajax({
                url : "getCouponBatches",
                type : "post",
                data : {stop : stop},
            });
            $("#dialog-search").dialog("close");
        }
        loadGrid(batchID, amount, distributiontag, creditable, generatedfrom, generatedto, generatedby, validfrom, validto, status, promoname);
     }
     function quickSearch(event) {
         if (event.keyCode == 13) {
             searchCouponBatch();
         }
     }
</script>
<style>
    .mgmt{
        margin-left: 20px;
        margin-right: 20px;
    }
    .edit-lbl{
        vertical-align: top;
        width: 150px;
    }
</style>
<h2>Coupon Generation Tool</h2>
<hr style="color:#000;background-color:#000;">
<div style="float:right">
    <?php echo CHtml::button('Generate Coupon', array('id' => 'btngenerate', 'title' => 'Generate Coupon')); ?> &nbsp; &nbsp;
    <?php echo CHtml::button('Search', array('id' => 'btnsearch', 'title' => 'Search')); ?> &nbsp; &nbsp;
</div>
<div class="clear"></div>
<br />

<table id="list1"></table>
<div id="pager1"></div>
<br /><br />
<?php
$open = false;
$exportmsg = "";
$exporttbl = "";
$width = 350;
$btnfx = "js:function(){ $(this).dialog('close'); }";
if (isset($this->exportTrue))
{
    $open = true;
    $width = 400;
    $exportmsg = "The batch of coupon with the following info has been exported to filename "."\"CouponBatchID".$this->batchID."_".date("mdy").".csv\" <br />";
    $exporttbl = "<table>
                     <tr>
                        <td style='width: 150px;'>BatchID: </td>
                        <td>".$this->batchID."</td>
                     </tr>
                     <tr>
                        <td style='width: 150px;'>Count: </td>
                        <td>".$this->count."</td>
                     </tr>
                     <tr>
                        <td style='width: 150px;'>Amount: </td>
                        <td>".$this->amount."</td>
                     </tr>
                     <tr>
                        <td style='width: 150px;'>Promo Name: </td>
                        <td>".$this->promoname."</td>
                     </tr>
                     <tr>
                        <td style='width: 150px;'>Distribution Type: </td>
                        <td>".$this->distribtype."</td>
                     </tr>
                     <tr>
                        <td style='width: 150px;'>Creditable? </td>
                        <td>".$this->creditable."</td>
                     </tr>
                     <tr>
                        <td style='width: 150px;'>Status: </td>
                        <td>".$this->status."</td>
                     </tr>
                     <tr>
                        <td style='width: 150px;'>BatchID: </td>
                        <td>".$this->batchID."</td>
                     </tr>
                     <tr>
                        <td style='width: 150px;'>Valid From: </td>
                        <td>".$this->validfrom."</td>
                     </tr>
                     <tr>
                        <td style='width: 150px;'>Valid To: </td>
                        <td>".$this->validto."</td>
                     </tr>
                  </table>
                ";
    $btnfx = "js:function(){
                    $(this).dialog('close');
                    $('#hdnbatchID').val(".$this->batchID.");
                    $('#dl-form').submit();
                    $('#list1').setGridParam({datatype:'json', page:1}).trigger('reloadGrid');
              }";
}
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'alert-box',
    'options'=>array(
        'title' => 'COUPON GENERATION',
        'autoOpen' => $open,
        'modal' => true,
        'resizable' => false,
        'draggable' => false,
        'show' => 'fade',
        'hide' => 'fade',
        'width' => $width,
        'open'=>'js:function(event, ui) { $(".ui-dialog-titlebar-close").hide(); }',
        'close' => 'clear',
        'buttons' => array
        (
            'OK'=> $btnfx,
        ),
    ),
));
?>
<p id="dlgmessage"><?php echo $exportmsg; ?></p>
<span id="tblmessage"><?php echo $exporttbl; ?></span>
<?php
$this->endWidget('zii.widgets.jui.CJuiDialog');
?>
<?php $this->renderPartial('_searchbatch', array('model' => $model)); ?>
<?php $this->renderPartial('_edit', array('model' => $model)); ?>
<?php $this->renderPartial('_searchcoupon', array('model' => $model, 'sitelist' => $sitelist)); ?>
<?php $this->renderPartial('_list', array('model' => $model)); ?>
<?php $this->renderPartial('_generate', array('model' => $model)); ?>
<?php $this->renderPartial('_export', array('model' => $model)); ?>

<!---export to p----->
<?php
echo CHtml::form('download', 'post', array('id' => 'dl-form'));
echo CHtml::hiddenField('batchID', '', array('id' => 'hdnbatchID'));
echo CHtml::endForm();
?>
<div class="modal_load"></div>

