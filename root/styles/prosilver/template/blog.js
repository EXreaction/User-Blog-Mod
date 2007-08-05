/*
* For the deleted replies, to open and close divs
*/
function toggleDiv(divName, toggle)
{
    thisDiv = document.getElementById(divName);
    if (thisDiv)
	{
        if (thisDiv.style.display == "none" || toggle == "show")
		{
            thisDiv.style.display = "block";
        }
        else
		{
            thisDiv.style.display = "none";
        }
    }
}

/***********************************************
* Slashdot Menu script- By DimX(modified by EXreaction)
* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
* Used for the Archives menu
***********************************************/

var contractall_default = true; //Should all submenus be contracted by default?

var menu, titles, submenus, arrows, bypixels;
var heights = new Array();

var n = navigator.userAgent;
if(/Opera/.test(n)) bypixels = 2;
else if(/Firefox/.test(n)) bypixels = 3;
else if(/MSIE/.test(n)) bypixels = 2;

/////DD added expandall() and contractall() functions/////

function slash_expandall(){
if (typeof menu!="undefined"){
	for(i=0; i<Math.max(titles.length, submenus.length); i++){
		titles[i].className="title";
		arrows[i].src = "./styles/prosilver/theme/images/minus.gif";
		submenus[i].style.display="";
		submenus[i].style.height = heights[i]+"px";
	}
}
}

function slash_contractall(){
if (typeof menu!="undefined"){
	for(i=0; i<Math.max(titles.length, submenus.length); i++){
		titles[i].className="titlehidden";
		arrows[i].src = "./styles/prosilver/theme/images/plus.gif";
		submenus[i].style.display="none";
		submenus[i].style.height = 0;
	}
}
}


/////End DD added functions///////////////////////////////


function init(){
    menu = getElementsByClassName("archive_sdmenu", "div", document)[0];
    titles = getElementsByClassName("archive_title", "span", menu);
    submenus = getElementsByClassName("archive_submenu", "div", menu);
    arrows = getElementsByClassName("archive_arrow", "img", menu);
    for(i=0; i<Math.max(titles.length, submenus.length); i++) {
        titles[i].onclick = gomenu;
        arrows[i].onclick = gomenu;
        heights[i] = submenus[i].offsetHeight;
        submenus[i].style.height = submenus[i].offsetHeight+"px";
    }
    if (contractall_default) //DD added code
		slash_contractall() //DD added code

	showmenu(0); // Open the first menu
}

function gomenu(e) {
    if (!e)
        var e = window.event;
    var ce = (e.target) ? e.target : e.srcElement;
    var sm;
    for(var i in titles) {
        if(titles[i] == ce || arrows[i] == ce)
            sm = i;
    }
    if(parseInt(submenus[sm].style.height) > parseInt(heights[sm])-2) {
        hidemenu(sm);
    } else if(parseInt(submenus[sm].style.height) < 2) {
        titles[sm].className = "title";
        showmenu(sm);
    }
}

function hidemenu(sm) {
    var nr = submenus[sm].getElementsByTagName("a").length*bypixels;
    submenus[sm].style.height = (parseInt(submenus[sm].style.height)-nr)+"px";
    var to = setTimeout("hidemenu("+sm+")", 30);
    if(parseInt(submenus[sm].style.height) <= nr) {
        clearTimeout(to);
        submenus[sm].style.display = "none";
        submenus[sm].style.height = "0px";
        arrows[sm].src = "./styles/prosilver/theme/images/plus.gif";
        titles[sm].className = "titlehidden";
    }
}

function showmenu(sm) {
    var nr = submenus[sm].getElementsByTagName("a").length*bypixels;
    submenus[sm].style.display = "";
    submenus[sm].style.height = (parseInt(submenus[sm].style.height)+nr)+"px";
    var to = setTimeout("showmenu("+sm+")", 30);
    if(parseInt(submenus[sm].style.height) > (parseInt(heights[sm])-nr)) {
        clearTimeout(to);
        submenus[sm].style.height = heights[sm]+"px";
        arrows[sm].src = "./styles/prosilver/theme/images/minus.gif";
    }
        
        
}

function getElementsByClassName(strClassName, strTagName, oElm){
    var arrElements = (strTagName == "*" && document.all)? document.all : oElm.getElementsByTagName(strTagName);
    var arrReturnElements = new Array();
    strClassName = strClassName.replace(/\-/g, "\\-");
    var oRegExp = new RegExp("(^|\\s)" + strClassName + "(\\s|$)");
    var oElement;
    for(var i=0; i<arrElements.length; i++){
        oElement = arrElements[i];      
        if(oRegExp.test(oElement.className)){
            arrReturnElements.push(oElement);
        }   
    }
    return (arrReturnElements)
}

window.onload = init;