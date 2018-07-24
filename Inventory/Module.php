<?php
	namespace Inventory;
	
	use Zend\Mvc\ModuleRouteListener;
	use Zend\Mvc\MvcEvent;
	
	use Inventory\Model\StockOrder;
	use Inventory\Model\StockOrderTable;
	
	use Inventory\Model\StockEntry;
	use Inventory\Model\StockEntryTable;
	
	use Inventory\Model\SRStockOrder;
	use Inventory\Model\SRStockOrderTable;
	
	use Inventory\Model\SRStockDetails;
	use Inventory\Model\SRStockDetailsTable;
	
	use Inventory\Model\RETStockOrder;
	use Inventory\Model\RETStockOrderTable;
	
	use Inventory\Model\RETStockDetails;
	use Inventory\Model\RETStockDetailsTable;
	
	use Inventory\Model\SRStockReturn;
	use Inventory\Model\SRStockReturnDetailsTable;
	
	use Inventory\Model\ConsumptionEntry;
	use Inventory\Model\ConsumptionEntryTable;
	
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
				if ('Inventory' === $moduleNamespace ) {
					$controller->layout('Inventory/layout');
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
					 'Inventory\Model\StockOrderTable' => function ($sm) {
						$tableGateway = $sm->get('StockOrderTableGateway');
						$table = new StockOrderTable($tableGateway);
						return $table;
					},
					'StockOrderTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new StockOrder());
						return new TableGateway('i_stock_order', $dbAdapter, null, $resultSetPrototyp);
					},
					'Inventory\Model\StockEntryTable' => function ($sm) {
						$tableGateway = $sm->get('StockEntryTableGateway');
						$table = new StockEntryTable($tableGateway);
						return $table;
					},
					'StockEntryTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new StockEntry());
						return new TableGateway('i_stock_details', $dbAdapter, null, $resultSetPrototyp);
					},
					'Inventory\Model\SRStockOrderTable' => function ($sm) {
						$tableGateway = $sm->get('SRStockOrderTableGateway');
						$table = new SRStockOrderTable($tableGateway);
						return $table;
					},
					'SRStockOrderTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new SRStockOrder());
						return new TableGateway('i_sr_stock_dist', $dbAdapter, null, $resultSetPrototyp);
					},
					'Inventory\Model\SRStockDetailsTable' => function ($sm) {
						$tableGateway = $sm->get('SRStockDetailsTableGateway');
						$table = new SRStockDetailsTable($tableGateway);
						return $table;
					},
					'SRStockDetailsTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new SRStockDetails());
						return new TableGateway('i_sr_stock_dist_details', $dbAdapter, null, $resultSetPrototyp);
					},
					'Inventory\Model\RETStockOrderTable' => function ($sm) {
						$tableGateway = $sm->get('RETStockOrderTableGateway');
						$table = new RETStockOrderTable($tableGateway);
						return $table;
					},
					'RETStockOrderTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new RETStockOrder());
						return new TableGateway('i_retailer_stock_dist', $dbAdapter, null, $resultSetPrototyp);
					},
					'Inventory\Model\RETStockDetailsTable' => function ($sm) {
						$tableGateway = $sm->get('RETStockDetailsTableGateway');
						$table = new RETStockDetailsTable($tableGateway);
						return $table;
					},
					'RETStockDetailsTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new RETStockDetails());
						return new TableGateway('i_retailer_stock_dist_details', $dbAdapter, null, $resultSetPrototyp);
					},
					'Inventory\Model\SRStockReturnTable' => function ($sm) {
						$tableGateway = $sm->get('SRStockReturnTableGateway');
						$table = new SRStockReturnTable($tableGateway);
						return $table;
					},
					'SRStockReturnTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new SRStockReturn());
						return new TableGateway('i_sr_stock_return', $dbAdapter, null, $resultSetPrototyp);
					},
					'Inventory\Model\SRStockReturnDetailsTable' => function ($sm) {
						$tableGateway = $sm->get('SRStockReturnDetailsTableGateway');
						$table = new SRStockReturnDetailsTable($tableGateway);
						return $table;
					},
					'SRStockReturnDetailsTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new SRStockReturnDetails());
						return new TableGateway('i_sr_stock_ret_details', $dbAdapter, null, $resultSetPrototyp);
					},
					'Inventory\Model\ConsumptionEntryTable' => function ($sm) {
						$tableGateway = $sm->get('ConsumptionEntryTableGateway');
						$table = new ConsumptionEntryTable($tableGateway);
						return $table;
					},
					'ConsumptionEntryTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new ConsumptionEntry());
						return new TableGateway('i_consumption', $dbAdapter, null, $resultSetPrototyp);
					},
				),
			);
		}
	}
?>	