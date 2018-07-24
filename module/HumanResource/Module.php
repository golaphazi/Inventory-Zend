<?php
	namespace HumanResource;

	use Zend\Mvc\ModuleRouteListener;
	use Zend\Mvc\MvcEvent;
	
	use HumanResource\Model\EmployeePersonalInfo;
	use HumanResource\Model\EmployeePersonalInfoTable;

	use HumanResource\Model\EmployeeSpouseInfo;
	use HumanResource\Model\EmployeeSpouseInfoTable;

	use HumanResource\Model\EmployeeContactInfo;
	use HumanResource\Model\EmployeeContactInfoTable;

	use HumanResource\Model\EmployeeEducationInfo;
	use HumanResource\Model\EmployeeEducationInfoTable;

	use HumanResource\Model\EmployeePostingInfo;
	use HumanResource\Model\EmployeePostingInfoTable;

	use HumanResource\Model\EmployeeSalaryInfo;
	use HumanResource\Model\EmployeeSalaryInfoTable;

	use HumanResource\Model\EmployeeAttendanceInfo;
	use HumanResource\Model\EmployeeAttendanceInfoTable;
	
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Db\TableGateway\TableGateway;
	
	class Module {
		public function onBootstrap(MvcEvent $e) {
			$eventManager        = $e->getApplication()->getEventManager();
			$moduleRouteListener = new ModuleRouteListener();
			$moduleRouteListener->attach($eventManager);
			
			$e->getApplication()->getEventManager()->getSharedManager()->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function($e) {
				$controller      = $e->getTarget();
				$controllerClass = get_class($controller);
				$moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));
				if ('HumanResource' === $moduleNamespace ) {
					$controller->layout('HumanResource/layout');
				}
			}, 100);
		}
	
		public function getConfig() {
			return include __DIR__ . '/config/module.config.php';
		}
	
		public function getAutoloaderConfig() {
			return array(
				'Zend\Loader\ClassMapAutoloader' => array(
					__DIR__ . '/autoload_classmap.php',
				),
				'Zend\Loader\StandardAutoloader' => array(
					'namespaces' => array(
						__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
					),
				),
			);
		}
		
		public function getServiceConfig() {
			return array(
				'factories' => array(
					'HumanResource\Model\EmployeePersonalInfoTable' => function ($sm) {
						$tableGateway	= $sm->get('EmployeePersonalInfoTableGateway');
						$table 			= new EmployeePersonalInfoTable($tableGateway);
						return $table;
					},
					'EmployeePersonalInfoTableGateway' => function ($sm) {
						$dbAdapter 			= $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp 	= new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new EmployeePersonalInfo());
						return new TableGateway('hrms_employee_personal_info', $dbAdapter, null, $resultSetPrototyp);
					},
					
					'HumanResource\Model\EmployeeSpouseInfoTable' => function ($sm) {
						$tableGateway	= $sm->get('EmployeeSpouseInfoTableGateway');
						$table 			= new EmployeeSpouseInfoTable($tableGateway);
						return $table;
					},
					'EmployeeSpouseInfoTableGateway' => function ($sm) {
						$dbAdapter 			= $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp 	= new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new EmployeeSpouseInfo());
						return new TableGateway('hrms_employee_spouse_info', $dbAdapter, null, $resultSetPrototyp);
					},
					
					'HumanResource\Model\EmployeeContactInfoTable' => function ($sm) {
						$tableGateway	= $sm->get('EmployeeContactInfoTableGateway');
						$table 			= new EmployeeContactInfoTable($tableGateway);
						return $table;
					},
					'EmployeeContactInfoTableGateway' => function ($sm) {
						$dbAdapter 			= $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp 	= new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new EmployeeContactInfo());
						return new TableGateway('hrms_employee_contact_info', $dbAdapter, null, $resultSetPrototyp);
					},
					
					'HumanResource\Model\EmployeeEducationInfoTable' => function ($sm) {
						$tableGateway	= $sm->get('EmployeeEducationInfoTableGateway');
						$table 			= new EmployeeEducationInfoTable($tableGateway);
						return $table;
					},
					'EmployeeEducationInfoTableGateway' => function ($sm) {
						$dbAdapter 			= $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp 	= new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new EmployeeEducationInfo());
						return new TableGateway('hrms_employee_education_info', $dbAdapter, null, $resultSetPrototyp);
					},
					
					'HumanResource\Model\EmployeePostingInfoTable' => function ($sm) {
						$tableGateway	= $sm->get('EmployeePostingInfoTableGateway');
						$table 			= new EmployeePostingInfoTable($tableGateway);
						return $table;
					},
					'EmployeePostingInfoTableGateway' => function ($sm) {
						$dbAdapter 			= $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp 	= new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new EmployeePostingInfo());
						return new TableGateway('hrms_employee_posting_info', $dbAdapter, null, $resultSetPrototyp);
					},
					
					'HumanResource\Model\EmployeeSalaryInfoTable' => function ($sm) {
						$tableGateway	= $sm->get('EmployeeSalaryInfoTableGateway');
						$table 			= new EmployeeSalaryInfoTable($tableGateway);
						return $table;
					},
					'EmployeeSalaryInfoTableGateway' => function ($sm) {
						$dbAdapter 			= $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp 	= new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new EmployeeSalaryInfo());
						return new TableGateway('hrms_employee_salary_info', $dbAdapter, null, $resultSetPrototyp);
					},
					
					'HumanResource\Model\EmployeeAttendanceInfoTable' => function ($sm) {
						$tableGateway	= $sm->get('EmployeeAttendanceInfoTableGateway');
						$table 			= new EmployeeAttendanceInfoTable($tableGateway);
						return $table;
					},
					'EmployeeAttendanceInfoTableGateway' => function ($sm) {
						$dbAdapter 			= $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp 	= new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new EmployeeAttendanceInfo());
						return new TableGateway('hrms_employee_attendance_info', $dbAdapter, null, $resultSetPrototyp);
					},
					//End here.
				),
			);
		}
	}
?>