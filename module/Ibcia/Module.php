<?php
	
	/**
	 * Zend Framework (http://framework.zend.com/)
	 *
	 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
	 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
	 * @license   http://framework.zend.com/license/new-bsd New BSD License
	 */
	
	namespace Ibcia;
	
	use Zend\Mvc\ModuleRouteListener;
	use Zend\Mvc\MvcEvent;
	use Ibcia\Model\BusinessDate;
	use Ibcia\Model\BusinessDateTable;
	use Ibcia\Model\Holiday;
	use Ibcia\Model\HolidayTable;
	use Ibcia\Model\MarketPriceHistory;
	use Ibcia\Model\MarketPriceHistoryTable;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Db\TableGateway\TableGateway;
	
	class Module
	{
		public function onBootstrap(MvcEvent $e)
		{
			$em = $e->getApplication()->getEventManager();
			$moduleRouteListener = new ModuleRouteListener();
			$moduleRouteListener->attach($em);
        	$em->attach('route', array($this, 'checkAuthenticated'));
		}
		
		public function isOpenRequest(MvcEvent $e, $controller)
		{
			if ($e->getRouteMatch()->getParam('controller') == $controller) {
				return true;
			}
	
			return false;
		}
		
		public function checkAuthenticated(MvcEvent $e)
		{
			if(($e->getRouteMatch()->getParam('controller') == 'Ibcia\Controller\Login') && (strtolower($e->getRouteMatch()->getParam('action')) == 'logout')) {
				$e->getRouteMatch()
				->setParam('controller', 'Ibcia\Controller\Login')
				->setParam('action', 'logout');
			} else {
				$sm = $e->getApplication()->getServiceManager();

				if (!$this->isOpenRequest($e, 'Ibcia\Controller\LoginController')) {
					//echo "Sumon1";die();
					if(!$sm->get('IdentityManager')->hasIdentity()) {
						$e->getRouteMatch()
						->setParam('controller', 'Ibcia\Controller\Login')
						->setParam('action', 'index');
					}
				}
				/*if($sm->get('IdentityManager')->hasIdentity()) {
print_r($sm->get('IdentityManager')->hasIdentity());
echo $e->getRouteMatch()->getParam('action');
echo $e->getRouteMatch()->getParam('controller');die();
}*/
				if (!$this->isOpenRequest($e, 'Ibcia\Controller\BusinessDateController')) {
					//echo "Sumon1";die();	
					/*echo $sm->get('BusinessDateTable')->currentBusinessDate;
					echo $sm->get('BusinessDateTable')->isSODBkupFinished;
					echo $sm->get('BusinessDateTable')->isSODFinished;
					die();*/
					if($sm->get('IdentityManager')->hasIdentity() &&
						(null == $sm->get('BusinessDateTable')->currentBusinessDate ||
						null == $sm->get('BusinessDateTable')->isSODBkupFinished ||
						null == $sm->get('BusinessDateTable')->isSODFinished)) {
							$e->getRouteMatch()
							->setParam('controller', 'Ibcia\Controller\BusinessDate')
							->setParam('action', 'index');
					}
				}

			}
			//$config = $sm->get('config');
			//echo '<pre>';
			//print_r($sm->get('config'));die();
		}
	
		public function getServiceConfig()
		{
			return array(
				'initializers' => array(
					function ($instance, $sm) {
						if ($instance instanceof \Zend\Db\Adapter\AdapterAwareInterface) {
							$instance->setDbAdapter($sm->get('Zend\Db\Adapter\Adapter'));
						}
					}
				),
				'invokables' => array(
					 'SystemNavTable' => 'Ibcia\Model\SystemNavTable',
				),
				'factories' => array(
					'NavService' => 'Ibcia\Factory\Navigation\NavigationServiceFactory',
					'BusinessDateTable' => function ($sm) {
						$tableGateway = $sm->get('BusinessDateTableGateway');
						$table = new BusinessDateTable($tableGateway);
						return $table;
					},
					'BusinessDateTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new BusinessDate());
						return new TableGateway('l_business_date', $dbAdapter, null, $resultSetPrototyp);
					},
					'HolidayTable' => function ($sm) {
						$tableGateway = $sm->get('HolidayTableGateway');
						$table = new HolidayTable($tableGateway);
						return $table;
					},
					'HolidayTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new Holiday());
						return new TableGateway('l_ls_holiday', $dbAdapter, null, $resultSetPrototyp);
					},
					'MarketPriceHistoryTable' => function ($sm) {
						$tableGateway = $sm->get('MarketPriceHistoryTableGateway');
						$table = new MarketPriceHistoryTable($tableGateway);
						return $table;
					},
					'MarketPriceHistoryTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new MarketPriceHistory());
						return new TableGateway('P_CAP_MKT_PRICE_HISTORY', $dbAdapter, null, $resultSetPrototyp);
					},
				),
			);
		}
		
		public function getConfig()
		{
			return include __DIR__ . '/config/module.config.php';
		}
	
		public function getAutoloaderConfig()
		{
			return array(
				'Zend\Loader\StandardAutoloader' => array(
					'namespaces' => array(
						__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
					),
				),
			);
		}
	}
?>	