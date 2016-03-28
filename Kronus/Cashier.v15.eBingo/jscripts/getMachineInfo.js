//function getMachineInfo() {
//    var locator = new ActiveXObject ("WbemScripting.SWbemLocator");
//    var service = locator.ConnectServer(".");
//    var properties = service.ExecQuery("SELECT * FROM Win32_Processor");
//    var e = new Enumerator (properties);   
//    
//    for (;!e.atEnd();e.moveNext ()) {
//        var p = e.item (); 
//        var cpuid = p.ProcessorId;
//        var cpuname = p.SystemName ;
//        break;
//    }  
//    
//    properties = service.ExecQuery("SELECT * FROM Win32_BIOS");
//    e = new Enumerator (properties);
//    for (;!e.atEnd();e.moveNext ()) {
//        p = e.item (); 
//        var biosid = p.SerialNumber;
//        break;
//    }   
//
//    properties = service.ExecQuery("SELECT * FROM Win32_BaseBoard");
//        e = new Enumerator (properties);
//        for (;!e.atEnd();e.moveNext ()) {
//        p = e.item (); 
//        var mbid = p.SerialNumber;
//        break;
//    }   
//
//    properties = service.ExecQuery("SELECT * FROM Win32_OperatingSystem");
//    e = new Enumerator (properties);
//    for (;!e.atEnd();e.moveNext ()) {
//        p = e.item (); 
//        var osid = p.SerialNumber;
//        var oscaption = p.Caption;
//        var ossignature = p.Signature;
//        break;
//    }       
//
//    properties = service.ExecQuery("SELECT * FROM Win32_NetworkAdapterConfiguration");
//    e = new Enumerator (properties);
//    for (;!e.atEnd();e.moveNext ()) {
//        p = e.item (); 
//        if(p.MACAddress) {
//            var macid = p.MACAddress;
//            break;
//        }   
//    }       
//    document.getElementById('cpuid').value = cpuid;
//    document.getElementById('cpuname').value = cpuname;
//    document.getElementById('biosid').value = biosid;
//    document.getElementById('mbid').value= mbid;
//    document.getElementById('osid').value = osid;
//    document.getElementById('oscaption').value = oscaption;
//    document.getElementById('ossignature').value = ossignature;
//    document.getElementById('macid').value = macid; 
//}

$(document).ready(function(){
    var locator;
    try {
        locator = new ActiveXObject ("WbemScripting.SWbemLocator");
    }catch(e) {
        $.fancybox("Please use Internet Explorer",{modal:true});
        return false;
    }
    
    $.fancybox('<h2>Capturing machine info ...</h2>', {
        'modal' : true,
        onComplete : function() {
            var service = locator.ConnectServer(".");
            
            // get cpu name
            var properties = service.ExecQuery("SELECT * FROM Win32_Processor");
            var e = new Enumerator (properties);   
            for (;!e.atEnd();e.moveNext ()) {
                var p = e.item (); 
                var cpuid = p.ProcessorId;
                var cpuname = p.SystemName ;
                break;
            }  
            
            // get bios id
            properties = service.ExecQuery("SELECT * FROM Win32_BIOS");
            e = new Enumerator (properties);
               
            for (;!e.atEnd();e.moveNext ()) {
                var p = e.item (); 
                var biosid = p.SerialNumber;
                break;
            }              
            
            // get motherboard id
            properties = service.ExecQuery("SELECT * FROM Win32_BaseBoard");
            e = new Enumerator (properties);
            for (;!e.atEnd();e.moveNext ()){
                p = e.item (); 
                var mbid = p.SerialNumber;
                break;
            }            
            
            // get mac address
            properties = service.ExecQuery("SELECT * FROM Win32_NetworkAdapterConfiguration");
            e = new Enumerator (properties);
            for (;!e.atEnd();e.moveNext ()) {
                p = e.item (); 
                if(p.MACAddress) {
                    var macid = p.MACAddress;
                    break;
                }
            }
            
            // os signature
            properties = service.ExecQuery("SELECT * FROM Win32_OperatingSystem");
            e = new Enumerator (properties);
            for (;!e.atEnd();e.moveNext ()) {
                p = e.item (); 
                var osid = p.SerialNumber;
                var oscaption = p.Caption;
                var ossignature = p.Signature;
                break;
            }            
            var param = 'macid='+macid+'&cpuname='+cpuname+'&cpuid='+cpuid+'&biosid='+biosid+
                '&mbid='+mbid+'&ossignature='+ossignature+'&oscaption='+oscaption+'&osid='+osid;
            var url = 'index.php?r=storemachineinfo';
            $.ajax({
                url : url,
                data : param,
                type : 'post',
                success : function(data) {
                    setTimeout(closeFancy, 1000);
                },
                error : function(e) {
                    $.fancybox('<h2>Failed to get hardware info. Please refresh the browser</h2>',{modal:true});
                }
            });
            
        }        
    });
    
    closeFancy = function() {
        $.fancybox.close();
    }
});