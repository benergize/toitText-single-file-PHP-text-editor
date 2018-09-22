<?php 

	session_start();  
	
	/*
	**
	** toitText.php
	** Software by Ben Ehrlich (benergize)
	** Created 2016, revised 2018.
	**
	** You may freely use/modify this software under the MIT License
	** ...(http://www.benergize.com/mit.txt)
	**
	** This software is experimental--use at your own risk!
	** 
	*/
	
	
	//Trivial security lives below.
	//-----------------------------------
	
		// *** Generate a password with this function and replace $password with the result: ***
		/*die(password_hash("your password here",PASSWORD_BCRYPT));*/
		
		//Default password is 'alpine'. CHANGE THIS BEFORE YOU USE THE EDITOR!
		$password = '$2y$10$NpfqQZ3/i/ExRTsVyaHIRuE7TtKAchPi2gvz4LRnpiaBtJczy.WM2';
		
		//If we've come here from the form
		if(isset($_POST['login'])) { 
		
			//Verify password
			if(password_verify($_POST['password'],$password)) { $_SESSION['loggedIn'] = true; } 
			else { echo "Incorrect password."; }
		}
		
		//If the session didn't get set above, show the login form.
		if(!$_SESSION['loggedIn']) {
			
			die("
				<form action = '?' method = 'POST'>
					<label>Password: <input type = 'password' name = 'password'></label>
					<input type = 'submit' name = 'login' value = 'Login'>
				</form>
			");
			
		}
	//-----------------------------------
	//End trivial security
	
	
	
	//Load the file -- Called via AJAX to populate the textarea
	if($_POST['retrieve'] == "1") {
		if(isset($_POST['fileName'])) { die(file_get_contents($_POST['fileName'])); }
	}

	//Save the file -- Called via AJAX by function doSave() 
	else if($_POST['save'] == "1") {
		
		//fwrite doesn't like writing empty files so we turn an empty string into an escaped empty string.
		if($_POST['content'] == "") { $_POST['content'] = "\0"; }
	 
		//If we can open the file
		if($file = fopen($_POST['fileName'],"w")) {
		
			//Write it.
			if(fwrite($file,$_POST['content'])) {
			 
				fclose($file);
				
				die(json_encode(["desc"=>"Saved.","level"=>"success"]));
				
			} else { die(json_encode(["desc"=>"An error occurred writing the file.","level"=>"fatal"])); } 
			
		} else { die(json_encode(["desc"=>"Could not open file.","level"=>"fatal"])); }
		
	}
	
	//We're not saving or loading so we're going to try and display the editor
	else {
		if(!isset($_GET['fileName'])) { die("No file specified."); }
		if(!file_exists($_GET['fileName'])) { die("File not found."); }
	}
	
?>

<html>
	<head>
	
		<script src="https://code.jquery.com/jquery-2.2.0.min.js"></script>
		<script src = 'https://cdn.jsdelivr.net/npm/taboverride@4.0.3/build/output/taboverride.min.js'></script>
		<meta charset = 'UTF-8'>
		
		<style> a:link,a:visited,button{color:powderblue;text-decoration:none;background:none;}a:hover,button{text-decoration:underline;background:none;}body{font-family:'lucida grande','Segoe UI',Arial, sans-serif;background:black;color:white;}input[type=button]{font-size:16px;font-family:'lucida grande','Segoe UI',Arial, sans-serif;background:none;border:0px;text-decoration:none;color:powderblue;text-valign:top;}input[type=button]:hover{text-decoration:underline;cursor:pointer;}#editor{overflow-x:scroll;background:rgb(20,20,20);color:rgb(248,248,248);font-family:courier;width:100%;height:85%;font-size:13px;padding:24px;tab-size:2em;-moz-tab-size:2em;-o-tab-size:2em;white-space:pre;}#menubar{padding:.75em;}</style> 
		
		<script>
		
			//Minified-ish AJAX shorthand -- courtesy of iworkforthem on Github
			function postAjax(url, data, success) {var params = typeof data == 'string' ? data : Object.keys(data).map(function(k){return encodeURIComponent(k) + '=' + encodeURIComponent(data[k])}).join('&');var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");xhr.open('POST', url);xhr.onreadystatechange = function() {if(xhr.readyState>3 && xhr.status==200) { success(xhr.responseText); }};xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');xhr.send(params);return xhr;}
			//GET variables in javascript -- courtesy of gion_13 on StackOverflow
			var $_GET = {}; if(document.location.toString().indexOf('?') !== -1) { var query = document.location.toString().replace(/^.*?\?/, '').replace(/#.*$/, '').split('&'); for(var i=0, l=query.length; i<l; i++) { var aux = decodeURIComponent(query[i]).split('='); $_GET[aux[0]] = aux[1]; } }
			
			//Get the fileName from the GET variable
			var fileName = $_GET['fileName']; 		
		
			//Save the file
			function doSave() {
				postAjax("?",{"save":"1","fileName":fileName,"content":document.getElementById('editor').value},function(data) {
					
					//Validate the JSON and handle errors.
					try { data = JSON.parse(data); } 
					catch(e) { console.log(data); data = {desc:'An unknown error occurred.'}; }
					
						document.getElementById('saveStatus').innerHTML = ' - ' + data.desc;
						
						window.setTimeout(function(){
							$('#saveStatus').fadeOut(
								function() {
									document.getElementById('saveStatus').innerHTML = ''; 
									document.getElementById('saveStatus').style['display']='inline';
								}
							);
						},3000);
					
				});
			}
			
			//Let control-s save
			$(document).keydown(function(e) {if((e.which == '115' || e.which == '83' ) && (e.ctrlKey || e.metaKey)){ e.preventDefault(); doSave(); }}); 
		</script>
	</head>
	
	<body>
	
		<div id = 'menubar'>
			File: <a href = '#' target = '_BLANK' id = 'fileLink'></a>
			 | 
			<input type = 'button' onclick = 'doSave();' value = 'Save' style = 'padding:0px;'>
			 / 
			<a href = 'index.php'>Back</a>
			  
			<i id = 'saveStatus'></i>
		</div>
		
		<!-- The textarea where all the editing actually happens. Populated by AJAX below. -->
		<textarea active name = 'newContent' id = 'editor'></textarea>
		
		<!-- Enable tabbing in the textarea -->
		<script>tabOverride.set(document.getElementById('editor'));</script>
		
		<!-- Fill in all the blanks -->
		<script>
		
			//Populate filename text and link
			document.getElementById("fileLink").innerHTML = fileName;
			document.getElementById("fileLink").href = fileName;
			
			//Set page title
			document.title = "toitText -- Editing: '" + fileName + "'.";
			
			//Populate textarea
			postAjax("?",{"retrieve":"1","fileName":fileName},function(data) {
				document.getElementById("editor").innerHTML = data;
			});
			
		</script>
			
		<div>
			<small>
				toitText.php is a lightweight text editor by <a href = 'http://www.benergize.com' target = '_BLANK'>Ben Ehrlich</a>.<br/>
				toitText uses <a href = 'https://jquery.com'>jQuery</a> and <a href = 'https://github.com/wjbryant/taboverride'>tabeoverride.js</a>.
			</small>
		</div>
	</body>
</html>