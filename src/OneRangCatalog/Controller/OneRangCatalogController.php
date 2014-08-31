<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/OneRangCatalog for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace OneRangCatalog\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\Common\Collections\ArrayCollection;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;
use OneRangCatalog\Entity\OneRangCatalog;
use Zend\Escaper\Escaper;
use OneRangCatalog\Entity\Category;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Http\ClientStatic;
use Zend\Dom\Query;

class OneRangCatalogController extends AbstractActionController 
{

	private $parsedir = "data/parse/site1/";
	
	private $parsefile = "catalog.txt";
	
    public function indexAction()
    {
    	$vm = new ViewModel();
    	
    	$auto_config = $this->getServiceLocator()->get('Config')['OneRangCatalog'];
    	$vm->setVariable('config',$auto_config);
    	
    	$entityManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    	$repository = $entityManager->getRepository('OneRangCatalog\Entity\OneRangCatalog');
    	
    	$adapter = new DoctrineAdapter(new ORMPaginator($repository->createQueryBuilder('OneRangCatalog')));
    	$paginator = new Paginator($adapter);
    	
    	$page = (int)$this->getEvent()->getRouteMatch()->getParam('page');
    	
    	$paginator->setCurrentPageNumber($page)
    			  ->setItemCountPerPage(10);
    	
    	$vm->setVariable('page',$page);
    	$vm->setVariable('paginator',$paginator);
    	$vm->setVariable('catalog',$this->getCatalog());
    	$vm->setVariable('route',$this->getEvent()->getRouteMatch()->getMatchedRouteName());
		
    	return $vm;
    }

    public function addAction()
    {
    	$request = $this->getRequest();
    	if ($request->isPost()) {
    
    		if(isset($request->getPost()->title) && $request->getPost()->content)
    		{
    			$objectManager = $this
    			->getServiceLocator()
    			->get('Doctrine\ORM\EntityManager');
    			 
    			$catalog = new OneRangCatalog();
    
    			$catalog->__set("title", $request->getPost()->title);
    			$catalog->__set("content", $request->getPost()->content);
    			$catalog->__set("disabled", false);
    			$objectManager->persist($catalog);
    			 
    			foreach ($request->getPost()->category as $k=>$v)
    			{
    				$category = new Category();
    				$category->__set('name',htmlspecialchars($v));
    				$objectManager->persist($category);
    
    				$catalog->__get('category')->add($category);
    			}
    			
    			if($request->getFiles() && mb_strlen($request->getFiles()['image']['tmp_name']))
    			{	if(!$catalog->__set('image',$request->getFiles()['image']))
    					exit('File upload error');
    			}else
    				$catalog->__set('image','none');

    			
    			$objectManager->flush();
    
    			return $this->redirect()->toRoute("zfcadmin/one-rang-catalog/paginator",array("page"=>$this->getEvent()->getRouteMatch()->getParam('page')));
    		}
    
    	}
    	
    	if($this->getServiceLocator()->get('Config')['OneRangCatalog'] === NULL)
    		exit("You must create Config\OneRangCatalog first");
    		
    	$auto_config = $this->getServiceLocator()->get('Config')['OneRangCatalog'];
    	
    	return array("page"=>$this->getEvent()->getRouteMatch()->getParam('page'),"config"=>$auto_config);
    }

    public function editAction()
    {
    	$request = $this->getRequest();
    	$objectManager = $this
    	->getServiceLocator()
    	->get('Doctrine\ORM\EntityManager');
    	$vm =  new ViewModel();
    	
    	$OneRangCatalog = $objectManager
    	->getRepository('OneRangCatalog\Entity\OneRangCatalog')
    	->findOneBy(
    			array(
    					'id' => (int)$this->getEvent()->getRouteMatch()->getParam('id')
    			)
    	);
    	
    	if ($request->isPost()) {
    		
    		if($request->getFiles() && mb_strlen($request->getFiles()['image']['tmp_name']))
	    		if(!$OneRangCatalog->__set('image',$request->getFiles()['image']))
	    			exit('File upload error');
    		
    		
    		if(isset($request->getPost()->title) && $request->getPost()->content)
    		{
    			$OneRangCatalog->__set("title", $request->getPost()->title);
    			$OneRangCatalog->__set("content", $request->getPost()->content);
    			$objectManager->persist($OneRangCatalog);
    			
    			foreach ($OneRangCatalog->__get('category') as $catalog)
    			{
    				$catalog_current = $objectManager
    				->getRepository('OneRangCatalog\Entity\Category')
    				->findOneBy(
    						array(
    								'id' => (int)$catalog->__get('id')
    						)
    				);
    				$objectManager->remove($catalog_current);
    			}
    			
    			
    			$objectManager->persist($OneRangCatalog);
    			 
    			foreach ($request->getPost()->category as $k=>$v)
    			{
    				$category = new Category();
    				$category->__set('name',htmlspecialchars($v));
    				$objectManager->persist($category);
    			
    				$OneRangCatalog->__get('category')->add($category);
    			}
    			
    			$objectManager->flush();
    	
    			return $this->redirect()->toRoute("zfcadmin/one-rang-catalog/paginator",array('page'=>(int)$this->getEvent()->getRouteMatch()->getParam('page')));
    		}
    	
    	}
    	else 
    	{
    		$vm->setVariable('page',(int)$this->getEvent()->getRouteMatch()->getParam('page'));
    		
    		$vm->setVariable('id',$OneRangCatalog->__get("id"));
    		$vm->setVariable('title',$OneRangCatalog->__get("title"));
    		$vm->setVariable('content',$OneRangCatalog->__get("content"));
    		$vm->setVariable('image',$OneRangCatalog->__get("image"));
    		$vm->setVariable('category',$OneRangCatalog->__get("category"));
    		
    	}
    	 
    	return $vm;
    }
    
    public function deleteAction()
    {

        $request = $this->getRequest();
        $objectManager = $this
        ->getServiceLocator()
        ->get('Doctrine\ORM\EntityManager');

        
        $catalog = $objectManager
        ->getRepository('OneRangCatalog\Entity\OneRangCatalog')
        ->findOneBy(
        		array(
        				'id' => (int)$this->getEvent()->getRouteMatch()->getParam('id')
        		));

        $catalog->deleteImage();
        
    	$objectManager->remove($catalog);
    	$objectManager->flush();
        
        return $this->redirect()->toRoute("zfcadmin/one-rang-catalog/paginator",array('page'=>$this->getEvent()->getRouteMatch()->getParam('page')));
        
    	return array();
    }
    
    public function parseAction()
    {

    	$dir = $this->parsedir;
    	$file = $this->parsefile;

    	

    	if(!file_exists($dir.$file))
    	{
    		$response = ClientStatic::get('http://z500proekty.ru/doma.html?r=aaQ&limit=all');
    		 
    		$dom = new Query($response->getBody());
    		 
    		// Получаем список проектов в правой части
    		$results = $dom->execute('.small-views .item a');
    		
    		if(!file_exists("data"))
    			mkdir("data");
    		
    		if(!file_exists("data/parse"))
    			mkdir("data/parse");
    		
    		if(!file_exists("data/parse/site1"))
    			mkdir("data/parse/site1");
			
    		$fp = fopen("$dir$file", "w");
    		
    		$i = 0;
    		foreach ($results as $result) {
    			$href = $result->getAttribute('href')."\n";
    			
    			if(preg_match("/([a-z0-9_]+)\.html/is", $href,$href_arr))
    			{
					$data_to_save[$href_arr[1].".html\n"] = true;
    				$i++;
    			}
    		}
    		
    		if(isset($data_to_save) && is_array($data_to_save))
    			foreach ($data_to_save as $k=>$v)
	   				fwrite($fp, $k);
    			    
    		fclose($fp);
			
    		echo "We are save all catalog\n";
    		echo "We have ".count($data_to_save)." elements\n";
    	}
    	
    	return "Done! Module works!!!\n";
    }
    
    /**
     * http://z500proekty.ru/thumb.php?src=res/wizualizacje/Z1/Z1_view1.jpg&size=350&height=197
     * http://z500proekty.ru/thumb.php?src=res/wizualizacje/Zx29/Zx29_k_view1.jpg&size=350&height=197
     * http://z500proekty.ru/thumb.php?src=res/wizualizacje/Zx41/Zx41_v1_view1.jpg&size=350&height=197
     * http://z500proekty.ru/thumb.php?src=res/wizualizacje/Zx38/Zx38_v1_view1.jpg&size=353&height=265
     * http://z500proekty.ru/thumb.php?src=res/wizualizacje/Zx38/Zx38_view1.jpg&size=353&height=265
     */
    
    public function cleardataAction()
    {
    	$dir = $this->parsedir;
    	$file = $this->parsefile;
    	
    	if(file_exists($dir.$file))
    		unlink($dir.$file);

    	return "Catalog file deleted\n";
    }
    
    public function getfilesAction()
    {
    	echo "get files Start\n";

    	$dir = $this->parsedir;
    	$file = $this->parsefile;
    	
    	$site = "";
    	
    	echo "Work with $dir$file\n";
		
    	$fp = fopen("$dir$file", "r");

    	$str_num = 0;
    	
    	$urls = array();
    	
    	if(!file_exists($dir."thumb"))
    		mkdir($dir."thumb");
    		
    	
    	while ($str = fgets($fp))
    	{
    		$str_num++;
    		
    		$str_arr = explode(".html", $str);
    		
    		$_u = explode("_", $str_arr[0]);
    		
    		$urls[] = array(
    			"page" => "http://z500proekty.ru/projekt/".$str,
    			"thumb" => array(
    				"main" => "http://z500proekty.ru/thumb.php?src=res/wizualizacje/Z".substr($_u[0],1)."/Z".substr($str_arr[0], 1,1).strtoupper(substr($str_arr[0], 2))."_view1.jpg&size=353&height=265",
    				"fasad"=> array(
    					"front" => "http://z500proekty.ru/thumb.php?src=res/elewacje/Z".substr($_u[0],1)."/Z".strtoupper(substr($str_arr[0], 1))."_front.png&size=220",
    					"tyl" => "http://z500proekty.ru/thumb.php?src=res/elewacje/Z".substr($_u[0],1)."/Z".strtoupper(substr($str_arr[0], 1))."_tyl.png&size=220",
    					"bok1" => "http://z500proekty.ru/thumb.php?src=res/elewacje/Z".substr($_u[0],1)."/Z".strtoupper(substr($str_arr[0], 1))."_bok1.png&size=220",
    					"bok2" => "http://z500proekty.ru/thumb.php?src=res/elewacje/Z".substr($_u[0],1)."/Z".strtoupper(substr($str_arr[0], 1))."_bok2.png&size=220",
    				),
    			),
    		);
    	}
    	
    	/**
    	 * Фасады
    	 * http://z500proekty.ru/thumb.php?src=res/elewacje/Z38/Z38_D_L_GP_front.png&size=220
    	 * http://z500proekty.ru/thumb.php?src=res/elewacje/Z7/Z38_P_35_front.png&size=220
    	 * Схема первый этаж
    	 * http://z500proekty.ru/thumb.php?src=res/rzuty/Z53/Z53_rzut1.png&size=530
    	 * 		 второй этаж
    	 * http://z500proekty.ru/thumb.php?src=res/rzuty/Z53/Z53_rzut2.png&size=530
    	 * 		 подвал
    	 * http://z500proekty.ru/thumb.php?src=res/rzuty/Z53/Z53_rzut0.png&size=530
    	 * Минимальные размеры участка
    	 * http://z500proekty.ru/thumb.php?src=res/rzuty/Z1/Z1_bl_dzialka.png&size=220
    	 */
    	
    	var_dump($urls[59]);
    	
    	fclose($fp);
    	
    	echo "We have $str_num row of catalog elements\n";

    	echo "==========================================\n";
    	echo "We start getting files from sourse server ";
    	
    	
    	return "Get files action finished\n";
    }
    
    private function getCatalog()
    {
    	$objectManager = $this
    	->getServiceLocator()
    	->get('Doctrine\ORM\EntityManager');
    	
    	$OneRangCatalog = $objectManager
    	->getRepository('OneRangCatalog\Entity\OneRangCatalog')
    	->findAll();
    	
    	return $OneRangCatalog;
    }
    
//     public function getCatalogByPage()
//     {

    	
//     	$entityManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
//     	$repository = $entityManager->getRepository('OneRangCatalog\Entity\OneRangCatalog');
//     	$adapter = new DoctrineAdapter(new ORMPaginator($repository->createQueryBuilder('OneRangCatalog')));
//     	$paginator = new Paginator($adapter);
//     	$paginator->setDefaultItemCountPerPage(10);
    	
//     	$page = (int)$this->getEvent()->getRouteMatch()->getParam('page');
    	
    	
//     	if($page) $paginator->setCurrentPageNumber($page);
    	
//     	return $paginator;
//     }
    public function getCatalogByPage()
    {
    	$entityManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    	
    	$category = (int)$this->getEvent()->getRouteMatch()->getParam('category');
    	if($category)
    	{
    		$category_name = $entityManager->getRepository('OneRangCatalog\Entity\Category')->findOneBy(array('id' => $category))->__get('name');

    		$repository = $entityManager->getRepository('OneRangCatalog\Entity\OneRangCatalog')->getFilteredOneRangCatalog($category_name,$entityManager);
    		
    		$adapter = new DoctrineAdapter(new ORMPaginator($repository, $fetchJoinCollection = true));

    	}
    	else 
    	{
    		$repository = $entityManager->getRepository('OneRangCatalog\Entity\OneRangCatalog');
    		$adapter = new DoctrineAdapter(new ORMPaginator($repository->createQueryBuilder('OneRangCatalog')));
    	}
    	
    	$paginator = new Paginator($adapter);
    	
    	$paginator->setDefaultItemCountPerPage(10);
    	 
    	$page = (int)$this->getEvent()->getRouteMatch()->getParam('page');
    	 
    	if($page) $paginator->setCurrentPageNumber($page);
    	
    	return $paginator;
    }
    
    public function getCategory()
    {
    	$entityManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    	 
    	$dql = "SELECT c from OneRangCatalog\Entity\Category c group by c.name";
    	//    		where n.start_date<=:date and n.end_date>=:date and n.disabled_news=:disabled_news
    	$query = $entityManager->createQuery($dql);
   	 
    	return  $query->getResult();
    }

    public function getCatalogElement($id)
    {
    	$entityManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    	
    	return $entityManager
		        ->getRepository('OneRangCatalog\Entity\OneRangCatalog')
		        ->findOneBy(
		        		array(
		        				'id' => (int)$id
		        		));
    	
    }
    public function getVisibleCatalog()
    {
    	if(!method_exists($this, 'getServiceLocator'))
    	$entityManager = $this->serviceManager->get('doctrine.entitymanager.orm_default');
    	else
    	$entityManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    	
    	return $entityManager
		        ->getRepository('OneRangCatalog\Entity\OneRangCatalog')
		        ->findBy(
		        		array(
		        				'disabled' => 0
		        		));
    	
    }
}
