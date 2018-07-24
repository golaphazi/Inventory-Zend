<?php
	namespace LocalSetting;

	use Zend\Mvc\ModuleRouteListener;
	use Zend\Mvc\MvcEvent;
		
	use LocalSetting\Model\SupplierInformation;
	use LocalSetting\Model\SupplierInformationTable;
	
	use LocalSetting\Model\RetailerInformation;
	use LocalSetting\Model\RetailerInformationTable;
	
	use LocalSetting\Model\ZoneInformation;
	use LocalSetting\Model\ZoneInformationTable;
	
	use LocalSetting\Model\SrZoneMap;
	use LocalSetting\Model\SrZoneMapTable;
	
	use LocalSetting\Model\SrRetailerMap;
	use LocalSetting\Model\SrRetailerMapTable;
	
	use LocalSetting\Model\SrTarget;
	use LocalSetting\Model\SrTargetTable;
	
	use LocalSetting\Model\SrTargetBkdn;
	use LocalSetting\Model\SrTargetBkdnTable;

	use LocalSetting\Model\Category;
	use LocalSetting\Model\CategoryTable;
	
	use LocalSetting\Model\CategoryPrice;
	use LocalSetting\Model\CategoryPriceTable;

	use Zend\Db\ResultSet\ResultSet;
	use Zend\Db\TableGateway\TableGateway;
	
	use LocalSetting\Model\TargetExposure;
	use LocalSetting\Model\TargetExposureTable;
	
	use LocalSetting\Model\SuppWiseCategory;
	use LocalSetting\Model\SuppWiseCategoryTable;
	
	
	
	class Module {
		public function onBootstrap(MvcEvent $e) {
			$eventManager        = $e->getApplication()->getEventManager();
			$moduleRouteListener = new ModuleRouteListener();
			$moduleRouteListener->attach($eventManager);
			
			$e->getApplication()->getEventManager()->getSharedManager()->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function($e) {
				$controller      = $e->getTarget();
				$controllerClass = get_class($controller);
				$moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));
				if ('LocalSetting' === $moduleNamespace ) {
					$controller->layout('LocalSetting/layout');
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
					'LocalSetting\Model\SupplierInformationTable' => function ($sm) {
						$tableGateway = $sm->get('SupplierInformationGateway');
						$table = new SupplierInformationTable($tableGateway);
						return $table;
					},
					'SupplierInformationGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new SupplierInformation());
						return new TableGateway('ls_supplier_info', $dbAdapter, null, $resultSetPrototyp);
					},
					'LocalSetting\Model\RetailerInformationTable' => function ($sm) {
						$tableGateway = $sm->get('RetailerInformationGateway');
						$table = new RetailerInformationTable($tableGateway);
						return $table;
					},
					'RetailerInformationGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new RetailerInformation());
						return new TableGateway('ls_retailer_info', $dbAdapter, null, $resultSetPrototyp);
					},
					'LocalSetting\Model\ZoneInformationTable' => function ($sm) {
						$tableGateway = $sm->get('ZoneInformationGateway');
						$table = new ZoneInformationTable($tableGateway);
						return $table;
					},
					'ZoneInformationGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new ZoneInformation());
						return new TableGateway('ls_zone_info', $dbAdapter, null, $resultSetPrototyp);
					},
					'LocalSetting\Model\SrZoneMapTable' => function ($sm) {
						$tableGateway = $sm->get('SrZoneMapGateway');
						$table = new SrZoneMapTable($tableGateway);
						return $table;
					},
					'SrZoneMapGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new SrZoneMap());
						return new TableGateway('ls_sr_zone_map', $dbAdapter, null, $resultSetPrototyp);
					},
					'LocalSetting\Model\SrRetailerMapTable' => function ($sm) {
						$tableGateway = $sm->get('SrRetailerMapGateway');
						$table = new SrRetailerMapTable($tableGateway);
						return $table;
					},
					'SrRetailerMapGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new SrRetailerMap());
						return new TableGateway('ls_sr_retailer_map', $dbAdapter, null, $resultSetPrototyp);
					},
					'LocalSetting\Model\SrTargetTable' => function ($sm) {
						$tableGateway = $sm->get('SrTargetGateway');
						$table = new SrTargetTable($tableGateway);
						return $table;
					},
					'SrTargetGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new SrTarget());
						return new TableGateway('ls_sr_target', $dbAdapter, null, $resultSetPrototyp);
					},
					'LocalSetting\Model\SrTargetBkdnTable' => function ($sm) {
						$tableGateway = $sm->get('SrTargetBkdnGateway');
						$table = new SrTargetBkdnTable($tableGateway);
						return $table;
					},
					'SrTargetBkdnGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new SrTargetBkdn());
						return new TableGateway('ls_sr_target_bkdn', $dbAdapter, null, $resultSetPrototyp);
					},
					'LocalSetting\Model\CategoryTable' => function ($sm) {
						$tableGateway = $sm->get('CategoryTableGateway');
						$table = new CategoryTable($tableGateway);
						return $table;
					},
					'CategoryTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new Category());
						return new TableGateway('ls_category', $dbAdapter, null, $resultSetPrototyp);
					},
					'LocalSetting\Model\CategoryPriceTable' => function ($sm) {
						$tableGateway = $sm->get('CategoryPriceTableGateway');
						$table = new CategoryPriceTable($tableGateway);
						return $table;
					},
					'CategoryPriceTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new CategoryPrice());
						return new TableGateway('ls_cat_price', $dbAdapter, null, $resultSetPrototyp);
					},
					'LocalSetting\Model\SuppWiseCategoryTable' => function ($sm) {
						$tableGateway = $sm->get('SuppWiseCategoryTableGateway');
						$table = new SuppWiseCategoryTable($tableGateway);
						return $table;
					},
					'SuppWiseCategoryTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new SuppWiseCategory());
						return new TableGateway('ls_supp_wise_category', $dbAdapter, null, $resultSetPrototyp);
					},
					
					//End
				),


			);
		}
	}
?>