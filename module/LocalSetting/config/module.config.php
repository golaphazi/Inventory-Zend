<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
	'router' => array(
		'routes' => array(
			'localsetting' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/localsetting[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'LocalSetting\Controller\LocalSetting',
						'action' => 'index',
					),
				),
			),
			'supplierinformation' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/supplierinformation[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'LocalSetting\Controller\SupplierInformation',
						'action' => 'index',
					),
				),
			),
			'retailerinformation' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/retailerinformation[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'LocalSetting\Controller\RetailerInformation',
						'action' => 'index',
					),
				),
			),			
			'zoneinformation' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/zoneinformation[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'LocalSetting\Controller\ZoneInformation',
						'action' => 'index',
					),
				),
			),
			'srzonemap' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/srzonemap[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'LocalSetting\Controller\SrZoneMap',
						'action' => 'index',
					),
				),
			),
			'srretailermap' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/srretailermap[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'LocalSetting\Controller\SrRetailerMap',
						'action' => 'index',
					),
				),
			),
			'srtarget' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/srtarget[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'LocalSetting\Controller\SrTarget',
						'action' => 'index',
					),
				),
			),
			
			'category' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/category[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'LocalSetting\Controller\Category',
						'action' => 'index',
					),
				),
			),	
			'categoryprice' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/categoryprice[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'LocalSetting\Controller\CategoryPrice',
						'action' => 'index',
					),
				),
			),
			'suppwisecategory' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/suppwisecategory[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'LocalSetting\Controller\SuppWiseCategory',
						'action' => 'index',
					),
				),
			),
			
			//End 		
		),
	),
    'controllers' => array(
        'invokables' => array(            
			'LocalSetting\Controller\PortfolioType' 					=> 'LocalSetting\Controller\PortfolioTypeController',
			'LocalSetting\Controller\SupplierInformation' 				=> 'LocalSetting\Controller\SupplierInformationController',
			'LocalSetting\Controller\RetailerInformation' 				=> 'LocalSetting\Controller\RetailerInformationController',
			'LocalSetting\Controller\ZoneInformation' 					=> 'LocalSetting\Controller\ZoneInformationController',
			'LocalSetting\Controller\SrZoneMap' 						=> 'LocalSetting\Controller\SrZoneMapController',
			'LocalSetting\Controller\SrRetailerMap' 					=> 'LocalSetting\Controller\SrRetailerMapController',
			'LocalSetting\Controller\SrTarget'	 						=> 'LocalSetting\Controller\SrTargetController',
			'LocalSetting\Controller\Category' 							=> 'LocalSetting\Controller\CategoryController',
			'LocalSetting\Controller\CategoryPrice' 					=> 'LocalSetting\Controller\CategoryPriceController',
			'LocalSetting\Controller\LocalSetting' 						=> 'LocalSetting\Controller\LocalSettingController',
			'LocalSetting\Controller\SuppWiseCategory' 					=> 'LocalSetting\Controller\SuppWiseCategoryController',
			
			
        ),
    ),
    'view_manager' => array(
		'template_map' => array(
			'LocalSetting/layout'           => __DIR__ . '/../view/layout/layout.phtml','paginator-slide' => __DIR__ . '/../view/layout/slidePaginator.phtml',
		),
		'template_path_stack' => array(
			__DIR__ . '/../view',
		),
	),
);
?>