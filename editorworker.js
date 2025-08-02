addEventListener("message",event=>{
	let rdata=event.data;
	switch(rdata.action){
		case "submit":
			postMessage(rdata);
			let darr=[];
			for(let m in rdata.data){
				darr.push(m+"="+rdata.data[m]);
			}
			postMessage(darr.join("&"));
			var xhr = new XMLHttpRequest();
			xhr.open(rdata.method, rdata.url, true);
			xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

			xhr.onreadystatechange = function() { // Call a function when the state changes.
			    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
			        try{
			        	postMessage(JSON.parse(this.responseText));
			        }catch(e){
			        	postMessage({error:"Parse error: Invalid Json form server"});
			        }
			    }else if(this.status !== 200 && this.status !== 301 && this.status !== 302){
			    	postMessage({error:"Server reply with "+this.status+": "+this.responseText});
			    }
			}
			xhr.send(darr.join("&"));

			/*fetch(rdata.url,{method:rdata.method,redirect: 'error',mode: 'cors',cache: 'default',headers: {'Content-Type': 'application/json',}, body: JSON.stringify(rdata.data)})
			.then(res=>res.text())
			.then(tt=>{postMessage(tt);console.log(tt)});*/
			break;
	}
});