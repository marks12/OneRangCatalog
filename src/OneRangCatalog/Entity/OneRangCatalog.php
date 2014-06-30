<?php
namespace OneRangCatalog\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;

/** 
 * @ORM\Entity(repositoryClass="OneRangCatalog\Repository\OneRangCatalogRepository") 
 * @ORM\HasLifecycleCallbacks()
 * 
 */
class OneRangCatalog {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;
	
	/** @ORM\Column(type="string") */
	protected $title;

	/** @ORM\Column(type="text") */
	protected $content;

	/** @ORM\Column(type="boolean") */
	protected $disabled;
	
	/** @ORM\Column(type="string", nullable=true) */
	protected $image;

    /** @ORM\ManyToMany(targetEntity="Category") */
    protected $category;
	
    public function __construct()
    {
    	$this->category = new ArrayCollection();
    }
    
    /**
     * Magic getter
     * @param $property
     * @return mixed
     */
    public function __get($key)
    {
    	if(property_exists($this, $key))
    	return $this->{$key};
    	else
    	die("Requested property {$key} not exists");
    }
    
    /**
     * Magic setter
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
    	if(method_exists($this, 'set'.$key))
    	{
    		return $this->{'set'.$key}($value);
    	}
    	
    	if(property_exists($this, $key))
    	$this->{$key} = $value;
    	else
    	die("Requested property {$key} not exists");
    }
    
    /**
     * Real path to uploaded file
     * @param unknown $file_path
     */
    private function setimage($file_array)
    {

    	if(
    		!isset($file_array['tmp_name']) 
			|| !isset($file_array['name'])
			|| !isset($file_array['type'])
			|| !in_array($file_array['type'],array('image/png','image/jpeg','image/jpg','image/gif'))
			)
    	return false;
    	
    	$file_path = $file_array['tmp_name'];
    	
    	$dir_path = $this->getDirPath();

        $count_files = 0;
        
        $dir = opendir($dir_path);
        while (readdir($dir))
        	$count_files++;
        closedir($dir);
        
        
        $file_name = "({$count_files})".$file_array['name'];
        

        if($this->image)
        	$this->deleteImage();
         
        if(file_exists($file_path))
        	if(move_uploaded_file($file_path, $dir_path."/".$file_name))
        		$uploaded = true;
        	else 
        		$uploaded = false;
        
        if(property_exists($this, 'image'))
    		$this->image = $file_name;
        else 
        	exit("in ".__CLASS__." property image dont exists!");
        
        return $uploaded;
    }
    
    public function deleteImage()
    {
    	$dir_path = $this->getDirPath();
    	
    	if(mb_strlen($this->image))
	    	if(file_exists($dir_path."/".$this->image) && !is_dir($dir_path."/".$this->image))
	    		if(unlink($dir_path."/".$this->image))
	    		{	
	    			return true;
	    		}else 
	    		{
	    			return false;
	    		}
	   	return true;
    }
    
    public function getDirPath()
    {
    	$dir_path = __DIR__."/../../../../../../public_html/img/catalog";
    	
    	if(!file_exists($dir_path))
    		mkdir($dir_path);
    	
    	return $dir_path;
    }
}