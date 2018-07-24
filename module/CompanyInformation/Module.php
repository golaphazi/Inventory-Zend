<?php
	namespace CompanyInformation;
	
	use Zend\Mvc\ModuleRouteListener;
	use Zend\Mvc\MvcEvent;
	use CompanyInformation\Model\Company;
	use CompanyInformation\Model\CompanyTable;
	use CompanyInformation\Model\Branch;
	use CompanyInformation\Model\BranchTable;
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
				if ('CompanyInformation' === $moduleNamespace ) {
					$controller->layout('CompanyInformation/layout');
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
					'CompanyInformation\Model\CompanyTable' => function ($sm) {
						$tableGateway = $sm->get('CompanyTableGateway');
						$table = new CompanyTable($tableGateway);
						return $table;
					},
					'CompanyTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new Company());
						return new TableGateway('c_company', $dbAdapter, null, $resultSetPrototyp);
					},
					'CompanyInformation\Model\BranchTable' => function ($sm) {
						$tableGateway = $sm->get('BranchTableGateway');
						$table = new BranchTable($tableGateway);
						return $table;
					},
					'BranchTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new Branch());
						return new TableGateway('c_branch', $dbAdapter, null, $resultSetPrototyp);
					},
				),
			);
		}
	}
?>	
