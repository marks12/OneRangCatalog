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
			                            'route'    => '/[:action[/:id[/page/:page]]]',
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
   	'console' => array(
   			'router' => array(
   					'routes' => array(
   							'parsesite' => array(
   									'options' => array(
   											'route'    => 'parsesite',
   											'defaults' => array(
   													'controller' => 'OneRangCatalog\Controller\OneRangCatalog',
   													'action'     => 'parse'
   											),
   									),
   							),
   							'getfiles' => array(
   									'options' => array(
   											'route'    => 'getfiles',
   											'defaults' => array(
   													'controller' => 'OneRangCatalog\Controller\OneRangCatalog',
   													'action'     => 'getfiles'
   											),
   									),
   							),
   							'cleardata' => array(
   									'options' => array(
   											'route'    => 'cleardata',
   											'defaults' => array(
   													'controller' => 'OneRangCatalog\Controller\OneRangCatalog',
   													'action'     => 'cleardata'
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
							'route' => 'zfcadmin/one-rang-catalog/paginator',
					),
			),
	),
	'view_manager' => array(
        'template_path_stack' => array(
            'OneRangCatalog' => __DIR__ . '/../view',
        ),
		'template_map' => array(
				'OneRangCatalog/partials/add-button'				=> __DIR__ . '/../view/partials/add-button.phtml',
		),
			
    ),
	'doctrine' => array(
			'driver' => array(
					'onerangcatalog_entities' => array(
							'class' =>'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
							'cache' => 'array',
							'paths' => array(__DIR__ . '/../src/OneRangCatalog/Entity'),
					),
					'onerangcatalog_repo' => array(
							'class' =>'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
							'cache' => 'array',
							'paths' => array(__DIR__ . '/../src/OneRangCatalog/Repository'),
					),
					'orm_default' => array(
							'drivers' => array(
									'OneRangCatalog\Entity' => 'onerangcatalog_entities',
									'OneRangCatalog\Repository' => 'onerangcatalog_repo',
							),
					),
			),
	),
);
