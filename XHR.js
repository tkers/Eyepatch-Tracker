// XHR channel
// Tijn Kersjes
//
// Load external files trough an AJAX call


function XHR(){
	try{ //Firefox, Opera 8.0+, Safari
		this.call=new XMLHttpRequest();
	}
	catch(e){ //Internet Explorer
		try{
			this.call=new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch(e){
			try{
				this.call=new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch(e){
				return false;
			}
		}
	}
	return true;
}

XHR.prototype.include=function(url,id){
	this.call.field=id;
	this.call.open("GET",url,true);
	this.call.onreadystatechange=function(){
		if(this.readyState==4){
			if(this.status==200){
				this.field.innerHTML=this.responseText;
			}
		}
	}
	this.call.send(null);
}

XHR.prototype.get=function(url,callback){
	this.call.callback=callback;
	this.call.open("GET",url,true);
	this.call.onreadystatechange=function(){
		if(this.readyState==4){
			eval(this.callback+"(this)");
		}
	}
	this.call.send(null);
}

XHR.prototype.post=function(url,vars,callback){
	this.call.callback=callback;
        this.call.open('POST',url,true);
        this.call.onreadystatechange=function(){
            if(this.readyState==4){
                eval(this.callback+"(this)");
            }
        }
        this.call.setRequestHeader("If-Modified-Since", "Sat, 1 Jan 2005 00:00:00 GMT");
        this.call.setRequestHeader("Content-type","application/x-www-form-urlencoded");
        this.call.setRequestHeader("Content-length",vars.length);
        this.call.setRequestHeader("Connection","close");
	this.call.send(vars);
}

XHR.prototype.abort=function(){
	if(this.call.readyState!==4){
		this.call.abort();
	}
}