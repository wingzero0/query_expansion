function rate(form){
	var id = form.elements[4].value;
	var element = document.getElementById(id);
	//console.log(element);
	
	var postValue = {};
	var i;
	for (i = 1;i<=10;i++){
		postValue[form.elements[i].name] = form.elements[i].value; 
	} 
	//console.log(postValue);
	
	$.ajax({  
		type: "POST",
		url: "DBUpload.php",
		data: postValue,
		dataType: "json",
		success: function(data) {
			if (data.result == true){
				element.style.display="none"; 
			}else{
				alert(data.err);
				//element.innerHTML = data.err;
			}
		}  
	});   
}

function skip(form){
	/*
	var i;
	for(i=0;i<form.elements.length;i++){
		console.log(form.elements[i]);
	}*/
	var id = form.elements[4].value;
	var element = document.getElementById(id);
	console.log(element);
	element.style.display="none";
}