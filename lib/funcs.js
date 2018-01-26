/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var auto_hide = true;

function messageBox(text, timeout){
    $("body").append('<div id="msgbox">'+text+'</div>');
    setTimeout(function(){$("#msgbox").remove();}, timeout);
}

function cancel(post){
    $("#p"+post).html(container[post]);
}

function decodeHTMLEntities(text) {
    var entities = [
        ['apos', '\''],
        ['amp', '&'],
        ['lt', '<'],
        ['gt', '>'],
        ['nbsp', ' '],
        ['#91', '['],
        ['#93', ']'],
    ];

    for (var i = 0, max = entities.length; i < max; ++i) 
        text = text.replace(new RegExp('&'+entities[i][0]+';', 'g'), entities[i][1]);

    return text;
}

function replaceAll(find, replace, str) {
	return str.replace(new RegExp(find, 'g'), replace);
}

function escapeHtml(text) {
  return text
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
}


function resize_full(){
    $("#fullscreen").height(window.innerHeight);$("#fullscreen").css("width",window.innerWidth);
    $("#fullscreenimg").css("max-width",window.innerWidth);
    console.log($("#fullscreen").width());
}

function findHeight(width, isLandscape, aspectRatio){
    if(isLandscape){
        return parseInt(width/aspectRatio);
    }else{
        return parseInt(width*aspectRatio);
    }
}

function fullscreenInit(arg){
    $("#fullscreencontainer").css("visibility","visible");
    var baseUrl = window.location.href.split('#')[0];
    window.history.replaceState( {} , '',  baseUrl + '#fullscreen' );
    fullScreen = true;
    $("body").css("overflow", "hidden");
    //console.log(arg);
    if(arg){
        $("#fullscreenimg").attr("src",arg.src);
        //console.log(arg.parent().parent().parent().children().eq(0).children().eq(0).children().eq(0).html());
        $("#fullscreencontainer > a > h2").html(arg.parentNode.parentNode.parentNode.children[0].children[0].children[0].innerHTML);
        $("#fullscreencontainerdownloadlink").attr('href',$(".download_original_link").get(0).href);
        
    }
}

function fullscreenClose(){
    //alert("s");
    $("#fullscreencontainer").css("visibility","hidden");
    var baseUrl = window.location.href.split('#')[0];
    window.history.replaceState( {} , '',  baseUrl + '#' );
    fullScreen = false;
    $("body").css("overflow", "auto");
}

function lineWrap(){
    var wrap = function () {
        console.log("wrap");
        var elems = document.getElementsByClassName('syntaxhighlighter');
        for (var j = 0; j < elems.length; ++j) {
            var sh = elems[j];
            var glutter = sh.getElementsByClassName('gutter');
            if(glutter.length > 0){
                var gLines = glutter[0].getElementsByClassName('line');
                var cLines = sh.getElementsByClassName('code')[0].getElementsByClassName('line');
                var stand = 15;
                for (var i = 0; i < gLines.length; ++i) {
                    var h = $(cLines[i]).height();
                    if (h != stand) {
                        console.log(i);
                        gLines[i].setAttribute('style', 'height: ' + h + 'px !important;');
                    }
                }
            }
        }
     };
    var whenReady = function () {
        if ($('.syntaxhighlighter').length === 0) {
            setTimeout(whenReady, 800);
        } else {
            wrap();
        }
    };
    whenReady();
}
function resize_img(){
    var numberOfThumbs = $(".thumb_large").length;
    if(numberOfThumbs == 0){
        return false;
    }
    
    min_size = 320;

    for(var i = 0; i < numberOfThumbs; i++){

   
    var containerInner = $($(".large_container_inner")[i]);
    var thumbLarge = $($(".thumb_large")[i]);
    var nav_w = 0;
    var tmp = 0;

    aspectRatio = aspectRatio == Infinity ? 1.5 : aspectRatio ;
    
    if($(".navigation").width() < 200){
        nav_w = 27;
    }else{
        nav_w = parseInt(Math.max($(window).width()*0.2 - 2, 200));
    }
    
    var max_w = $(window).width() - nav_w -60;
    if(i == 0){ 
        var desiredHeight = $(window).height() - $("#navigation_thumbnails").height() - $(".post_header").height();
    }else{
        var desiredHeight = $(window).height() - 100;
    }
    desiredHeight -= 10;
    

    if(thumbLarge.height() >= desiredHeight && (thumbLarge.width() < max_w)){// too tall
        if(thumbLarge.width() > thumbLarge.height()){ //landscape
            tmp = parseInt((desiredHeight)*aspectRatio);
        }else{ //portrait
            tmp = parseInt((desiredHeight)/aspectRatio);
        }
        containerInner.width(tmp);
    }else{
         if(findHeight(max_w-40, (thumbLarge.width() > thumbLarge.height()), aspectRatio) >= desiredHeight){
            if(thumbLarge.width() > thumbLarge.height()){ //landscape
               tmp = parseInt((desiredHeight)*aspectRatio); 
            }else{ //portrait
               tmp = parseInt((desiredHeight)/aspectRatio);
            }
            tmp = parseInt(Math.min(tmp, max_w-40));
            containerInner.css("width",tmp+"px");
         }else{
            containerInner.css("width",max_w-40+"px");
         }
    }
    }

}

function initFullscreen2(arg){
    console.log(arg);
    $("#fullscreenimg").attr("src",arg.src.replace("small","large"));
    $("#fullscreencontainer > a > h2").html(arg.parentElement.parentElement.parentElement.children.item(3).childNodes[0].innerHTML);
    $("#fullscreencontainerlink").attr('href',arg.parentElement.parentElement.parentElement.children.item(3).childNodes[0].href);
    $("#fullscreencontainerdownloadlink").attr('href',arg.src.replace("small","large"));
    fullscreenInit();
}

function fullscreenOfPost(post){
    $("#fullscreenimg").attr("src",$("#post_img"+post).attr("src"));
    $("#fullscreencontainer > a > h2").html($("#post"+post).html());
    $("#fullscreencontainerlink").attr('href',$("#post"+post).parent().attr("href"));
    $("#fullscreencontainerdownloadlink").attr('href',$("#download_original_link_"+post).attr("href"));
    fullscreenInit();
}

/*
 * POST
 * a - action
 * p - post
 * Editor
 * report_msg - reason for report
 * GET
 * edit +
 * new +
 * approve +
 * viewreport /
 * report report_msg + 
 * closereport + 
 * delete confirm=yes +
 */




function processaction(action, post_id){
    console.log(action,post_id);
    if(container[post_id]!=undefined){
        $("#p"+post_id).html(container[post_id]);
    }
    if(action=='edit'){
        container[post_id] = $("#p"+post_id).html();
        $("#p"+post_id).html('<textarea id="edit'+post_id+'" style="width: 99%" rows="4" cols="50">'+$("#p"+post_id).html()+'</textarea><br> <button type="button" onclick="postcomment('+post_id+')">Post</button> <button type="button" onclick="cancel('+post_id+')">Cancel</button>');
    }else if(action=='delete'){
        container[post_id] = $("#p"+post_id).html();
        $("#p"+post_id).html('Are you sure you want to delete this?<br> <button type="button" onclick="deletecomment('+post_id+')">Delete</button> <button type="button" onclick="cancel('+post_id+')">Cancel</button>');
        /*$.post("./lib/view_comment.php?p="+post+"&a=delete",{},function(result){
            $("#p"+post+"container").html("");
        });*/
    }else if(action=='report'){
        container[post_id] = $("#p"+post_id).html();
        $("#p"+post_id).html('reason:<br><textarea id="report'+post_id+'" style="width: 99%" rows="4" cols="50"></textarea><br> <button type="button" onclick="report('+post_id+')">Report</button> <button type="button" onclick="cancel('+post_id+')">Cancel</button>');
    }else if(action=='approve'){
        $.post(rootDir+"/lib/view_comment.php?id="+GET_id+"&p="+post_id+"&a="+action,{},function(result){
            if(result == "approve_success"){
                messageBox("Post successfully approved", 3000);
            }else if(result == "approve_denied"){
                messageBox("You do not have permission to approve posts", 3000);
            }
        });
     }else if(action=='viewreport'){
         container[post_id] = $("#p"+post_id).html();
         $.post(rootDir+"/lib/view_comment.php?id="+GET_id+"&p="+post_id+"&a="+action,{},function(result){
            $("#p"+post_id).html(result+'<br><button type="button" onclick="cancel('+post_id+')">Cancel</button>');
        });
     }else if(action=='closereport'){
         container[post_id] = $("#p"+post_id).html();
         $.post(rootDir+"/lib/view_comment.php?id="+GET_id+"&p="+post_id+"&a="+action,{},function(result){
            if(result == "close_report_success"){
                messageBox("Report successfully closed", 3000);
            }else if(result == "close_report_fail"){
                messageBox("Failed to close report", 3000);
            }else if(result == "close_report_denied"){
                messageBox("You do not have permission to close reports", 3000);
            }
        });
     }
}

function deletecomment(post_id){
    $.post(rootDir+"/lib/view_comment.php?id="+GET_id+"&p="+post_id+"&a=delete&confirm=yes",{},function(result){
        if(result == "delete_success"){
            messageBox("comment have beel successfully deleted", 3000);
            $("#content"+post_id).remove();
        }else if(result == "delete_denied"){
            messageBox("To delete comment", 3000);
        }
    });
}

function report(post_id){
    var str = $("#report"+post_id).val();
    if(str.length < 1){
        alert("You must specify reason to report");
    }
    $.post(rootDir+"/lib/view_comment.php?id="+GET_id+"&p="+post_id+"&a=report",{report_msg:str},function(result){
        if(result == "report_success"){
            messageBox("Post successfully reported", 3000);
        }else{
            messageBox("Failed to report post", 3000);
        }
    });
    cancel(post_id);
}

function getParam(arr,name){
    for(var i = 0;i < arr.length; i++ ){
        var ret = arr[i].split("="); 
        if(ret[0]==name){
            return ret[1];
        }
    }
}

function parseLink(link){
    var parts = link.split("?"); 
    parts = parts[1].split("&"); 
    processaction(getParam(parts,'a'),getParam(parts,'p'))
}

function postcomment(post_id){
    var a = 'edit';
    if(post_id == 0){
        a = 'new';
    }
    //alert("./lib/view_comment.php?id="+GET_id+"&p="+post_id+"&a="+a);
    //alert($("#edit"+post_id).val());
    $.post(rootDir+"/lib/view_comment.php?id="+GET_id+"&p="+post_id+"&a="+a,{Editor:$("#edit"+post_id).val(),website:$( "input[name='website']" ).val()},function(result){
        if(result == "edit_success"){
            messageBox("Post successfully edited", 3000);
        }else if(result == "edit_failed"){
            messageBox("Failed to edit post", 3000);
        }else if(result == "comment_failed"){
            messageBox("Failed to post comment", 3000);
        }else{
            //alert(result);
            $( result ).insertAfter( "#separator" );
            messageBox("comment successfully posted<br>Please note that this comment may require approval before it appears.", 5000);
        }
    });
    if(post_id>0){
        $("#p"+post_id).html($("#edit"+post_id).val());
    }else{
        $("#edit"+post_id).val("");
    }
}
function enablecomments(){
    $.post(rootDir+"/lib/view_comment.php?id="+GET_id+"&a=test",{},function(result){
        if(result == "comments_exist"){
            $("#comments").html('<p class="comments_button" onclick="getcomments();">View comments</p>');
        }   
    });
}
    
function getcomments(){
    var data = 'Post a comment:<br><textarea id="edit0" style="width: 99%" rows="4" cols="50"></textarea><br><input name="website" style="position: absolute; left: -9000px"> <button type="button" onclick="postcomment(0)">Post</button> <br id="separator">';
    $.post(rootDir+"/lib/view_comment.php?id="+GET_id+"",{},function(result){
        $("#comments").html(data+result);
    });
}


function resizeWin(){
    if(autoResize){
        var id =  document.getElementById("ACP_ACTIONS_MENU");
        if(window.innerWidth <= 320){
            if(id != null && auto_hide){
                if(olddata == ""){
                    olddata = id.innerHTML;
                }
                id.innerHTML = '<span class="bold_title show" onclick="showmenu();"><img style="position:relative; bottom: -6px;" class="icon24" src="'+rootDir+'/theme/'+templatename+'/icons/menu.png"></span>';
                id.style.width= "0px";
                id.style.minWidth= "0px";
            }
        }else{
            if(id != null && auto_hide){
                showmenu();
                showbtn = false;
            }
        }
    }
}

function showmenu(){
    if(olddata != ""){
        var id = document.getElementById("ACP_ACTIONS_MENU");
        id.style.width= "20%";
        id.style.minWidth= "200px";
        id.innerHTML = olddata;
        olddata = "";
        if(!showbtn){
            showbtn = true;
        }
    }
    resize_img();
}

function hidemenu(){
    var id = document.getElementById("ACP_ACTIONS_MENU");
    if(id == null){
        return;
    }
    olddata = id.innerHTML.replace('</b><span class="bold_title hide" onclick="hidemenu();auto_hide=false;"><<</span><br>',"</b><br>");
    showbtn = false;
    id.innerHTML = '<span class="bold_title show" onclick="showmenu();"><img style="position:relative; bottom: -6px;" class="icon24" src="'+rootDir+'/theme/'+templatename+'/icons/menu.png"></span>';
    id.style.width= "0px";
    id.style.minWidth= "0px";
    resize_img();
}

window.onresize = function(event) {
    resizeWin();
    resize_img();
    resize_full();
};

function resToString(result){
    switch(result) {
    case "like_denied_guest":
        return "Guests are not allowed to like, please login.";
        break;
    case "like_denied":
        return "You do not have permission to like this post.";
        break;
    case "unlike_denied_guest":
        return "Guests are not allowed to unlike, please login.";
        break;
    case "unlike_denied":
        return "You do not have permission to unlike this post.";
        break;
    case "forum_denied":
        return "You do not have access to this forum.";
        break;
    default:
        return "Unknown error."
    } 
}

function Like(elem, likes, pid){
    console.log(rootDir+"/lib/like.php?a=like&p="+pid);
    $.post(rootDir+"/lib/like.php?a=like&p="+pid,{},function(result){
        if(result == "like_success"){
            if(likes > 0){
                elem.parentNode.innerHTML = "You and "+likes+' people like this <a href="#" onclick="UnLike(this, '+likes+','+pid+');return false;">Unlike</a>';
            }else{
                elem.parentNode.innerHTML = '<a href="#" onclick="UnLike(this, '+likes+','+pid+');return false;">Unlike</a>';
            }
        }else{
            messageBox(resToString(result),5000);
            //messageBox(result,50000);
        }   
    });
}

function UnLike(elem, likes, pid){
    $.post(rootDir+"/lib/like.php?a=unlike&p="+pid,{},function(result){
        if(result == "unlike_success"){
            if(likes-1 > 0){
                 elem.parentNode.innerHTML = "You and "+likes+' people like this <a href="#" onclick="Like(this, '+(likes-1)+','+pid+');return false;">Like</a>';
            }else{
                elem.parentNode.innerHTML = '<a href="#" onclick="Like(this, '+likes+','+pid+');return false;">Like</a>';
            }
        }else{
            messageBox(resToString(result),5000);
        }   
    });
}

function deleteBan(id){
    $.post(window.location.href+"&mode=delete" ,{ban_id:id},function(result){
        //alert(result);
    });
}

function editPermissions(forum_id){
    $('input[name=forum_id]').val(forum_id);
    $("#groupjunkie").submit();
}


function CreateWindow(winStruct){
    var color = "#DBEDFF";
    var titlecolor = "#9CF";
    var showtitleBar = true;
    var resizable = true;
    var scrollBar = true;
    var showCloseButton = true;
    
    if(winStruct.resizable != undefined){
        resizable = winStruct.resizable;
    }
    
    var overflow = "hidden";
    if(scrollBar){
        overflow = "auto";
    }
    
    var closeButton = "";
    if(showCloseButton){
       closeButton = '<div style="float:right; text-align: left;" onclick="$(\'#win\').remove();"><img class="icon24" src="'+rootDir+'/lib/acp/close.png"></div><div style="clear:both;"></div>';
    }
    
    
    var titleBar = "";
    if(showtitleBar){
        titleBar = '<div class="window_title" id="titlebar" >'+winStruct.title+closeButton+'</div>';
    }
    
    var window = '<div id="win" class="window" style="position: fixed; left: '+winStruct.x+'; top: '+winStruct.y+'; width: '+winStruct.width+'; height: '+winStruct.height+';">'+titleBar+'<div id="wincontent" style="height: 100%; overflow:'+overflow+';"></div></div>';
    var win = $(window);
    win.appendTo('body');
    win.draggable({ 
    containment: 'window', 
    scroll: false, 
    handle: '#titlebar' 
    });
    if(resizable){
        win.resizable();
        $(".ui-icon-gripsmall-diagonal-se").css("background-image", "")
    }
}

function requestMembers(how_many){
  $.get( "../memberlist.php?ajax=1&mode="+how_many, function( data ) {
    //$(data).appendTo($("#win"))
    $("#wincontent").html(data);
  });
}

function createUserSelector(){
    CreateWindow({title:"Select user",x:"10px",y:"10px", width:"95%", height:"85%"});
    requestMembers("one");
}

function createUsersSelector(){
    CreateWindow({title:"Select user",x:"10px",y:"10px", width:"95%", height:"85%"});
    requestMembers("many");
}


function selectUser(name){
    $("#username").val(name);
    $("#win").remove();
}


function postdata(actionUrl, method, data) {
    var mapForm = $('<form id="mapform" action="' + actionUrl + '" method="' + method.toLowerCase() + '"></form>');
    for (var key in data) {
        if (data.hasOwnProperty(key)) {
            mapForm.append('<input type="hidden" name="' + key + '" id="' + key + '" value="' + data[key] + '" />');
        }
    }
    $('body').append(mapForm);
    mapForm.submit();
}


function parseList(){
    var elements = $("div.list");
    for(var elem = 0; elem < elements.length; elem++ ){
        var lines = elements[elem].innerHTML;
        lines = lines.replace(/(\r\n|\n|\r)/gm,"");
        lines = lines.split('<br>');
        var indent = 0;
        var ul_indent = 0;
        var line = "";
        var html = "";
        var star_count = 0;
        for(var i = 0; i < lines.length; i++){
            star_count = 0;

            while(lines[i].substring(0,1) == "*"){
                star_count += 1;
                lines[i] = lines[i].substring(1);
            }

            if(lines[i] == ""){
                while(indent > 0){
                    indent--;
                    html += "</ul>";
                }
                html += "<br>";
                continue;
            }

            if(star_count < indent){
                for(var j = star_count; j < indent; j++ ){
                    html += "</ul>";
                }
                var diff = indent - star_count;
                indent -= diff;
            }else if(star_count > indent){
                for(var j = indent; j < star_count; j++ ){
                    html += "<ul>";
                }
                var diff = indent - star_count;
                indent -= diff;
            }

            if(indent == 0){
                html += '<b>'+lines[i]+'</b><br>';
            }else{
                html += '<li>'+lines[i]+'</li>';
            }
        }
        while(indent > 0){
            indent--;
            html += "</ul>";
        }
        elements[elem].innerHTML = html;
    }
    
    function build_url(arg){
        var url = location.protocol + '//' + location.host;
        url += "?" + $.param(arg) ;
    }
    
}

function fullscreenSetup(autostart) {
    if(autostart){
        $("#fullscreencontainer > a > h2").html(toicTitle);
        $("#fullscreenimg").attr('src', firstImage);
    } else {
        if (pid == '') {
            $("#fullscreencontainer > a > h2").html($('h2[id^="post"]').get(0).innerHTML);
            $("#fullscreenimg").attr('src', $('img[id^="post_img"]').get(0).src);
        } else {
            $("#fullscreencontainer > a > h2").html($('#post' + pid).get(0).innerHTML);
            $("#fullscreenimg").attr('src', $('#post_img' + pid).get(0).src);
        }
    }
}

function getPrevLink(){
    return prevLink + window.location.hash;
}

function getNextLink(){
    return nextLink + window.location.hash;
}


function fullscreenCheck(){
    var fullscreen = window.location.hash == "#fullscreen";


    try {
        if (fullscreen) {
            fullscreenSetup(true);
            fullscreenInit();
        }
    } catch (e) {
    }

    if (isMobile != "") {
        var items = $("div > a > img:nth-child(1)");
        for (var i = 0; i < items.length; i++) {
            items[i].onclick = function () {
                initFullscreen2(this);
                return false;
            }
        }
    }

    $(document).keydown(function (e) {
        //console.log(e.target.tagName);
        if (e.target.tagName.toLowerCase() !== 'input' &&
                e.target.tagName.toLowerCase() !== 'textarea') {
            switch (e.which) {
                case 37: // left
                    window.location = getPrevLink();
                    break;

                case 39: // right
                    window.location = getNextLink();
                    break;

                case 27:
                    fullscreenClose();
                    break;

                default:
                    return; // exit this handler for other keys
            }
            e.preventDefault(); // prevent the default action (scroll / move caret)
        }
    });

    resize_full();
}

function composeCell(row, template) {
    var expression = /\{(\w|-){1,}\}/g;
    var properties = template.match(expression);
    var replaced = template;
    for (var property = 0; property < properties.length; property++) {
        var extractedName = properties[property].replace('{', '').replace('}', '');
        replaced = replaced.replace(properties[property], row[extractedName]);
    }
    return replaced;
}

function buildTable(tableConfig, tableData) {
    var table = '<table class="sortable" style="text-align: left; width: 100%;" border="0" cellpadding="2" cellspacing="0"><thead><tr>';
    for (var column = 0; column < tableConfig.length; column++) {
        table += '<th>' + tableConfig[column].title + '</th>';
    }
    table += '</tr></thead><tbody>';
    for (var row = 0; row < tableData.length; row++) {
        table += '<tr>';
        for (var column = 0; column < tableConfig.length; column++) {
            table += '<td>' + composeCell(tableData[row], tableConfig[column].template) + '</td>';
        }
        table += '</tr>';
    }
    table += '</tbody></table>';
    return table;
}