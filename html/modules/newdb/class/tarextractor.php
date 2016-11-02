<?php
include_once XOOPS_ROOT_PATH.'/class/class.tar.php';

/**
 * class TarExtractor
 *
 * this provides function of extracting tar archive.
 */

class TarExtractor extends tar{

	/**
	 * public
	 */
	var $file_limit;

	// for debug
	var $show_fname;

	/**
	 * private
	 */
	var $archive;
	var $extract_path;
	var $filename;
	var $error;
	var	$directory;
	var	$dir_path;

	/**
	 * Class Constructor
	 */
	function TarExtractor(){
	
		$this->file_limit = 288000000;
		$this->show_fname = 0;
		$this->archive = '';
		$this->extract_path = '';
		$this->filename = '';
		$this->error = '';
		$this->directory = array();
		$this->dir_path = '';
		
		return true;
	}
	
 	/**
	 * setArchive
   *
   * @param string $archive (tar archive name)
   * @param string $extract_path (base directory path)
   * @access public
   * @return bool
	 */
	function setArchive($archive, $extract_path){

		$this->archive = $archive;
		$this->extract_path = $extract_path;

		if(substr($extract_path, -1) == '/'){
			$this->extract_path = substr($extract_path, 0, -1);
		}
		if(!file_exists($this->archive)){
			$this->error = $this->archive.' does not exists. (tarextractor.php line '.__LINE__.')<br>';
			return false;

		}elseif(filesize($this->archive) > $this->file_limit){
			$this->error = $this->archive.' size is too large. (tarextractor.php line '.__LINE__.')<br>';
			return false;
		}
		return true;
	}
	
		
 	/**
	 * doRegExtract
   *
   * @access public
   * @return bool
   *
	 * this extract tar archive into extract directory and classify its
	 * contents (files, directories) into special directories.
	 */
	function doRegExtract($label_id, $suffix=''){
	
		#$dir = explode('.', $dataname);
		#$dir = $dir[0];
		$dir = $label_id;
	
		$suf = array();
		if(!empty($suffix)){
			$suf = explode('|', $suffix);
		}

		if($this->openTAR($this->archive)){
			foreach($this->files as $file){

				#	make directories
				$this->directory = array();
				$dammy = explode('/', $file['name']);
				$file_path = '';
				for($i=0; $i<count($dammy); $i++){
					if(!$i){
						$file_path.= $dir.'/';
					}else{
						$file_path.= $dammy[$i].'/';
					}
				}
				$file_path = substr($file_path, 0,-1);
				$dammy = explode('/', $file_path);

				# extract/dataname/thumbnail/...
				if(isset($dammy[1]) && $dammy[1] == 'thumbnail'){
					$this->directory = explode('/', $file_path);
				
				# insert 'data' directory
				# extract/dataname/data/...
				}else{
					$this->directory[] = $dammy[0];
					$this->directory[] = 'data';
					for($i=1; $i<count($dammy); $i++){
						$this->directory[] = $dammy[$i];
					}
				}
				$num = count($this->directory) - 1;
	
				for($i=0; $i<$num; $i++){
					$this->dir_path = $this->extract_path.'/';
					for($j=0; $j<$i; $j++){
						$this->dir_path .= $this->directory[$j].'/';
					}
					$this->dir_path.= $this->directory[$i];
				
					if(!is_dir($this->dir_path)){
						if(!mkdir($this->dir_path, 0777)){
							$this->error = 'mkdir ('.$this->dir_path.') false. (tarextractor.php line '.__LINE__.')<br>';
							return false;
						}
					}
				}

				# make files;
				$this->filename = $this->dir_path.'/'.$this->directory[$num];
				if($this->show_fname) echo str_replace($this->extract_path.'/', '', $this->filename).'<br>';
				
				if(!file_exists($this->filename)){
					# suffix check
					if(!empty($suffix)){
						$tmp = explode('.', $this->filename);
						$tmp_suf = $tmp[count($tmp)-1];
						if(in_array($tmp_suf, $suf)){
							$fp = fopen($this->filename,"x");
							fputs($fp,$file['file']);
							fclose($fp);
						}
					}else{
						$fp = fopen($this->filename,"x");
						fputs($fp,$file['file']);
						fclose($fp);
					}
				}
			}
			return true;
		
		}else{
			$this->error = 'openTAR error. (tarextractor.php line '.__LINE__.')<br>';
			return false;
		}
	}
	

 	/**
	 * doExtract
   *
   * @access public
   * @return bool
   * general function 
	 */
	function doExtract($archive, $extract_path, $suffix=''){
		
		if(!$this->setArchive($archive, $extract_path)) return false;

		$suf = array();
		if(!empty($suffix)){
			$suf = explode('|', $suffix);
		}

		if($this->openTAR($this->archive)){
			foreach($this->files as $file){
			
				$this->filename = $this->extract_path.'/'.$file['name'];
				if($this->show_fname)	echo $file['name'].'<br>';
	
				#	make directories
				$this->directory = explode('/', $file['name']);
				$num = count($this->directory) - 1;
	
				for($i=0; $i<$num; $i++){
					$this->dir_path = $this->extract_path.'/';
					for($j=0; $j<$i; $j++){
						$this->dir_path .= $this->directory[$j].'/';
					}
					$this->dir_path.= $this->directory[$i];
				
					if(!is_dir($this->dir_path)){
						if(!mkdir($this->dir_path, 0777)){
							$this->error = 'mkdir ('.$this->dir_path.') false. (tarextractor.php line '.__LINE__.')<br>';
							return false;
						}
					}
				}
				
				# make files
				if(!file_exists($this->filename)){
					# suffix check
					if(!empty($suffix)){
						$tmp = explode('.', $this->filename);
						$tmp_suf = $tmp[count($tmp)-1];
						if(in_array($tmp_suf, $suf)){
							$fp = fopen($this->filename,"x");
							fputs($fp,$file['file']);
							fclose($fp);
						}
					}else{
						$fp = fopen($this->filename,"x");
						fputs($fp,$file['file']);
						fclose($fp);
					}
				}
			}
			return true;
		
		}else{
			$this->error = 'openTAR error. (tarextractor.php line '.__LINE__.')<br>';
			return false;
		}
	}
	
	
	/**
	 * error
	 *
	 * @access public
	 */
	function error(){
		return $this->error;
	}
}

?>