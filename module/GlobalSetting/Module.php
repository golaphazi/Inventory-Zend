<?php
	namespace GlobalSetting;

	use Zend\Mvc\ModuleRouteListener;
	use Zend\Mvc\MvcEvent;

	use GlobalSetting\Model\Coa;
	use GlobalSetting\Model\CoaTable;
	
	use GlobalSetting\Model\InitialCoa;
	use GlobalSetting\Model\InitialCoaTable;
		
	use GlobalSetting\Model\Nationality;
	use GlobalSetting\Model\NationalityTable;
	
	use GlobalSetting\Model\Occupation;
	use GlobalSetting\Model\OccupationTable;
	
	use GlobalSetting\Model\Tech;
	use GlobalSetting\Model\TechTable;
	
	use GlobalSetting\Model\Country;
	use GlobalSetting\Model\CountryTable;
	
	use GlobalSetting\Model\City;
	use GlobalSetting\Model\CityTable;
	
	use GlobalSetting\Model\OrganizationType;
	use GlobalSetting\Model\OrganizationTypeTable;
	
	use GlobalSetting\Model\MoneyMarketOrganization;
	use GlobalSetting\Model\MoneyMarketOrganizationTable;
	
	use GlobalSetting\Model\OrganizationBranch;
	use GlobalSetting\Model\OrganizationBranchTable;
	
	use GlobalSetting\Model\AccountType;
	use GlobalSetting\Model\AccountTypeTable;
	
	use GlobalSetting\Model\AccountDetails;
	use GlobalSetting\Model\AccountDetailsTable;
	
	use GlobalSetting\Model\ChequeBookDetails;
	use GlobalSetting\Model\ChequeBookDetailsTable;
	
	use GlobalSetting\Model\ChequeBookDetailsBkdn;
	use GlobalSetting\Model\ChequeBookDetailsBkdnTable;

	use GlobalSetting\Model\Designation;
	use GlobalSetting\Model\DesignationTable;
	
	use GlobalSetting\Model\Relation;
	use GlobalSetting\Model\RelationTable;
	
	use GlobalSetting\Model\Document;
	use GlobalSetting\Model\DocumentTable;
	
	use GlobalSetting\Model\Currency;
	use GlobalSetting\Model\CurrencyTable;
	
	use GlobalSetting\Model\AccBudget;
	use GlobalSetting\Model\AccBudgetTable;
	
	use GlobalSetting\Model\StaffInformation;
	use GlobalSetting\Model\StaffInformationTable;
	
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
				if ('GlobalSetting' === $moduleNamespace ) {
					$controller->layout('GlobalSetting/layout');
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
					'GlobalSetting\Model\CoaTable' => function ($sm) {
						$tableGateway = $sm->get('CoaTableGateway');
						$table = new CoaTable($tableGateway);
						return $table;
					},
					'CoaTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new Coa());
						return new TableGateway('gs_coa', $dbAdapter, null, $resultSetPrototyp);
					},
					'GlobalSetting\Model\InitialCoaTable' => function ($sm) {
						$tableGateway = $sm->get('InitialCoaTableGateway');
						$table = new InitialCoaTable($tableGateway);
						return $table;
					},
					'InitialCoaTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new InitialCoa());
						return new TableGateway('gs_coa', $dbAdapter, null, $resultSetPrototyp);
					},
					'GlobalSetting\Model\NationalityTable' => function ($sm) {
						$tableGateway = $sm->get('NationalityTableGateway');
						$table = new NationalityTable($tableGateway);
						return $table;
					},
					'NationalityTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new Nationality());
						return new TableGateway('gs_nationality', $dbAdapter, null, $resultSetPrototyp);
					},
					'GlobalSetting\Model\OccupationTable' => function ($sm) {
						$tableGateway = $sm->get('OccupationTableGateway');
						$table = new OccupationTable($tableGateway);
						return $table;
					},
					'OccupationTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new Occupation());
						return new TableGateway('gs_occupation', $dbAdapter, null, $resultSetPrototyp);
					},
					'GlobalSetting\Model\TechTable' => function ($sm) {
						$tableGateway = $sm->get('TechTableGateway');
						$table = new TechTable($tableGateway);
						return $table;
					},
					'TechTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new Tech());
						return new TableGateway('test', $dbAdapter, null, $resultSetPrototyp);
					},
					
					'GlobalSetting\Model\CountryTable' => function ($sm) {
						$tableGateway = $sm->get('CountryTableGateway');
						$table = new CountryTable($tableGateway);
						return $table;
					},
					'CountryTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new Country());
						return new TableGateway('gs_country', $dbAdapter, null, $resultSetPrototyp);
					},
					'GlobalSetting\Model\CityTable' => function ($sm) {
						$tableGateway = $sm->get('CityTableGateway');
						$table = new CityTable($tableGateway);
						return $table;
					},
					'CityTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new City());
						return new TableGateway('gs_city', $dbAdapter, null, $resultSetPrototyp);
					},
					'GlobalSetting\Model\OrganizationTypeTable' => function ($sm) {
						$tableGateway = $sm->get('OrganizationTypeGateway');
						$table = new OrganizationTypeTable($tableGateway);
						return $table;
					},
					'OrganizationTypeGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new OrganizationType());
						return new TableGateway('gs_org_type', $dbAdapter, null, $resultSetPrototyp);
					},
					
					'GlobalSetting\Model\MoneyMarketOrganizationTable' => function ($sm) {
						$tableGateway = $sm->get('MoneyMarketOrganizationGateway');
						$table = new MoneyMarketOrganizationTable($tableGateway);
						return $table;
					},
					'MoneyMarketOrganizationGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new MoneyMarketOrganization());
						return new TableGateway('gs_money_mkt_org', $dbAdapter, null, $resultSetPrototyp);
					},
					
					'GlobalSetting\Model\OrganizationBranchTable' => function ($sm) {
						$tableGateway = $sm->get('OrganizationBranchGateway');
						$table = new OrganizationBranchTable($tableGateway);
						return $table;
					},
					'OrganizationBranchGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new OrganizationBranch());
						return new TableGateway('gs_org_branch', $dbAdapter, null, $resultSetPrototyp);
					},
					'GlobalSetting\Model\AccountTypeTable' => function ($sm) {
						$tableGateway = $sm->get('AccountTypeGateway');
						$table = new AccountTypeTable($tableGateway);
						return $table;
					},
					'AccountTypeGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new AccountType());
						return new TableGateway('gs_account_type', $dbAdapter, null, $resultSetPrototyp);
					},
					'GlobalSetting\Model\AccountDetailsTable' => function ($sm) {
						$tableGateway = $sm->get('AccountDetailsGateway');
						$table = new AccountDetailsTable($tableGateway);
						return $table;
					},
					'AccountDetailsGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new AccountDetails());
						return new TableGateway('gs_account_details', $dbAdapter, null, $resultSetPrototyp);
					},
					'GlobalSetting\Model\ChequeBookDetailsTable' => function ($sm) {
						$tableGateway = $sm->get('ChequeBookDetailsGateway');
						$table = new ChequeBookDetailsTable($tableGateway);
						return $table;
					},
					'ChequeBookDetailsGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new ChequeBookDetails());
						return new TableGateway('gs_cheque_book_details', $dbAdapter, null, $resultSetPrototyp);
					},
					'GlobalSetting\Model\ChequeBookDetailsBkdnTable' => function ($sm) {
						$tableGateway = $sm->get('ChequeBookDetailsBkdnGateway');
						$table = new ChequeBookDetailsBkdnTable($tableGateway);
						return $table;
					},
					'ChequeBookDetailsBkdnGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new ChequeBookDetailsBkdn());
						return new TableGateway('gs_cheque_book_details_bkdn', $dbAdapter, null, $resultSetPrototyp);
					},
					'GlobalSetting\Model\DesignationTable' => function ($sm) {
						$tableGateway = $sm->get('DesignationGateway');
						$table = new DesignationTable($tableGateway);
						return $table;
					},
					'DesignationGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new Designation());
						return new TableGateway('gs_designation', $dbAdapter, null, $resultSetPrototyp);
					},
					'GlobalSetting\Model\RelationTable' => function ($sm) {
						$tableGateway = $sm->get('RelationGateway');
						$table = new RelationTable($tableGateway);
						return $table;
					},
					'RelationGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new Relation());
						return new TableGateway('gs_relation', $dbAdapter, null, $resultSetPrototyp);
					},
					'GlobalSetting\Model\CurrencyTable' => function ($sm) {
						$tableGateway = $sm->get('CurrencyTableGateway');
						$table = new CurrencyTable($tableGateway);
						return $table;
					},
					'CurrencyTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new Currency());
						return new TableGateway('gs_list_currency', $dbAdapter, null, $resultSetPrototyp);
					},
					'GlobalSetting\Model\AccBudgetTable' => function ($sm) {
						$tableGateway = $sm->get('AccBudgetTableGateway');
						$table = new AccBudgetTable($tableGateway);
						return $table;
					},
					'AccBudgetTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new AccBudget());
						return new TableGateway('gs_acc_budget', $dbAdapter, null, $resultSetPrototyp);
					},
					'GlobalSetting\Model\StaffInformationTable' => function ($sm) {
						$tableGateway = $sm->get('StaffInformationTableGateway');
						$table = new StaffInformationTable($tableGateway);
						return $table;
					},
					'StaffInformationTableGateway' => function ($sm) {
						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
						$resultSetPrototyp = new ResultSet();
						$resultSetPrototyp->setArrayObjectPrototype(new StaffInformation());
						return new TableGateway('staffinformation', $dbAdapter, null, $resultSetPrototyp);
					},
					//End here
				),
			);
		}
	}
?>	
