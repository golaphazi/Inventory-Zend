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
			'globalsetting' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/globalsetting[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'GlobalSetting\Controller\GlobalSetting',
						'action' => 'index',
					),
				),
			),
			'initialcoa' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/initialcoa[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'GlobalSetting\Controller\InitialCoa',
						'action' => 'index',
					),
				),
			),
			'coa' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/coa[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'GlobalSetting\Controller\Coa',
						'action' => 'index',
					),
				),
			),
			'nationality' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/nationality[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'GlobalSetting\Controller\Nationality',
						'action' => 'index',
					),
				),
			),
			'occupation' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/occupation[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'GlobalSetting\Controller\Occupation',
						'action' => 'index',
					),
				),
			),
			'tech' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/tech[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'GlobalSetting\Controller\Tech',
						'action' => 'index',
					),
				),
			),
			'country' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/country[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'GlobalSetting\Controller\Country',
						'action' => 'index',
					),
				),
			),
			'city' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/city[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'GlobalSetting\Controller\City',
						'action' => 'index',
					),
				),
			),
			'organizationtype' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/organizationtype[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'GlobalSetting\Controller\OrganizationType',
						'action' => 'index',
					),
				),
			),
			'moneymarketorganization' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/moneymarketorganization[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'GlobalSetting\Controller\MoneyMarketOrganization',
						'action' => 'index',
					),
				),
			),
			'organizationbranch' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/organizationbranch[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'GlobalSetting\Controller\OrganizationBranch',
						'action' => 'index',
					),
				),
			),
			'accounttype' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/accounttype[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'GlobalSetting\Controller\AccountType',
						'action' => 'index',
					),
				),
			),
			'accountinformation' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/accountinformation[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'GlobalSetting\Controller\AccountInformation',
						'action' => 'index',
					),
				),
			),
			'chequebookdetails' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/chequebookdetails[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'GlobalSetting\Controller\ChequeBookDetails',
						'action' => 'index',
					),
				),
			),
			'designation' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/designation[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'GlobalSetting\Controller\Designation',
						'action' => 'index',
					),
				),
			),
			'relation' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/relation[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'GlobalSetting\Controller\Relation',
						'action' => 'index',
					),
				),
			),
			'lookupinfomation' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/lookupinfomation[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'GlobalSetting\Controller\LookupInfomation',
						'action' => 'index',
					),
				),
			),
			'holiday' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/holiday[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'GlobalSetting\Controller\Holiday',
						'action' => 'index',
					),
				),
			),
			'currency' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/currency[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'GlobalSetting\Controller\Currency',
						'action' => 'index',
					),
				),
			),
			'accbudget' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/accbudget[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'GlobalSetting\Controller\AccBudget',
						'action' => 'index',
					),
				),
			),
			'staffinformation' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/staffinformation[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'GlobalSetting\Controller\StaffInformation',
						'action' => 'index',
					),
				),
			),
			
			//End here
		),
	),
    'controllers' => array(
        'invokables' => array(
            'GlobalSetting\Controller\GlobalSetting' 				=> 'GlobalSetting\Controller\GlobalSettingController',
			'GlobalSetting\Controller\LookupInfomation' 			=> 'GlobalSetting\Controller\LookupInfomationController',
			'GlobalSetting\Controller\InitialCoa'					=> 'GlobalSetting\Controller\InitialCoaController',
			'GlobalSetting\Controller\Coa' 							=> 'GlobalSetting\Controller\CoaController',
			'GlobalSetting\Controller\Nationality' 					=> 'GlobalSetting\Controller\NationalityController',
			'GlobalSetting\Controller\Occupation' 					=> 'GlobalSetting\Controller\OccupationController',
			'GlobalSetting\Controller\Country' 						=> 'GlobalSetting\Controller\CountryController',
			'GlobalSetting\Controller\City' 						=> 'GlobalSetting\Controller\CityController',
			'GlobalSetting\Controller\MarketInformation' 			=> 'GlobalSetting\Controller\MarketInformationController',
			'GlobalSetting\Controller\OrganizationType' 			=> 'GlobalSetting\Controller\OrganizationTypeController',
			'GlobalSetting\Controller\MoneyMarketOrganization' 		=> 'GlobalSetting\Controller\MoneyMarketOrganizationController',
			'GlobalSetting\Controller\OrganizationBranch' 			=> 'GlobalSetting\Controller\OrganizationBranchController',
			'GlobalSetting\Controller\AccountType' 					=> 'GlobalSetting\Controller\AccountTypeController',
			'GlobalSetting\Controller\AccountInformation' 			=> 'GlobalSetting\Controller\AccountInformationController',
			'GlobalSetting\Controller\Designation' 					=> 'GlobalSetting\Controller\DesignationController',
			'GlobalSetting\Controller\Relation' 					=> 'GlobalSetting\Controller\RelationController',
			'GlobalSetting\Controller\ChequeBookDetails' 			=> 'GlobalSetting\Controller\ChequeBookDetailsController',
			'GlobalSetting\Controller\Holiday' 						=> 'GlobalSetting\Controller\HolidayController',
			'GlobalSetting\Controller\Currency' 					=> 'GlobalSetting\Controller\CurrencyController',
			'GlobalSetting\Controller\AccBudget' 					=> 'GlobalSetting\Controller\AccBudgetController',
			'GlobalSetting\Controller\StaffInformation'				=> 'GlobalSetting\Controller\StaffInformationController',
			'GlobalSetting\Controller\Tech' 						=> 'GlobalSetting\Controller\TechController',
        ),
    ),
    'view_manager' => array(
		'template_map' => array(
			'GlobalSetting/layout'           => __DIR__ . '/../view/layout/layout.phtml',
		),
		'template_path_stack' => array(
			__DIR__ . '/../view',
		),
	),
);
