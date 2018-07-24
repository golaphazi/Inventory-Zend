<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    /*'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'application' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/application',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),*/
	'router' => array(
		'routes' => array(
			'inventory' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/inventory[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'Inventory\Controller\Inventory',
						'action' => 'index',
					),
				),
			),
			'stockentry' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/stockentry[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'Inventory\Controller\StockEntry',
						'action' => 'index',
					),
				),
			),
			'stockinformationedit' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/stockinformationedit[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'Inventory\Controller\StockInformationEdit',
						'action' => 'index',
					),
				),
			),
			'srstockentry' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/srstockentry[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'Inventory\Controller\SRWiseStockDistribution',
						'action' => 'index',
					),
				),
			),
			'retstockentry' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/retstockentry[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'Inventory\Controller\RETWiseStockDistribution',
						'action' => 'index',
					),
				),
			),
			'srstockreturn' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/srstockreturn[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'Inventory\Controller\SRWiseStockReturn',
						'action' => 'index',
					),
				),
			),
			'stockreturn' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/stockreturn[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'Inventory\Controller\StockReturn',
						'action' => 'index',
					),
				),
			),
			'salesreturn' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/salesreturn[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'Inventory\Controller\SalesReturn',
						'action' => 'index',
					),
				),
			),
			'consumptionentry' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/consumptionentry[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'Inventory\Controller\ConsumptionEntry',
						'action' => 'index',
					),
				),
			),
			'purchaseentryedit' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/purchaseentryedit[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'Inventory\Controller\PurchaseEntryEdit',
						'action' => 'index',
					),
				),
			),
			
			
		),
	),
    'controllers' => array(
        'invokables' => array(
            'Inventory\Controller\Inventory' 				=> 'Inventory\Controller\InventoryController',
			'Inventory\Controller\StockEntry' 				=> 'Inventory\Controller\StockEntryController',
			'Inventory\Controller\StockInformationEdit' 	=> 'Inventory\Controller\StockInformationEditController',
			'Inventory\Controller\SRWiseStockDistribution' 	=> 'Inventory\Controller\SRWiseStockDistributionController',
			'Inventory\Controller\RETWiseStockDistribution' => 'Inventory\Controller\RETWiseStockDistributionController',
			'Inventory\Controller\SRWiseStockReturn' 		=> 'Inventory\Controller\SRWiseStockReturnController',
			'Inventory\Controller\StockReturn' 				=> 'Inventory\Controller\StockReturnController',
			'Inventory\Controller\SalesReturn' 				=> 'Inventory\Controller\SalesReturnController',
			'Inventory\Controller\ConsumptionEntry' 		=> 'Inventory\Controller\ConsumptionEntryController',
			'Inventory\Controller\PurchaseEntryEdit' 		=> 'Inventory\Controller\PurchaseEntryEditController',
        ),
    ),
    'view_manager' => array(
		'template_map' => array(
			'Inventory/layout'           => __DIR__ . '/../view/layout/layout.phtml',
		),
		'template_path_stack' => array(
			__DIR__ . '/../view',
		),
	),
);
