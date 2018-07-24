<?php
	namespace Accounts;

	use Zend\Mvc\ModuleRouteListener;
	use Zend\Mvc\MvcEvent;
	
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Db\TableGateway\TableGateway;
	
	use Accounts\Model\Master;
	use Accounts\Model\MasterTable;

	use Accounts\Model\Child;
	use Accounts\Model\ChildTable;
	
	use Accounts\Model\Voucher;
	use Accounts\Model\VoucherTable;

	use Accounts\Model\TrialBalance;
	use Accounts\Model\TrialBalanceTable;
	
	use Accounts\Model\OpeningBalanceEntry;
	use Accounts\Model\OpeningBalanceEntryTable;
	
	use Accounts\Model\PaymentTransaction;
	use Accounts\Model\PaymentTransactionTable;
	
	
		
	class Module {
		public function onBootstrap(MvcEvent $e) {
			$eventManager        = $e->getApplication()->getEventManager();
			$moduleRouteListener = new ModuleRouteListener();
			$moduleRouteListener->attach($eventManager);
			
			$e->getApplication()->getEventManager()->getSharedManager()->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function($e) {
				$controller      = $e->getTarget();
				$controllerClass = get_class($controller);
				$moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));
				if ('Accounts' === $moduleNamespace ) {
					$controller->layout('Accounts/layout');
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
					'Accounts\Model\MasterTable' => function ($sm) {
						$tableGateway = $sm->get('MasterTableGateway');
						$table = new MasterTable($tableGateway);
						return $table;
					},
					'MasterTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new Master());
						return new TableGateway('a_transaction_master', $dbAdapter, null, $resultSetPrototyp);
					},
					'Accounts\Model\ChildTable' => function ($sm) {
						$tableGateway = $sm->get('ChildTableGateway');
						$table = new ChildTable($tableGateway);
						return $table;
					},
					'ChildTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new Child());
						return new TableGateway('a_transaction_child', $dbAdapter, null, $resultSetPrototyp);
					},
					'Accounts\Model\VoucherTable' => function ($sm) {
						$tableGateway = $sm->get('VoucherTableGateway');
						$table = new VoucherTable($tableGateway);
						return $table;
					},
					'VoucherTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new Voucher());
						return new TableGateway('a_voucher', $dbAdapter, null, $resultSetPrototyp);
					},
					'Accounts\Model\TrialBalanceTable' => function ($sm) {
						$tableGateway = $sm->get('TrialBalanceTableGateway');
						$table = new TrialBalanceTable($tableGateway);
						return $table;
					},
					'TrialBalanceTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new TrialBalance());
						return new TableGateway('a_trial_bal', $dbAdapter, null, $resultSetPrototyp);
					},
					'Accounts\Model\OpeningBalanceEntryTable' => function ($sm) {
						$tableGateway = $sm->get('OpeningBalanceEntryTableGateway');
						$table = new OpeningBalanceEntryTable($tableGateway);
						return $table;
					},
					'OpeningBalanceEntryTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new OpeningBalanceEntry());
						return new TableGateway('a_trial_bal', $dbAdapter, null, $resultSetPrototyp);
					},					
					'Accounts\Model\PaymentTransactionTable' => function ($sm) {
						$tableGateway = $sm->get('PaymentTransactionTableGateway');
						$table = new PaymentTransactionTable($tableGateway);
						return $table;
					},
					'PaymentTransactionTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new PaymentTransaction());
						return new TableGateway('i_payment_transaction', $dbAdapter, null, $resultSetPrototyp);
					},
				),
			);
		}
	}
?>