$.fn.dataTable.ext.buttons.mecreate = {
    text: 'Create',
    action: function ( e, dt, node, config ) {
        console.log(dt);
        config.editor.add();
    }
};

$.fn.dataTable.ext.buttons.meupdate = {
    text: 'Update',
    attr:{
        disabled:''
    },
    className:'btn btn-secondary button-akdisabled',
    action: function ( e, dt, node, config ) {
        table=$(config.editor.tableId).DataTable().search('').draw();
        data=table.rows( { selected: true } ).data()[0];
        config.editor.update(data);
    }
};

$.fn.dataTable.ext.buttons.melink = {
    text: 'New',
    href:'',
    action: function ( e, dt, node, config ) {
        window.location.assign(config.href);
    }
};

$.fn.dataTable.ext.buttons.medelete = {
    text: 'Remove',
    attr:{
        disabled:''
    },
    className:'btn btn-secondary button-akdisabled',
    action: function ( e, dt, node, config ) {
        table=$(config.editor.tableId).DataTable().search('').draw();
        data=table.rows( { selected: true } ).data()[0];
        config.editor.remove(data);
        console.log(data);
    }
};


class KCSLEditor{
    constructor(options={}){
        this.url=options.ajax || window.location.href || '';
        var that=this;
        this.fields=options.fields || [];
        this.tableId=options.table;
        this.title=options.title || "Editor";
        this.options={};
        this.doptions={};
        
        let worker=new Worker("static/js/editorworker.js");
        
        worker.addEventListener("message",event=>{
            let rdata=event.data;
            //console.log(rdata);
            if((rdata.error !== undefined && rdata.error !== null) && rdata.error.length>0){
                alert(rdata.error);
            }else if((rdata.error !== undefined && rdata.error !== null) && rdata.error.length === 0){
                console.log(rdata.options);
                that.doptions=rdata.options; 
            }
        });
        //console.log(field);
        worker.postMessage({url:this.url,method:"POST",data:{'KACTION':'options'},action:"submit"});


        setTimeout(function(){
            that.table=$(that.tableId).DataTable().search('').draw();
            that.table.on("select",function(e, dt, type, indexes){
                that.selectid=that.table.rows( { selected: true } ).data()[0]['DT_RowId'].split('_')[1];
                that.table.buttons( ['.button-akdisabled'] ).enable(
                    that.table.rows( { selected: true } ).indexes().length === 0 ?
                        false :
                        true
                );
            });
            that.table.on("deselect",function(e, dt, type, indexes){
                that.selectid=null
                that.table.buttons( ['.button-akdisabled'] ).enable(
                    that.table.rows( { selected: true } ).indexes().length === 0 ?
                        false :
                        true
                );
            });
            that.table.on("xhr.dt",function(e,settings,data){
                if(data !== null){that.options=data.options};
            });
        },1000)
    }
    getElemetHeader(){
       let str='';
        str+='<div class="modal fade KDTD" role="dialog">';
        str+='<div class="modal-dialog">';
        str+='<div class="modal-content">';
        str+='<div class="modal-header">';
        str+='<div class="h4">'+this.title+'</div>';
        str+='<span class="progress p-2 pb-4 d-none"></span><button type="button" class="close" data-dismiss="modal">&times;</button>';
        str+='</div>';
        str+='<div class="modal-body">';
        str+='<form role="form" class="form-horizontal KDTDform" method="post">';
        return str; 
    }
    getElemetFooter(){
        let str='</form>';
        str+='</div>';
        str+='<div class="modal-footer">';
        str+='<button type="submit" class="btn btn-default btn-default pull-left" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> Cancel</button>';
        str+='</div>';
        str+='</div>';
        str+='</div>';
        str+='</div>';
        return str;
    }
    getAction(action){
        let str='<div class="form-group">';
        str+=this.getField({type:"hidden",name:"KACTION"},{KACTION:action});
        str+="</div>";
        return str;
    }
    getSubmit(submit){
        let str='<div class="form-group">';
        str+=this.getField({type:"submit",className:"btn btn-primary",name:"submit"},{submit:submit});
        str+="</div>";
        return str;
    }
    add(){
        let mdl=document.createElement("div");
        mdl.setAttribute("class","container");
        let that=this;
        let str=this.getElemetHeader();
        str+=this.getAction("create");
        this.fields.forEach(field=>{
            str+='<div class="form-group">';
            str+=that.getField(field);
            str+="</div>";
        })
        str+=this.getSubmit("Create");

        str+=this.getElemetFooter();
        mdl.innerHTML=str;

        document.body.appendChild(mdl);
        this.events(mdl);
    }
    events(mdl){
        let that=this;
        $(".KDTD").modal();
        $(".KDTD").on('hidden.bs.modal', function(){
            document.body.removeChild(mdl);
        });
        $(".KDTDform").on("submit",function(){
            event.stopPropagation();
            event.preventDefault();
            let subdata={};
            $(".KDTDform").serialize().split("&").forEach(re=>{
                let ret=re.split("=");
                subdata[ret[0]]=ret[1];
            });
            that.submit(subdata);
            return false;
        });
        $(".KDTDUploader").on("change",function(){
            let inname=$(this).attr("data-name");
            let multi=$(this).attr("data-multi");
            let elem=this;
            var fd = new FormData();
            var files = $(this)[0].files[0];
            fd.append(inname, files);
            fd.append("feild", inname);
            fd.append("KACTION", "upload");
        
            $.ajax({
                url: that.url,
                type: 'post',
                data: fd,
                contentType: false,
                processData: false,
                success: function(response){
                    response=JSON.parse(response);
                    //console.log(response);
                    if(response.error.length>0){
                        let dv=document.createElement("div");
                        dv.classList.add("alert")
                        dv.classList.add("text-danger")
                        dv.classList.add("alert-light")
                        dv.innerHTML=response.error+"<span class='close fa fa-times' data-dismiss='alert'></span>";
                        elem.parentElement.appendChild(dv);
                    }else{
                        if(multi){

                        }else{
                            elem.style.display="none";
                        }
                        that.previewFile(response.uploadedfilepath,elem,multi);
                    }
                }
            });
        });
    }
    previewFile(fpath,elem,multi){
        let fparts=fpath.split(".");
        let ext=fparts[fparts.length-1];
        let dv=document.createElement("div");
        let instr="";
        if(multi){
            instr="<input type='hidden' value='"+fpath+"' name='"+$(elem).attr("data-name")+"[]'>";
        }else{
            instr="<input type='hidden' value='"+fpath+"' name='"+$(elem).attr("data-name")+"'>";
        }
        instr+="<span class='btn-sm fa fa-times' style='cursor:pointer'></span>"
        switch(ext){
            case "png":
            case "jpg":
            case "jpeg":
            case "tif":
            case "pdf":
            case "mp4":
            case "mp3":
            case "webm":
                dv.innerHTML="<embed src='uploads/"+fpath+"' style='height:300px;width:90%'>"+instr;
                break;
            default:
                dv.innerHTML=fpath+instr;
        }
        elem.parentElement.appendChild(dv);
    }
    getData(subdata){
        let mdata={};
        this.fields.forEach(field=>{
            field.type=field.type || "text";

            if(field.type !== "editor"){
                field.name=field.name || field.label;
                field.altname=field.altname || field.name
                mdata[field.altname]=subdata[field.name] || "";
            }
        })
        mdata['KACTION']=subdata["KACTION"];
        return mdata;
    }
    submit(subdata){
        $(".KDTDform").parent().parent().find(".progress").toggleClass("d-none").text("Processing...");
        let mdata={}
        let that=this;
        let edfilds=[];
        let subforms=[]
        this.fields.forEach(field=>{
            field.type=field.type || "text";

            if(field.type !== "editor" && field.type !== "subform"){
                field.name=field.name || field.label;
                field.altname=field.altname || field.name
                mdata[field.altname]=subdata[field.name] || "";
            }else if(field.type === "editor"){
                edfilds.push(field);
            }else{
                subforms.push(field);
            }
        });
        if(edfilds.length>0){
            let peflds=edfilds;
            let redfs=0;
            let rqedfs=edfilds.length;
            while(peflds.length>0){
                let field=peflds[0];
                peflds.splice(0,1);
                let eddata=field.editor.getData(subdata);
                //console.log(eddata);
                
                let worker=new Worker("static/js/editorworker.js");
                
                worker.addEventListener("message",event=>{
                    let rdata=event.data;
                    //console.log(rdata);
                    if((rdata.error !== undefined && rdata.error !== null) && rdata.error.length>0){
                        that.wait=false;
                        that.stop=true;
                        alert(rdata.error);
                        redfs++;
                    }else if((rdata.error !== undefined && rdata.error !== null) && rdata.error.length === 0){
                      that.wait=false;
                      that.stop=false;
                      mdata[field.rel]=rdata.lastId; 
                      redfs++ 
                    }
                    if (redfs == rqedfs) {
                        if(that.stop){
                            return;
                        }
                        this.finishSubmittion(mdata,subdata,subforms);
                    }
                });
                //console.log(field);
                worker.postMessage({url:field.editor.url,method:"POST",data:eddata,action:"submit"});
            }
        }else{
            this.finishSubmittion(mdata,subdata,subforms);
        }
    }
    finishSubmittion(mdata,subdata,subforms){
        let that=this;
        mdata['KACTION']=subdata["KACTION"];
        if (subdata.id !== undefined && subdata.id !== null) {
            mdata["id"]=subdata.id;
        }
        //console.log(mdata);
        let darr=[];
        for(let m in mdata){
            darr.push(m+"="+mdata[m]);
        }
        //console.log(darr.join("&"));
        $(".KDTDform").parent().parent().find(".progress").toggleClass("d-none").text("Processing...");
        $.ajax({
            url:that.url,
            type:"POST",
            data:darr.join("&"),
            success:function(data){
                window.dispatchEvent(new Event("addcontinue"));
                data=JSON.parse(data);
                //console.log(data);
                $(".KDTDform").parent().parent().find(".progress").toggleClass("d-none").text("Done");
                if(data.error){
                    alert(data.error);
                }else{
                    that.lastid=data.lastId;
                    if(subforms.length>0){
                        that.submitSubForms(subdata,data.lastId,subforms);
                    }else{
                        let dtt=$(that.tableId).DataTable().search("").draw();
                        dtt.ajax.reload();
                        $(".KDTD").modal('hide');
                    }
                }
            },
            error:function(err,errstr,xhr){
                window.dispatchEvent(new Event("addcontinue"));
                alert(err+errstr);
                //console.log(err);
                //console.log(errstr);
                //console.log(shr);
            }
        });
    }
    submitSubForms(subdata,lastId,subforms){
        let peflds=subforms;
        let redfs=0;
        let rqedfs=edfilds.length;
        while(peflds.length>0){
            let field=peflds[0];
            peflds.splice(0,1);
            subdata[field.name]=lastId;
            let eddata=field.editor.getData(subdata);
            //console.log(eddata);
            
            let worker=new Worker("static/js/editorworker.js");
            
            worker.addEventListener("message",event=>{
                let rdata=event.data;
                //console.log(rdata);
                if((rdata.error !== undefined && rdata.error !== null) && rdata.error.length>0){
                    that.wait=false;
                    that.stop=true;
                    alert(rdata.error);
                    redfs++;
                }else if((rdata.error !== undefined && rdata.error !== null) && rdata.error.length === 0){
                  that.wait=false;
                  that.stop=false;
                  mdata[field.rel]=rdata.lastId; 
                  redfs++ 
                }
                if (redfs == rqedfs) {
                    if(that.stop){
                        return;
                    }
                    mdata['KACTION']=subdata["KACTION"];
                    //console.log(mdata);
                    let darr=[];
                    for(let m in mdata){
                        darr.push(m+"="+mdata[m]);
                    }
                    //console.log(darr.join("&"));
                    $(".KDTDform").parent().parent().find(".progress").toggleClass("d-none").text("Processing...");
                    $.ajax({
                        url:that.url,
                        type:"POST",
                        data:darr.join("&"),
                        success:function(data){
                            window.dispatchEvent(new Event("addcontinue"));
                            data=JSON.parse(data);
                            //console.log(data);
                            $(".KDTDform").parent().parent().find(".progress").toggleClass("d-none").text("Done");
                            if(data.error){
                                alert(data.error);
                            }else{
                                that.lastid=data.lastId;
                                if(subforms.length>0){
                                    that.submitSubForms(subdata,data.lastId,subforms);
                                }else{
                                    let dtt=$(that.tableId).DataTable().search("").draw();
                                    dtt.ajax.reload();
                                    $(".KDTD").modal('hide');
                                }
                            }
                        },
                        error:function(err,errstr,xhr){
                            window.dispatchEvent(new Event("addcontinue"));
                            alert(err+errstr);
                            //console.log(err);
                            //console.log(errstr);
                            //console.log(shr);
                        }
                    });
                }
            });
            //console.log(field);
            worker.postMessage({url:field.editor.url,method:"POST",data:eddata,action:"submit"});
        }
    }
    awaitToContinue(){
        this.wait=false;
        window.removeEventListener("addcontinue",that.awaitToContinue());
    }
    sleep(ms) {
        var now = new Date().getTime();
        while(new Date().getTime() < now + ms){}
    }
    update(data){
        let mdl=document.createElement("div");
        mdl.setAttribute("class","container");
        let str=this.getElemetHeader();
        let that=this;
        str+=this.getAction("update");
        str+='<div class="form-group">';
        str+=that.getField({type:"hidden",name:"id"},{id:data['DT_RowId'].split('_')[1]});
        str+="</div>";
        this.fields.forEach(field=>{
            str+='<div class="form-group">';
            str+=that.getField(field,data);
            str+="</div>";
        })
        str+=this.getSubmit("Update");

        str+=this.getElemetFooter();
        mdl.innerHTML=str;
        document.body.appendChild(mdl);
        this.events(mdl);
    }
    remove(data){
        let mdl=document.createElement("div");
        mdl.setAttribute("class","container");
        let str=this.getElemetHeader();
        let that=this;
        str+=this.getAction("remove");
        str+='<div class="form-group">';
        str+=that.getField({type:"hidden",name:"id"},{id:data['DT_RowId'].split('_')[1]});
        str+="</div>";
        str+='<div class="form-group">';
        str+=that.getField({type:"label",label:"Do you want to delete row: "+data['DT_RowId'].split('_')[1]});
        str+="</div>";
        str+=this.getSubmit("Delete");

        str+=this.getElemetFooter();
        mdl.innerHTML=str;




          
        document.body.appendChild(mdl);
        this.events(mdl);
    }
    getFields(data={}){
        let str="";
        let that=this;
        this.fields.forEach(field=>{
            str+='<div class="form-group">';
            str+=that.getField(field,data);
            str+="</div>";
        })
        return str;
    }
    getField(conf,data={},options=[]){
        conf.type=conf.type || "text";
        conf.label=conf.label || conf.name || "";
        conf.name=conf.name || conf.label;
        conf.attr=conf.attr || {};
        let attr="";
        for(m in conf.attr){
            attr+=" "+m+"='"+conf.attr[m]+"'";
        }
        data[conf.name]=data[conf.name] || "";
        conf.className=conf.className || "form-control"
        let str,val,moptions;
        switch(conf.type){
            case "hidden":
                return '<input type="'+conf.type+'" value="'+data[conf.name]+'" name="'+conf.name+'"'+attr+'>';
            case "submit":
                return '<input type="'+conf.type+'" class="'+conf.className+'" value="'+data[conf.name]+'"'+attr+'>';
            case "label":
                return '<label>'+conf.label+'</label>';
            case "textarea":
                return '<label>'+conf.label+'</label><textarea class="'+conf.className+'" name="'+conf.name+'"'+attr+'>'+data[conf.name]+'</textarea>';
            case "datetime":
                return '<label>'+conf.label+'</label><input type="'+conf.type+'-local" value="'+data[conf.name]+'" class="'+conf.className+'" name="'+conf.name+'"'+attr+'>';
            case "select":
                let sstr='<label>'+conf.label+'</label><select class="'+conf.className+'" name="'+conf.name+'"'+attr+'>';
                val=data[conf.name];
                moptions=conf.options || this.doptions[conf.name] || this.options[conf.name];
                Array.from(moptions).forEach(opt=>{
                    if (opt.value == val) {
                        sstr+='<option value="'+opt.value+'" selected>'+opt.text+'</option>';
                    }else{
                        sstr+='<option value="'+opt.value+'">'+opt.text+'</option>';
                    }
                });
                sstr+='</select>';
                return sstr
            case "checkbox":
                conf.className=conf.className.replace(/form-control/i,"");
                str='<label>'+conf.label+'</label><div class="row">';
                val=data[conf.name];
                moptions=conf.options || this.doptions[conf.name] || this.options[conf.name];
                Array.from(moptions).forEach(opt=>{
                    if (val.indexOf(opt.value) != -1) {
                        str+='<div class="col-md-3"><input type="checkbox" class="'+conf.className+'" value="'+opt.value+'" name="'+conf.name+'[]"'+attr+' selected>'+opt.text+'</div>';
                    }else{
                        str+='<div class="col-md-3"><input type="checkbox" class="'+conf.className+'" value="'+opt.value+'" name="'+conf.name+'[]"'+attr+'>'+opt.text+'</div>';
                    }
                });
                str+='</div>';
                return str
            case "radio":
                conf.className=conf.className.replace(/form-control/i,"");
                str='<label>'+conf.label+'</label><div class="row">';
                val=data[conf.name];
                moptions=conf.options || this.doptions[conf.name] || this.options[conf.name];
                Array.from(moptions).forEach(opt=>{
                    if (val.indexOf(opt.value) != -1) {
                        str+='<div class="col-md-3"><input type="radio" class="'+conf.className+'" value="'+opt.value+'" name="'+conf.name+'[]"'+attr+' selected>'+opt.text+'</div>';
                    }else{
                        str+='<div class="col-md-3"><input type="radio" class="'+conf.className+'" value="'+opt.value+'" name="'+conf.name+'[]"'+attr+'>'+opt.text+'</div>';
                    }
                });
                str+='</div>';
                return str
            case "editor":
                return conf.editor.getFields(data);
            case "upload":
                val=data[conf.name];

                let str='<label>'+conf.label+'</label><input type="file" class="'+conf.className+' KDTDUploader" data-name="'+conf.name+'"'+attr+'>';
                if(val !== null){
                    let fparts=val.split(".");
                    let ext=fparts[fparts.length-1];
                    let instr="<div>";
                    if(conf.multi){
                        instr=str+instr;
                        instr+="<input type='hidden' value='"+val+"' name='"+conf.name+"[]'>";
                    }else{
                        instr+="<input type='hidden' value='"+val+"' name='"+conf.name+"'>";
                    }
                    instr+="<span class='btn-sm fa fa-times' style='cursor:pointer'></span>"
                    switch(ext){
                        case "png":
                        case "jpg":
                        case "jpeg":
                        case "tif":
                        case "pdf":
                        case "mp4":
                        case "mp3":
                        case "webm":
                            instr+="<embed src='uploads/"+val+"' style='height:300px;width:90%'>"+instr;
                            break;
                        default:
                            instr=val+instr;
                    }
                    return instr;
                }
                return str;

            default:
                return '<label>'+conf.label+'</label><input type="'+conf.type+'" value="'+data[conf.name]+'" class="'+conf.className+'" name="'+conf.name+'"'+attr+'>';
        }
        
    }
}