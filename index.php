<?php require 'file-management.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Testing</title>
</head>
<body>
	<?php 
	echo "<pre style='height:200px;overflow:scroll;'>";
	$FM = new FileManagement("./files");
	print_r($FM->ReturnFiles());
	echo "</pre>";

	if($FM->DeleteFile("404.html"))
		echo "True";


	$FM->DeleteFolder("doc/");
	?>

	<form action="file-management.php" method="post" enctype="multipart/form-data">
	    Select image to upload:
	    <input type="file" name="test">
	    <input type="submit" value="Upload Image" name="submit">
	</form>
</body>
</html>