/*
 * @author Jeremiah D. Lachica
 * @date January 06, 2015
 * 
 */

var colorActiveTerminal = '';
var colorNotActiveTerminal = '';
var colorIdleTerminal = '';
var activeRangeElement = '';

function loadStartUpMethods(){
    displayTerminals(1);
    changeBorderWidthToPercentage();
}

function changeTerminalsBySize(){
    var parentSize = JSON.parse($("#terminalMonitoringSize").val());
    var parentWidth = parentSize.width;
    var parentHeight = parentSize.height;
    var terminalPerPage = parentWidth * parentHeight;
    var rangeFrom = 1;
    var rangeTo = (terminalPerPage > terminalNumbers)?terminalNumbers:terminalPerPage;
    
    getAndLoadTerminals(rangeFrom, rangeTo, parentWidth, parentHeight);
    loadPagination(rangeTo);
    activatePager('range1');
}

function changeTerminalsByPage(elementID){
    var parentSize = JSON.parse($("#terminalMonitoringSize").val());
    var parentWidth = parentSize.width;
    var parentHeight = parentSize.height;
    
    var data =$("#"+elementID).data('range');
    var rangeFrom = data.rangeFrom;
    var rangeTo = data.rangeTo;
    
    getAndLoadTerminals(rangeFrom, rangeTo, parentWidth, parentHeight);
    
    deactivePager();
    activatePager(elementID);
}

function getAndLoadTerminals(rangeFrom, rangeTo, parentWidth, parentHeight){
    showLightbox(function(){
        $.ajax({
            url : urlNewTerminalMonitoring,
            type : 'post',
            data: {'rangeFrom':rangeFrom,'rangeTo':rangeTo,'parentWidth':parentWidth,'parentHeight':parentHeight},
            success : function(data) {
                var obj = JSON.parse(data);
                loadTerminals(parentWidth, parentHeight, rangeFrom, rangeTo, obj);
                hideLightbox();
            },
            error : function(e) {
                displayError(e);
            }
        });
    });
}

function displayTerminals(rangeFrom){
    var parentSize = JSON.parse($("#terminalMonitoringSize").val());
    var parentWidth = parentSize.width;
    var parentHeight = parentSize.height;
    var terminalPerPage = parentWidth * parentHeight;
    var rangeTo = (terminalPerPage > terminalNumbers)?terminalNumbers:terminalPerPage;
    loadTerminals(parentWidth, parentHeight, rangeFrom, rangeTo, terminalList);
    loadPagination(rangeTo);
    activatePager('range1');
}

function loadTerminals(parentWidth, parentHeight, rangeFrom, rangeTo, terminalObjects){
    var htmlTerminals = generateTerminals(terminalObjects);
    $("#terminals-container").html(htmlTerminals); 
    changeTerminalBoxSize(parentWidth, parentHeight);
}

function loadPagination(terminalNumberPerPage){
    var htmlPagination = generatePagination(terminalNumberPerPage);
    $("#terminalPager").html(htmlPagination);
}

//function generateTerminals(rangeFrom,rangeTo){
//    var html='';
//    for(var t=rangeFrom;t<=rangeTo;t++){
//        html += '<div id="'+t+'" class="terminal-box"><div class="pad5">'+t+'</div></div>';
//    }
//    return html;
//}
function generateTerminals(arr){
    var html='';
    
    for(var key in arr){
        if(arr.hasOwnProperty(key)){
            var obj = arr[key];
            var id = obj.TerminalID;
            var status = obj.Status;
            var classColor = '.colorInactive';
            
            
            if(status==0){classColor='colorDisable';}
            else if(status==1){classColor = 'colorActive';}
            else if(status==3){classColor='colorIdle';}
            else if(status==2 || null){classColor='colorInactive';}
            
            html += '<div id="'+id+'" class="terminal-box '+classColor+'"><div class="pad5">'+id+'</div></div>';
        }
    }
    return html;
}

function generatePagination(terminalPerPage){
    var html='';
    var remainder = terminalNumbers % terminalPerPage;
    var quantity = Math.floor(terminalNumbers / terminalPerPage);
    var pagerQuantity = (remainder)?quantity+1:quantity;
    
    var p=1;
    var pp;
    var rangeFrom=0;
    var rangeTo=0;
    var x=terminalNumbers;
    
    while(p<=pagerQuantity){
        var y = (x>=terminalPerPage)?terminalPerPage:x;
        
        rangeFrom = rangeTo + 1;
        rangeTo = rangeTo + y;
        pp = rangeFrom + '-' + rangeTo;
       
        html += "<div id='range"+p+"' class='pagerLinks' data-range='{"+'"rangeFrom":'+rangeFrom+',"rangeTo":'+rangeTo+"}' onclick='changeTerminalsByPage(this.id)'>"+pp+"</div>";
        x= x - terminalPerPage;
        p++;
    }
    return html;
}

function changeTerminalBoxSize(parentWidth, parentHeight){
    var terminalBoxWidth = calculateTerminalBoxWidth(parentWidth);
    var terminalBoxHeight = calculateTerminalBoxHeight(parentHeight);
    $(".terminal-box").css('width',terminalBoxWidth);
    
    
    if(terminalBoxHeight){
        $(".terminal-box").css('height',terminalBoxHeight);
    }
    
}

function calculateTerminalBoxWidth(width){
    var screenWidth = $('#terminals-container').width();
    var marginWidth = parseInt($(".terminal-box").css('marginLeft'));
    var borderWidth = parseInt($(".terminal-box").css('border-left-width'));
    var otherWidth = convertToPercentage(((marginWidth + borderWidth)*(2)) * width, screenWidth);
    var availableScreenWidth = (99 -otherWidth);
    var totalSize = (availableScreenWidth / width);
    return totalSize+'%';
}

function calculateTerminalBoxHeight(height){
    var screenHeight = $('#terminals-container').height();
    var marginHeight = parseInt($(".terminal-box").css('marginTop'));
    var borderHeight = parseInt($(".terminal-box").css('border-top-width'));
    var otherHeight = ((marginHeight + borderHeight)*(2))*height;
    var terminalBoxHeight = ($(".terminal-box").height());
    var totalConsumedVerticalHeight = (terminalBoxHeight * height) + otherHeight;
   
    var returnValue;
     
    if(totalConsumedVerticalHeight > screenHeight){
        var availableVerticalSpace = (screenHeight - otherHeight);
        var terminalBoxHeight = convertToPercentage((availableVerticalSpace / height), screenHeight);
        returnValue = terminalBoxHeight +'%';
    }else{
        returnValue = null;
    }
    return returnValue;
}

function changeBorderWidthToPercentage(){
    var screenWidth = $('#terminals-container').width();
    var borderWidth = parseInt($(".terminal-box").css('border-left-width'));
    var percentBorderWidth = convertToPercentage(borderWidth, screenWidth)+'%';
    $('.terminal-box').css({'borderWidth':percentBorderWidth});
}

function changeMarginWidthToPercentage(){
    var screenHeight = $('#terminals-container').height();
    var marginWidth = parseInt($(".terminal-box").css('marginLeft'));
    var percentMarginWidth = convertToPercentage(marginWidth, screenHeight);
    $('.terminal-box').css({'marginWidth':percentMarginWidth});
}

function convertToPercentage(partialAmount,totalAmount){
    return (partialAmount/totalAmount)*100;
}

function activatePager(elementID){
    $('#'+elementID).css({'background':'#666','border':'1px solid #333','color':'#FFF'});
    activeRangeElement = elementID;
}

function deactivePager(){
    $('.pagerLinks').css({'background':'none','border':'1px solid #999','color':'#666'});
}

function resizeTerminalBoxSize(){
    var parentSize = JSON.parse($("#terminalMonitoringSize").val());
    var parentWidth = parentSize.width;
    var parentHeight = parentSize.height;
    
    changeTerminalBoxSize(parentWidth, parentHeight);
}

function hoverOnPagerLinks(id){$('#'+id).css({'background':'#999','border':'1px solid #666','color':'#FFF'});}
function hoverOutPagerLinks(id){$('#'+id).css({'background':'none','border':'1px solid #999','color':'#666'});}
