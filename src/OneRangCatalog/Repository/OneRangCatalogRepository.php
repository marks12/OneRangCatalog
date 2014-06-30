<?php

namespace OneRangCatalog\Repository;

use Doctrine\ORM\EntityRepository;

class OneRangCatalogRepository extends EntityRepository
{
	public function getFilteredOneRangCatalog($category_name, $em)
	{
		
		return $em->createQuery("SELECT c, ca FROM OneRangCatalog\Entity\OneRangCatalog c JOIN c.category ca WHERE ca.name = '$category_name'")
    			;
	}
}