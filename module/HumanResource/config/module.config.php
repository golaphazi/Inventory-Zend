<?php
return array(
	'router' => array(
		'routes' => array(
			'humanresource' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/humanresource[/][:action][/:id]',
					'constraints' => array(
						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
					),
					'defaults' => array(
						'controller' => 'HumanResource\Controller\HumanResource',
						'action' => 'index',
					),
				),
			),
			'employeeregistration' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/employeeregistration[/][:action][/:id][/order_by/:order_by][/:order]',
					'constraints' => array(
						'action' => '(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
						'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
					),
					'defaults' => array(
						'controller' => 'HumanResource\Controller\EmployeeRegistration',
						'action' => 'index',
					),
				),
			),
			'employeeaccesscontrol' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/employeeaccesscontrol[/][:action][/:id][/order_by/:order_by][/:order]',
					'constraints' => array(
						'action' => '(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
						'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
					),
					'defaults' => array(
						'controller' => 'HumanResource\Controller\EmployeeAccessControl',
						'action' => 'index',
					),
				),
			),
			'employeejoining' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/employeejoining[/][:action][/:id][/order_by/:order_by][/:order]',
					'constraints' => array(
						'action' => '(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
						'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
					),
					'defaults' => array(
						'controller' => 'HumanResource\Controller\EmployeeJoining',
						'action' => 'index',
					),
				),
			),
			'employeeattendance' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/employeeattendance[/][:action][/:id][/order_by/:order_by][/:order]',
					'constraints' => array(
						'action' => '(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
						'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
					),
					'defaults' => array(
						'controller' => 'HumanResource\Controller\EmployeeAttendance',
						'action' => 'index',
					),
				),
			),
			'employeemakepayroll' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/employeemakepayroll[/][:action][/:id][/order_by/:order_by][/:order]',
					'constraints' => array(
						'action' => '(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
						'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
					),
					'defaults' => array(
						'controller' => 'HumanResource\Controller\EmployeeMakePayroll',
						'action' => 'index',
					),
				),
			),
			'employeepayment' => array(
				'type' => 'segment',
				'options' => array(
					'route' => '/employeepayment[/][:action][/:id][/order_by/:order_by][/:order]',
					'constraints' => array(
						'action' => '(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
						'id' => '[0-9]+',
						'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order' => 'ASC|DESC',
					),
					'defaults' => array(
						'controller' => 'HumanResource\Controller\EmployeePayment',
						'action' => 'index',
					),
				),
			),
			// Last Here
		),
	),
    'controllers' => array(
        'invokables' => array(
            'HumanResource\Controller\HumanResource' 			=> 'HumanResource\Controller\HumanResourceController',
			'HumanResource\Controller\EmployeeRegistration' 	=> 'HumanResource\Controller\EmployeeRegistrationController',
			'HumanResource\Controller\EmployeeAccessControl' 	=> 'HumanResource\Controller\EmployeeAccessControlController',
			'HumanResource\Controller\EmployeeJoining' 			=> 'HumanResource\Controller\EmployeeJoiningController',
			'HumanResource\Controller\EmployeeAttendance' 		=> 'HumanResource\Controller\EmployeeAttendanceController',
			'HumanResource\Controller\EmployeeMakePayroll' 		=> 'HumanResource\Controller\EmployeeMakePayrollController',
			'HumanResource\Controller\EmployeePayment' 			=> 'HumanResource\Controller\EmployeePaymentController',
        ),
    ),
    'view_manager' => array(
		'template_map' => array(
			'HumanResource/layout' => __DIR__ . '/../view/layout/layout.phtml','paginator-slide' => __DIR__ . '/../view/layout/slidePaginator.phtml',
		),
		'template_path_stack' => array(
			__DIR__ . '/../view',
		),
	),
);
?>