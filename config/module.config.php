<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'OneRangCatalog\Controller\OneRangCatalog' => 'OneRangCatalog\Controller\OneRangCatalogController',
        ),
    ),
    'router' => array(
        'routes' => array(
       		'zfcadmin' => array(
   				'child_routes' => array(
			            'one-rang-catalog' => array(
			                'type'    => 'Literal',
			                'options' => array(
			                    // Change this to something specific to your module
			                    'route'    => '/oneRangCatalog',
			                    'defaults' => array(
			                        // Change this value to reflect the namespace in which
			                        // the controllers for your module are found
			                        '__NAMESPACE__' => 'OneRangCatalog\Controller',
			                        'controller'    => 'OneRangCatalog',
			                        'action'        => 'index',
			                    ),
			                ),
			                'may_terminate' => true,
			                'child_routes' => array(
			                    // This route is a sane default when developing a module;
			                    // as you solidify the routes for your module, however,
			                    // you may want to remove it and replace it with more
			                    // specific routes.
			                    'default' => array(
			                        'type'    => 'Segment',
			                        'options' => array(
			                            'route'    => '/[:action[/:id[/:page]]]',
			                            'constraints' => array(
			                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
			                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
			                            ),
			                            'defaults' => array(
			                            	'page'		=> '0',
		                            		'__NAMESPACE__' => 'OneRangCatalog\Controller',
		                            		'controller'    => 'OneRangCatalog',
			                            ),
			                        ),
			                    ),
		                		'paginator' => array(
		                				'type'    => 'Segment',
		                				'options' => array(
		                						'route'    => '/[:page]',
		                						'constraints' => array(
		                								'page'		 => '[0-9]*',
		                						),
		                						'defaults' => array(
		                						),
		                				),
		                		),
			                ),
			            ),
	                ),
	            ),
        ),
    ),
	'navigation' => array(
			'admin' => array(
					'one-rang-catalog' => array(
							'label' => 'Каталог услуг',
							'route' => 'zfcadmin/one-rang-catalog',
					),
			),
	),
	'view_manager' => array(
        'template_path_stack' => array(
            'OneRangCatalog' => __DIR__ . '/../view',
        ),
    ),
	'doctrine' => array(
			'driver' => array(
					'onerangcatalog_entities' => array(
							'class' =>'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
							'cache' => 'array',
							'paths' => array(__DIR__ . '/../src/OneRangCatalog/Entity'),
					),
					'orm_default' => array(
							'drivers' => array(
									'OneRangCatalog\Entity' => 'onerangcatalog_entities',
							),
					),
			),
	),
);
