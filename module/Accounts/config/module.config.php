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
			'accounts' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/accounts[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'Accounts\Controller\Accounts',
						'action' => 'index',
					),
				),
			),
			'companyaccounts' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/companyaccounts[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'Accounts\Controller\CompanyAccounts',
						'action' => 'index',
					),
				),
			),
			'portfolioaccounts' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/portfolioaccounts[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'Accounts\Controller\PortfolioAccounts',
						'action' => 'index',
					),
				),
			),
			'portfoliopaymententry' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/portfoliopaymententry[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'Accounts\Controller\PortfolioPaymentEntry',
						'action' => 'index',
					),
				),
			),
			'portfolioreceiptentry' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/portfolioreceiptentry[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'Accounts\Controller\PortfolioReceiptEntry',
						'action' => 'index',
					),
				),
			),
			'portfoliojournalentry' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/portfoliojournalentry[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'Accounts\Controller\PortfolioJournalEntry',
						'action' => 'index',
					),
				),
			),
			'contraentry' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/contraentry[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'Accounts\Controller\ContraEntry',
						'action' => 'index',
					),
				),
			),
			'paymententry' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/paymententry[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'Accounts\Controller\PaymentEntry',
						'action' => 'index',
					),
				),
			),
			'receiptentry' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/receiptentry[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'Accounts\Controller\ReceiptEntry',
						'action' => 'index',
					),
				),
			),
			'journalentry' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/journalentry[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'Accounts\Controller\JournalEntry',
						'action' => 'index',
					),
				),
			),
			'openingbalanceentry' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/openingbalanceentry[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'Accounts\Controller\OpeningBalanceEntry',
						'action' => 'index',
					),
				),
			),
			'generalaccountentryedit' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/generalaccountentryedit[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'Accounts\Controller\GeneralAccountEntryEdit',
						'action' => 'index',
					),
				),				
			),
			'monthendprocess' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/monthendprocess[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'Accounts\Controller\MonthEndProcess',
						'action' => 'index',
					),
				),
			),
			'portfolioaccountentryedit' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/portfolioaccountentryedit[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'Accounts\Controller\PortfolioAccountEntryEdit',
						'action' => 'index',
					),
				),				
			),
			'portfolioadjustmententry' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/portfolioadjustmententry[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'Accounts\Controller\PortfolioAdjustmentEntry',
						'action' => 'index',
					),
				),
			),
		),
	),
    'controllers' => array(
        'invokables' => array(
            'Accounts\Controller\Accounts' 					=> 'Accounts\Controller\AccountsController',
			'Accounts\Controller\CompanyAccounts' 			=> 'Accounts\Controller\CompanyAccountsController',
			'Accounts\Controller\PortfolioAccounts' 		=> 'Accounts\Controller\PortfolioAccountsController',
			'Accounts\Controller\PortfolioPaymentEntry' 	=> 'Accounts\Controller\PortfolioPaymentEntryController',
			'Accounts\Controller\PortfolioReceiptEntry' 	=> 'Accounts\Controller\PortfolioReceiptEntryController',
			'Accounts\Controller\PortfolioJournalEntry' 	=> 'Accounts\Controller\PortfolioJournalEntryController',
			'Accounts\Controller\ContraEntry' 				=> 'Accounts\Controller\ContraEntryController',
			'Accounts\Controller\PaymentEntry' 				=> 'Accounts\Controller\PaymentEntryController',
			'Accounts\Controller\ReceiptEntry' 				=> 'Accounts\Controller\ReceiptEntryController',
			'Accounts\Controller\JournalEntry' 				=> 'Accounts\Controller\JournalEntryController',
			'Accounts\Controller\OpeningBalanceEntry' 		=> 'Accounts\Controller\OpeningBalanceEntryController',
			'Accounts\Controller\GeneralAccountEntryEdit' 	=> 'Accounts\Controller\GeneralAccountEntryEditController',
			'Accounts\Controller\MonthEndProcess' 			=> 'Accounts\Controller\MonthEndProcessController',
			'Accounts\Controller\PortfolioAccountEntryEdit' 	=> 'Accounts\Controller\PortfolioAccountEntryEditController',
			'Accounts\Controller\PortfolioAdjustmentEntry' 	=> 'Accounts\Controller\PortfolioAdjustmentEntryController',
        ),
    ),
    'view_manager' => array(
		'template_map' => array(
			'Accounts/layout'           => __DIR__ . '/../view/layout/layout.phtml',
		),
		'template_path_stack' => array(
			__DIR__ . '/../view',
		),
	),
);
?>