function rate(form, formId){
	var element = document.getElementById(formId); // the whole div block
	//console.log(element);
	
	var postValue = {};
	var i;
	postValue["nonRelevant"] = 0;
	postValue["neutral"] = 0;
	postValue["Relevant"] = 0;
	for (i = 0;i<form.elements.length;i++){
		//console.log(form.elements[i].name);
		if (form.elements[i].type == "radio"){
			if ( form.elements[i].checked ){
				postValue[form.elements[i].name] = form.elements[i].value;
				postValue[form.elements[i].value] += 1; 
			}
		}else{
			postValue[form.elements[i].name] = form.elements[i].value; 
		}
	}
	//document.getElementsByName('group');
	console.log(postValue);
	
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

function skip(form,formId){
	/*
	var i;
	for(i=0;i<form.elements.length;i++){
		console.log(form.elements[i]);
	}*/
	var element = document.getElementById(formId);
	console.log(element);
	element.style.display="none";
}

function addOne(form, name){
	var num = parseInt(form.elements[name].value) ;
	console.log(num);
	form.elements[name].value = num + 1;
}
