<?php 
header('Content-Type: text/html; charset=utf-8');

class FileManagement
{
	//Set file root location WITH TRAILING SLASH (write "./" for current folder)
	protected $FileRoot;
	//Trashcan folder name INCLUDING TRAILING SLASH
	protected $TrashCan = "_trash/";

	//File exclusion array, these files or folders will be exempt from indexing or deletion
	protected $FileExclusion = [
		"_trash",
		"index.php",
		"index.html",
		"file-management.php",
		".htaccess",
		".htpasswd",
		"../",
		"./",
		"..",
		".",
		"",
		" ",
	];

	public function __construct ($FileRoot = "./") {
    $this->FileRoot = $FileRoot;

    if(!file_exists($FileRoot . $this->TrashCan)) {
    	if(!mkdir($FileRoot . $this->TrashCan, 0755)){
    		die("Failed to create trashcan folder, try changing folder permissions and try again.");
    	}
    }
  }

	//Return all files in root directory
	public function ReturnFiles($path = "") {
		//Store all files in array
		$Files = [];
		foreach (new DirectoryIterator($this->FileRoot . $path) as $fileInfo) {
			//Ignore dots and trashcan
		    if(in_array($fileInfo->getFilename(), $this->FileExclusion)) continue;
		    
		    $Files[] = array(
		    	'Filename' => $fileInfo->getFilename(), 
		    	'Extension' => $fileInfo->getExtension(),
		    	'IsFile' => ($fileInfo->isFile() == 1 ? 'true' : 'false'), 
		    	'Path' => str_replace('\\', '/', $fileInfo->getPathname()),
		    	'Size' => $fileInfo->getSize(),
		    	'SizeRounded' => $this->formatBytes($fileInfo->getSize(), $precision = 0),
		    	'LastModified' => $fileInfo->getMTime(),
		    	'LastModifiedDate' => gmdate("Y-m-d H:i:s", $fileInfo->getMTime())
		    );
		}
		return $Files;
	}

	//Move a file to trashcan
	public function DeleteFile($file) {
			if(in_array($file, $this->FileExclusion)) {
				return false;
			}
			else {
			
				if(file_exists($this->FileRoot . $file)) {
					rename($this->FileRoot . $file, $this->FileRoot . $this->TrashCan . $file);
					return true;
				}
				else {
					return false;
				}
			}
	}

	//Restore file from trashcan
	public function RestoreFile($file) {
		if(in_array($file, $this->FileExclusion)) {
			return false;
		}
		else {
			if(file_exists($this->FileRoot . $this->TrashCan . $file)) {
				rename($this->FileRoot . $this->TrashCan . $file, $this->FileRoot . $file);
				return true;
			}
			else {
				return false;
			}
		}
	}

	//Upload file (use POST-name when calling function)
	public function UploadFile($filename) {
		if(isset($_POST[$filename])) {
			$file = basename($_FILES[$filename]['name']);
			$file = $this->FileRoot . $this->formatFilename($file);
			move_uploaded_file($_FILES[$filename]['tmp_name'], $file);
		}
		else return false;
	}

	//Create folder
	public function CreateFolder($path) {
		$path = $this->FileRoot . $path;
		if(!file_exists($path) && !is_dir($path)) {
			mkdir($path, 0777, true);
			return true;
		}
		else {
			return false;
		}
	}

	public function DeleteFolder($path) {
		if($this->is_dir_empty($path)) rmdir($path);
		else {
			foreach (new DirectoryIterator($this->FileRoot . $path) as $fileInfo) {
				if($fileInfo->isDot()) continue;
		    	if(in_array($fileInfo->getFilename(), $this->FileExclusion)) continue;
				
				//Delete files in folder
				$this->DeleteFile($path.$fileInfo);
				echo "$fileInfo<br>";
			}
		}
	}

	public function EmptyTrashCan() {
		foreach (new DirectoryIterator($this->FileRoot . "/" . $this->TrashCan) as $fileInfo) {
			if($fileInfo->isDot()) continue;
		    if(in_array($fileInfo->getFilename(), $this->FileExclusion)) continue;
		    //This can not be restored
		    unlink($fileInfo->getPathname());
		}
	}

	// http://stackoverflow.com/questions/2510434/format-bytes-to-kilobytes-megabytes-gigabytes
	private function formatBytes($size, $precision = 2)
	{
		if($size === 0) return 0;
	    $base = log($size, 1024);
	    $suffixes = array('', 'KB', 'MB', 'GB', 'TB');   
	    return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
	}

	private function formatUploadFile($file) {
		$fileArray["Ext"] = pathinfo($file, PATHINFO_EXTENSION);
		$fileArray["Filename"] = basename($file, "." . $fileArray["Ext"]);
		return $fileArray;
	}

	private function formatFilename($file) {
		if(file_exists($this->FileRoot . $file)) {
			$filename = $this->formatUploadFile($file)["Filename"];
			$ext = $this->formatUploadFile($file)["Ext"];
			$renamed = false;
			$i = 1;
			while (!$renamed) {
				if(!file_exists($this->FileRoot . $filename . " (".$i.")." . $ext)) {
					$file = $filename . " (".$i.")." . $ext;
					$renamed = true;
				}
				$i++;
			}
			return $file;
		}
		else {
			return $file;
		}
	}

	//http://stackoverflow.com/questions/7497733/how-can-use-php-to-check-if-a-directory-is-empty
	private function is_dir_empty($dir) {
		if (!is_readable($dir)) return NULL; 
		$handle = opendir($dir);
		while (false !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != "..") {
				return FALSE;
			}
		}
		return TRUE;
	}
}

