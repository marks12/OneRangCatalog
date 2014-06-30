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

class OneRangCatalogController extends AbstractActionController
{
    public function indexAction()
    {
    	$vm = new ViewModel();
    	 
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
    	 
    	return array("page"=>$this->getEvent()->getRouteMatch()->getParam('page'));
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

}
