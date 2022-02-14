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
        this.fields=options.fields || []
        this.tableId=options.table;
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
        },1000)
    }
    add(){
        let mdl=document.createElement("div");
        mdl.setAttribute("class","container");
        let str='';
        str+='<div class="modal fade KDTD" role="dialog">';
        str+='<div class="modal-dialog">';
        str+='<div class="modal-content">';
        str+='<div class="modal-header">';
        str+='<span class="progress p-2 pb-4 d-none"></span><button type="button" class="close" data-dismiss="modal">&times;</button>';
        str+='</div>';
        str+='<div class="modal-body">';
        str+='<form role="form" class="form-horizontal KDTDform" method="post">';
        let that=this;
        str+='<div class="form-group">';
        str+=that.getField({type:"hidden",name:"KACTION"},{KACTION:"create"});
        str+="</div>";
        this.fields.forEach(field=>{
            str+='<div class="form-group">';
            str+=that.getField(field);
            str+="</div>";
        })
        str+='<div class="form-group">';
        str+=that.getField({type:"submit",className:"btn btn-primary",name:"submit"},{submit:"Create"});
        str+="</div>";

        str+='</form>';
        str+='</div>';
        str+='<div class="modal-footer">';
        str+='<button type="submit" class="btn btn-default btn-default pull-left" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> Cancel</button>';
        str+='</div>';
        str+='</div>';
        str+='</div>';
        str+='</div>';
        mdl.innerHTML=str;




          
        document.body.appendChild(mdl);
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
        this.fields.forEach(field=>{
            field.type=field.type || "text";

            if(field.type !== "editor"){
                field.name=field.name || field.label;
                field.altname=field.altname || field.name
                mdata[field.altname]=subdata[field.name] || "";
            }else{
                edfilds.push(field);
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
                                    let dtt=$(that.tableId).DataTable().search("").draw();
                                    dtt.ajax.reload();
                                    $(".KDTD").modal('hide');
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
        }else{
            mdata['KACTION']=subdata["KACTION"];
            //console.log(mdata);
            let darr=[];
            for(let m in mdata){
                darr.push(m+"="+mdata[m]);
            }
            //console.log(darr.join("&"));
            $.ajax({
                url:that.url,
                type:"POST",
                data:darr.join("&"),
                success:function(data){
                    data=JSON.parse(data);
                    //console.log(data);
                    $(".KDTDform").parent().parent().find(".progress").toggleClass("d-none").text("Done");
                    if(data.error){
                        alert(data.error);
                    }else{
                        that.lastid=data.lastId;
                        let dtt=$(that.tableId).DataTable().search("").draw();
                        dtt.ajax.reload();
                        $(".KDTD").modal('hide');
                    }
                },
                error:function(err,errstr,xhr){
                    alert(err+errstr);
                    //console.log(err);
                    //console.log(errstr);
                    //console.log(shr);
                }
            });
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
        let str='';
        str+='<div class="modal fade KDTD" role="dialog">';
        str+='<div class="modal-dialog">';
        str+='<div class="modal-content">';
        str+='<div class="modal-header">';
        str+='<span class="progress p-2 pb-4 d-none"></span><button type="button" class="close" data-dismiss="modal">&times;</button>';
        str+='</div>';
        str+='<div class="modal-body">';
        str+='<form role="form" class="form-horizontal KDTDform">';
        let that=this;
        str+='<div class="form-group">';
        str+=that.getField({type:"hidden",name:"KACTION"},{KACTION:"update"});
        str+="</div>";
        str+='<div class="form-group">';
        str+=that.getField({type:"hidden",name:"id"},{id:data['DT_RowId'].split('_')[1]});
        str+="</div>";
        this.fields.forEach(field=>{
            str+='<div class="form-group">';
            str+=that.getField(field,data);
            str+="</div>";
        })
        str+='<div class="form-group">';
        str+=that.getField({type:"submit",className:"btn btn-primary",name:"submit"},{submit:"Update"});
        str+="</div>";

        str+='</form>';
        str+='</div>';
        str+='<div class="modal-footer">';
        str+='<button type="submit" class="btn btn-default btn-default pull-left" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> Cancel</button>';
        str+='</div>';
        str+='</div>';
        str+='</div>';
        str+='</div>';
        mdl.innerHTML=str;




          
        document.body.appendChild(mdl);
        $(".KDTD").modal();
        $(".KDTD").on('hidden.bs.modal', function(){
            document.body.removeChild(mdl);
        });
        $(".KDTDform").on("submit",function(){
            $(this).parent().parent().find(".progress").toggleClass("d-none").text("Processing...");
            $.ajax({
                url:that.url,
                method:"POST",
                data:$(this).serialize(),
                success:function(data){
                    data=JSON.parse(data);
                    console.log(data);
                    $(".KDTDform").parent().parent().find(".progress").toggleClass("d-none").text("Done");
                    if(data.error){
                        alert(data.error);
                    }else{
                        let dtt=$(that.tableId).DataTable().search("").draw();
                        dtt.ajax.reload();
                        $(".KDTD").modal('hide');
                    }
                },
                error:function(err,errstr,xhr){
                    alert(err+errstr);
                    console.log(err);
                    console.log(errstr);
                    console.log(shr);
                }
            });
            return false;
        });
    }
    remove(data){
        let mdl=document.createElement("div");
        mdl.setAttribute("class","container");
        let str='';
        str+='<div class="modal fade KDTD" role="dialog">';
        str+='<div class="modal-dialog">';
        str+='<div class="modal-content">';
        str+='<div class="modal-header">';
        str+='<span class="progress p-2 pb-4 d-none"></span><button type="button" class="close" data-dismiss="modal">&times;</button>';
        str+='</div>';
        str+='<div class="modal-body">';
        str+='<form role="form" class="form-horizontal KDTDform">';
        let that=this;
        str+='<div class="form-group">';
        str+=that.getField({type:"hidden",name:"KACTION"},{KACTION:"remove"});
        str+="</div>";
        str+='<div class="form-group">';
        str+=that.getField({type:"hidden",name:"id"},{id:data['DT_RowId'].split('_')[1]});
        str+="</div>";
        str+='<div class="form-group">';
        str+=that.getField({type:"label",label:"Do you want to delete row: "+data['DT_RowId'].split('_')[1]});
        str+="</div>";
        str+='<div class="form-group">';
        str+=that.getField({type:"submit",className:"btn btn-danger",name:"submit"},{submit:"Delete"});
        str+="</div>";

        str+='</form>';
        str+='</div>';
        str+='<div class="modal-footer">';
        str+='<button type="submit" class="btn btn-default btn-default pull-left" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> Cancel</button>';
        str+='</div>';
        str+='</div>';
        str+='</div>';
        str+='</div>';
        mdl.innerHTML=str;




          
        document.body.appendChild(mdl);
        $(".KDTD").modal();
        $(".KDTD").on('hidden.bs.modal', function(){
            document.body.removeChild(mdl);
        });
        $(".KDTDform").on("submit",function(){
            $(this).parent().parent().find(".progress").toggleClass("d-none").text("Processing...");
            $.ajax({
                url:that.url,
                method:"POST",
                data:$(this).serialize(),
                success:function(data){
                    data=JSON.parse(data);
                    console.log(data);
                    $(".KDTDform").parent().parent().find(".progress").toggleClass("d-none").text("Done");
                    if(data.error){
                        alert(data.error);
                    }else{
                        let dtt=$(that.tableId).DataTable().search("").draw();
                        dtt.ajax.reload();
                        $(".KDTD").modal('hide');
                    }
                },
                error:function(err,errstr,xhr){
                    alert(err+errstr);
                    console.log(err);
                    console.log(errstr);
                    console.log(shr);
                }
            });
            return false;
        });
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
                str='<label>'+conf.label+'</label><select class="'+conf.className+'" name="'+conf.name+'"'+attr+'>';
                val=data[conf.name];
                moptions=conf.options || [{text:"True",value:"True"},{text:"False",value:"False"}];
                Array.from(moptions).forEach(opt=>{
                    if (opt.value == val) {
                        str+='<option value="'+opt.value+'" selected>'+opt.text+'</option>';
                    }else{
                        str+='<option value="'+opt.value+'">'+opt.text+'</option>';
                    }
                });
                str+='</select>';
                return str
            case "checkbox":
                conf.className=conf.className.replace(/form-control/i,"");
                str='<label>'+conf.label+'</label><div class="row">';
                val=data[conf.name];
                moptions=conf.options || [{text:"True",value:"True"},{text:"False",value:"False"}];
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
                moptions=conf.options || [{text:"True",value:"True"},{text:"False",value:"False"}];
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
            default:
                return '<label>'+conf.label+'</label><input type="'+conf.type+'" value="'+data[conf.name]+'" class="'+conf.className+'" name="'+conf.name+'"'+attr+'>';
        }
        
    }
}