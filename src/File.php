<?php 
namespace Pondol\Files;

class File{
/**
     * Create a folder
     * @param $path path/of/folder/to/create
     */
    public function mkfolder($path){## 폴더 생성
        //if(!file_exists($path)){
        if(!is_dir($path)){
            $result = @mkdir($path, 0777);
            if(!$result){ 
                return false;
            }else return true;
        }
    }
    
    /**
    * Create a folders
    * if not exist a foler to final path, this create each folder
    * @param $path /home/a/b/c/d
    * if not exist first folder "home" then create "home" then check next if not exist then create next....
    */
    public function mkfolders($path){
        $exFolder = explode("/", $path);
        $curpath = "";
        if(is_array($exFolder)) foreach ($exFolder as $key => $value) {
            if($value && $value != "." && $value != ".."){
                $curpath = $curpath."/".$value;
                $this->mkfolder($curpath);
            }else if($value == "." || $value == ".."){
                $curpath = $value;
            }
        } 
    }
    
    
    /**
     * Move Folder
     * @param $source form : path/of/source/folder
     * @param #dest to : path/of/destination/folder
     */
    public function MoveFolder($source,$dest){ 
        $this->CopyFiles($source,$dest);
        $this->RemoveFiles($source);
    }
    
    /**
     * return files and diretoris existing in given directory
     * @param $path String
     * @return Array file and folder List
     */
    public function dirList($path){
        if (is_dir($path)){
            $folder = opendir($path);
            while($file = readdir($folder)){
                if ($file == '.' || $file == '..') continue;
                $rtn[]   = $file;
            }
            closedir($folder);
        }
        
        return $rtn;
    }
    
    /**
     * In some case (NFT System), is_file will not work, then use is_file_lfs 
     */
    public function is_file_lfs($path){
        exec('[ -f "'.$path.'" ]', $tmp, $ret);
        return $ret == 0;
    }
        
        
    /**
     * Copy File from source foler to destination folder
     * @param $file String filename
     * @param $source_path String path/of/source/folder
     * @param $target_path String path/of/target/folder
     */
    public function cpfile($file, $source_path, $target_path){## 폴더 생성
        copy($source_path."/".$file, $target_path."/".$file);
    }
    
    /**
     * Copy file from source folder to dest. foler with some options
     * @param $source_path String path/of/source/folder/and/filename
     * @param $target_path String path/of/target/folder
     * @param $arr Array : $arr["overwrite"] : true or false
     */
    public function CopyFile($source_path, $target_path, $arr=null){## 폴더 생성
        $filename   = substr(strrchr($target_path, "/"), 1);
        $filepath   = substr($target_path, 0, -strlen($filename));
        if(is_array($arr)){
            if($arr["overwrite"] == true && is_file($target_path) ){
                $filename   = "_".$filename;
                copy($source_path, $filepath.$filename);
            }else{
                copy($source_path, $target_path);
            }
        
        }else{
            copy($source_path, $target_path);
        }
        
        return $filename;
    }
    
    /**
     * Copy multi files(actually copying folder)
     * @param $source String path/of/source/folder
     * @param $dest String path/of/target/folder
     */
    public function CopyFiles($source,$dest){   
        $this->mkfolders($dest);
        $folder = opendir($source);
        while($file = readdir($folder)){
            if ($file == '.' || $file == '..') continue;
            
            if(is_dir($source.'/'.$file)){
                mkdir($dest.'/'.$file,0777);
                $this->CopyFiles($source.'/'.$file,$dest.'/'.$file);
            }else copy($source.'/'.$file,$dest.'/'.$file);
        }
        closedir($folder);
        return 1;
    }
    
    /**
     * This method same as dirList but return only files
     * @param $path String
     * @return Array file and folder List
     */
    public function readFileList($path){
        $open_file = opendir($path);
        
        while($opendir = readdir($open_file)) {
            if(($opendir != ".") && ($opendir != "..") && is_file($targetdir."/".$opendir)) {
                //$createTime   = filemtime($path.$opendir); 
                $fileArr[] = $opendir;
            }
        }
        closedir($open_file);
        return $fileArr;
    
    }
    
    
    /**
     * Remove a file
     * @param $source String path/to/source/filename
     */
    public function RemoveFile($source){
        if (is_file($source)) unlink($source);
        return 1;
    }
    
    /**
     * Remove all files and folders lower depth then a given folder  
     * action this means Remoing Folder
     * @param $source String path/to/source/folder
     */
    public function RemoveFiles($source){
        if (is_dir($source)){
            $folder = opendir($source);
            while($file = readdir($folder)){
                if ($file == '.' || $file == '..')continue;
                if(is_dir($source.'/'.$file)) $this->RemoveFiles($source.'/'.$file);
                else unlink($source.'/'.$file);
            }
            closedir($folder);
            rmdir($source);
        }
        return 1;
    }
    
    

    /**
    * alias of MoveFolder
    */
    public function moveFiles($src, $desc){
        $this->MoveFolder($src, $desc);
    }
    
    
    /**
     * Change window's path to unix(linux)'s
     */
    public function isfile($file){ //윈도우등의 경로를 바로 바꾸어 줌
        return preg_match('/^[^.^:^?^\-][^:^?]*\.(?i)' . $this->getexts() . '$/',$file); 
        //first character cannot be . : ? - subsequent characters can't be a : ? 
        //then a . character and must end with one of your extentions 
        //getexts() can be replaced with your extentions pattern 
    } 
    
    private function getexts(){ 
        //list acceptable file extensions here 
        return '(app|avi|doc|docx|exe|ico|mid|midi|mov|mp3| 
        mpg|mpeg|pdf|psd|qt|ra|ram|rm|rtf|txt|wav|word|xls)'; 
    } 
    
    
    /**
     * extract filename and extension from filename
     * @param $path String /path/to/filename
     * @return $rtn Array full_filename, extension, filename not having extension
     */
     public function extfile($path){
        $rtn["fullfilename"]    = substr(strrchr($path, "/"), 1);
        $rtn["ext"]             = substr(strrchr($path, "."), 1);
        $rtn["filename"]        = substr($rtn["fullfilename"], 0, -(strlen($rtn["ext"])+1));
        return $rtn;
     }
    
} 