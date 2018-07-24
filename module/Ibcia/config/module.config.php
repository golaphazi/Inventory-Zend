<?php
	/**
	 * Zend Framework (http://framework.zend.com/)
	 *
	 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
	 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
	 * @license   http://framework.zend.com/license/new-bsd New BSD License
	 */
	
	//filename : Ibcia/config/module.config.php
	namespace Ibcia;

	return array(
		 
		//controllers services configuration...
		'controllers' => array(
			'factories' => array(
				'Ibcia\Controller\Login' =>
               		'Ibcia\Factory\Controller\LoginControllerServiceFactory',
				'Ibcia\Controller\Holiday' =>
               		'Ibcia\Factory\Controller\HolidayControllerServiceFactory',
				'Ibcia\Controller\BusinessDate' =>
               		'Ibcia\Factory\Controller\BusinessDateControllerServiceFactory'
			),
			'invokables' => array(
				'Ibcia\Controller\Index' =>
               		'Ibcia\Controller\IndexController',
				'Ibcia\Controller\TellAFriend' => 
					'Ibcia\Controller\TellAFriendController',
			),
		),
		 
		//service manager configuration...
		'service_manager' => array(
			'factories' => array(
				'AuthStorage' =>
               		'Ibcia\Factory\Storage\AuthStorageFactory',
            	'AuthService' => 
               		'Ibcia\Factory\Storage\AuthenticationServiceFactory',
				'IdentityManager' => 
					'Ibcia\Factory\Storage\IdentityManagerFactory',
			),
			'aliases' => array(
				'translator' => 'MvcTranslator',
			),
		),
		 
		//routing configuration...    
		'router' => array(
			'routes' => array(
				'login' => array(
					'type'    => 'segment',
					'options' => array(
						'route' => '/login[/][:action][/:id]',
						'constraints' => array(
							'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
							'id' => '[0-9]+',
						),
						'defaults' => array(
							'controller' => 'Ibcia\Controller\Login',
							'action'     => 'index',
						),
					),
				),
				'holiday' => array(
					'type'    => 'segment',
					'options' => array(
						'route' => '/holiday[/][:action][/:id]',
						'constraints' => array(
							'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
							'id' => '[0-9]+',
						),
						'defaults' => array(
							'controller' => 'Ibcia\Controller\Holiday',
							'action'     => 'index',
						),
					),
				),
				'businessdate' => array(
					'type'    => 'segment',
					'options' => array(
						'route' => '/businessdate[/][:action]',
						'constraints' => array(
							'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						),
						'defaults' => array(
							'__NAMESPACE__' => 'Ibcia\Controller',
							'controller' => 'Ibcia\Controller\BusinessDate',
							'action'     => 'index',
						),
					),
					'may_terminate' => true,
					'child_routes' => array(
						'eod' => array(
							'type'    => 'Literal',
							'options' => array(
								'route'    => '/index',
								'defaults' => array(
									'controller' => 'Ibcia\Controller\BusinessDate',
									'action'     => 'index',
								),
							),
						),
					),
				),
				/*'eod' => array(
					'type'    => 'segment',
					'options' => array(
						'route' => '/businessdate[/][:action][/:id]',
						'constraints' => array(
							'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
							'id' => '[0-9]+',
						),
						'defaults' => array(
							'controller' => 'Ibcia\Controller\BusinessDate',
							'action'     => 'index',
						),
					),
				),*/
				'home' => array(
					'type' => 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
						'route'    => '/',
						'defaults' => array(
							'controller' => 'Ibcia\Controller\Index',
							'action'     => 'index',
						),
					),
				),
				'/home' => array(
					'type' => 'segment',
					'options' => array(
						'route'    => '/home[/][:action][/:id]',
						'constraints' => array(
							'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
							'id' => '[0-9]+',
						),
						'defaults' => array(
							'controller' => 'Ibcia\Controller\Index',
							'action'     => 'index',
						),
					),
				),
				'index' => array(
					'type'    => 'segment',
					'options' => array(
						'route' => '/index[/][:action][/:id]',
						'constraints' => array(
							'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
							'id' => '[0-9]+',
						),
						'defaults' => array(
							'__NAMESPACE__' => 'Ibcia\Controller',
							'controller'    => 'Index',
							'action'        => 'index',
						),
					),
					'may_terminate' => true,
					'child_routes' => array(
						'default' => array(
							'type'    => 'Segment',
							'options' => array(
								'route'    => '/index[/][:action][/:id]',
								'constraints' => array(
									'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
									'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
								),
								'defaults' => array(
									'controller' => 'Ibcia\Controller\Index',
									'action'     => 'index',
								),
							),
						),
					),
				),
				'tellafriend' => array(
					'type' => 'segment',
					'options' => array(
						'route' => '/tellafriend[/][:action][/:id]',
						'constraints' => array(
							'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
							'id' => '[0-9]+',
						),
						'defaults' => array(
							'controller' => 'Ibcia\Controller\TellAFriend',
							'action' => 'index',
						),
					),
				),
				'signup' => array(
					'type'    => 'segment',
					'options' => array(
						'route' => '/signup[/][:action][/:id]',
						'constraints' => array(
							'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
							'id' => '[0-9]+',
						),
						'defaults' => array(
							'controller' => 'Ibcia\Controller\Signup',
							'action'     => 'signup',
						),
					),
				),
			),
		),
		
		//default navigation configuration...
		/*'navigation'  => array(
			'default' => array(
				array (
					'label' => 'Home',
					'route' => 'home',
				),
			),
		),*/
		
		//default translator configuration...
		'translator' => array(
			'locale' => 'en_US',
			'translation_file_patterns' => array(
				array(
					'type'     => 'gettext',
					'base_dir' => __DIR__ . '/../language',
					'pattern'  => '%s.mo',
				),
			),
		),
		
		//default view/layout configuration...
		'view_manager' => array(
			'display_not_found_reason' => true,
			'display_exceptions'       => true,
			'doctype'                  => 'HTML5',
			'not_found_template'       => 'error/404',
			'exception_template'       => 'error/index',
			'template_map' => array(
				'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
				'ibcia/home/index'		  => __DIR__ . '/../view/ibcia/home/index.phtml',
				'error/404'               => __DIR__ . '/../view/error/404.phtml',
				'error/index'             => __DIR__ . '/../view/error/index.phtml',
			),
			'template_path_stack' => array(
				__DIR__ . '/../view',
			),
		),
	);
	
	/*return array(
		'router' => array(
			'routes' => array(
				'home' => array(
					'type' => 'Zend\Mvc\Router\Http\Literal',
					'options' => array(
						'route'    => '/',
						'defaults' => array(
							'controller' => 'Ibcia\Controller\Index',
							'action'     => 'index',
						),
					),
				),
				// The following is a route to simplify getting started creating
				// new controllers and actions without needing to create a new
				// module. Simply drop new controllers in, and you can access them
				// using the path /application/:controller/:action
				'ibcia' => array(
					'type'    => 'Literal',
					'options' => array(
						'route'    => '/ibcia',
						'defaults' => array(
							'__NAMESPACE__' => 'Ibcia\Controller',
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
		),
		'controllers' => array(
			'invokables' => array(
				'Ibcia\Controller\Index' => 'Ibcia\Controller\IndexController'
			),
		),
		'view_manager' => array(
			'display_not_found_reason' => true,
			'display_exceptions'       => true,
			'doctype'                  => 'HTML5',
			'not_found_template'       => 'error/404',
			'exception_template'       => 'error/index',
			'template_map' => array(
				'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
				'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
				'error/404'               => __DIR__ . '/../view/error/404.phtml',
				'error/index'             => __DIR__ . '/../view/error/index.phtml',
			),
			'template_path_stack' => array(
				__DIR__ . '/../view',
			),
		),
	);*/
?>	