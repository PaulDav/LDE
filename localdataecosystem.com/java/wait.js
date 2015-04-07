
function cursorwait(e) {
    document.body.className = 'wait';
}


function fwait(){
	var formsCollection = document.getElementsByTagName("form");
	for (var i=0;i<formsCollection.length;i++)
	{
		formsCollection[i].setAttribute("onSubmit", "FormWait()");
	}
}

function FormWait(){
    cursorwait();
}