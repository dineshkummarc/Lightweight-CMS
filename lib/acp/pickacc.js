var PickerCreated = false;
var ResponseText = '';
var RespondTo = '';

function loadXMLDoc(page = 0)
{
	var xmlhttp;
	if (window.XMLHttpRequest)
	{// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	}
	else
	{// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange=function()
	{
		if (xmlhttp.readyState==4 && xmlhttp.status==200)
		{
			ResponseText = xmlhttp.responseText.replace(/\\/g, "\\\\");
		}
	};
	xmlhttp.open("GET","acp.php?a=MANAGE_USERS&mode=listusers&page="+page,false);
	xmlhttp.send();
}

function AccountPickerCreate(page = 0){
	TableStart = '<div style="text-align: left; width: 100%;"><div style="float:right; text-align: left;"><span class="link" onclick="AccountPickerClose()">Close</span></div><b>Member list</b></div><table style="text-align: left; width: 100%;" border="0" cellpadding="2" cellspacing="0"><tbody><tr style="color:#FFFFFF;font-weight:bold;" BGCOLOR="#428AFF"><td>Select</td><td>Name</td><td>Email</td><td>Posts</td><td>Joined</td></tr>\n';
	TableData = '';
	loadXMLDoc(page);
	eval(ResponseText);
	var sColor1 = "#99CCFF";
	var sColor2 = "#77B8FF";
	var sColor = '';
	for(var i in UserList) {
		if(i % 2){sColor = sColor1}else{sColor = sColor2;}
		if(RespondTo == ''){
			TableData += '<tr bgcolor="'+sColor+'"><td><a href="'+document.URL+"&uid="+UserList[i]['UserID']+'">[select]</a></td><td>'+UserList[i]['UserName']+'</td><td>'+UserList[i]['UserEmail']+'</td><td>'+UserList[i]['UserPostCount']+'</td><td>'+UserList[i]['UserJoinDate']+'</td></tr>\n';
		}else{
			TableData += '<tr bgcolor="'+sColor+'"><td><a onclick="set_value(\''+UserList[i]['UserName']+'\')">[select]</a></td><td>'+UserList[i]['UserName']+'</td><td>'+UserList[i]['UserEmail']+'</td><td>'+UserList[i]['UserPostCount']+'</td><td>'+UserList[i]['UserJoinDate']+'</td></tr>\n';
		}
	}
	TableEnd = '</tbody></table><div>'+GetPager(usercount,pagelimit,currentpage)+'</div>';
	//TableEnd = '</tbody></table><div></div>';
	return TableStart+TableData+TableEnd;
}

function GetPager(usercount,pagelimit,currentpage){
	var pager = "";
	if (pagelimit > usercount) return "";
	var pages = Math.ceil(usercount/pagelimit);
	if (currentpage > 0){pager += '<span class="link" onclick="AccountPickerOpen('+(currentpage-1)+')">< Previous</span> ';}
	for(var i = 0; i < pages;i++){
		if (i == currentpage){
			pager += i+" ";
		}else{
			pager += '<span class="link" onclick="AccountPickerOpen('+i+')">'+i+'</span> ';
		}
	}
	if (currentpage < (pages-1)){pager += '<span class="link" onclick="AccountPickerOpen('+(currentpage+1)+')">Next ></span>';}
	return pager;
}

function AccountPickerOpen(page = 0){
	hWnd = document.getElementById('account_picker');
	hWnd.innerHTML=AccountPickerCreate(page);
        hWnd.style.visibility = 'visible';
}

function AccountPickerClose(){
	hWnd = document.getElementById('account_picker');
	hWnd.style.visibility = 'hidden';
}


function set_value(text){
	var hWnd = document.getElementById(RespondTo);
	hWnd.value = text;
        AccountPickerClose();
}
