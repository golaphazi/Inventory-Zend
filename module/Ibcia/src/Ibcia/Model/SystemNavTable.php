<?php
	namespace Ibcia\Model;
	 
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\ResultSet\HydratingResultSet;
	use Zend\Db\TableGateway\AbstractTableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\AdapterAwareInterface;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	 
	class SystemNavTable extends AbstractTableGateway
		implements AdapterAwareInterface {
		protected $table = 's_system_nav';
		protected $adapter;
		
		public function setDbAdapter(Adapter $adapter) {
			$this->adapter = $adapter;
			$this->resultSetPrototype = new HydratingResultSet();
	 
			$this->initialize();
		}
	 
		public function fetchAll()  {
			$resultSet = $this->select(function (Select $select){
					$select->where(array('REF_SYSTEM_NAV_ID IS NULL', ))
					->order(array('ORDER_NUMBER ASC'));
			});
	 
			$resultSet = $resultSet->toArray();
	 
			return $resultSet;
		}
		
		public function getModule($userId, $navigationLevel) {
			$select = "SELECT PRIVILEGED_NAV.SYSTEM_NAV_ID   SYSTEM_NAV_ID,
						   PRIVILEGED_NAV.CONTROLLER_NAME CONTROLLER,
						   PRIVILEGED_NAV.CONTROLLER_NAME_UI
					  FROM (SELECT DISTINCT P.SYSTEM_NAV_ID SYSTEM_NAV_ID, P.CONTROLLER_NAME CONTROLLER_NAME, 
                        P.CONTROLLER_NAME_UI CONTROLLER_NAME_UI, P.ORDER_BY ORDER_BY
							  FROM s_system_nav P
							 RIGHT JOIN s_system_nav C
								ON P.LFT <= C.LFT
							   AND P.RGT >= C.RGT
							 RIGHT JOIN s_system_nav_operation SNO
								ON C.SYSTEM_NAV_ID = SNO.SYSTEM_NAV_ID
							 RIGHT JOIN up_is_user_operation UO
								ON SNO.SYSTEM_NAV_OPERATION_ID = UO.SYSTEM_NAV_OPERATION_ID
							   AND UO.USER_ID = {$userId}) PRIVILEGED_NAV,
						   (SELECT C.SYSTEM_NAV_ID, COUNT(C.CONTROLLER_NAME) AS NODE_DEPTH
							  FROM s_system_nav C, s_system_nav P
							 WHERE C.LFT BETWEEN P.LFT AND P.RGT
							 GROUP BY C.SYSTEM_NAV_ID, C.CONTROLLER_NAME, C.LFT
							 ORDER BY C.LFT) ST
					 WHERE PRIVILEGED_NAV.SYSTEM_NAV_ID = ST.SYSTEM_NAV_ID
					   AND ST.NODE_DEPTH = {$navigationLevel}
					 ORDER BY PRIVILEGED_NAV.ORDER_BY";
			$stmt = $this->adapter->createStatement($select);
			$stmt->prepare();
			$result = $stmt->execute();
			
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			
			$resultSet = $resultSet->toArray();
	 
			return $resultSet;
		}
		public function getSubModules($module,$operatorId) {
			$select = "
						SELECT 
								C.SYSTEM_NAV_ID         AS SYSTEM_NAV_ID,
								ST.LEFTMENUE     		AS LEFTMENUE,
								ST.RIGHTMENUE     		AS RIGHTMENUE
						FROM 
								s_system_nav C,
								s_system_nav P,
								(SELECT 
										DISTINCT SYSNAV.SYSTEM_NAV_ID	AS SYSTEM_NAV_ID,
										SYSNAV.LFT						AS LEFTMENUE,
										SYSNAV.RGT						AS RIGHTMENUE
								FROM 
										s_system_nav			SYSNAV,
										s_system_nav_operation 	SYSNAVOPERATION,
										up_is_user_operation   	USEROPERATION
								WHERE
										SYSNAV.SYSTEM_NAV_ID 					= SYSNAVOPERATION.SYSTEM_NAV_ID
								AND 	SYSNAVOPERATION.SYSTEM_NAV_OPERATION_ID = USEROPERATION.SYSTEM_NAV_OPERATION_ID 						
								AND 	USEROPERATION.USER_ID 					= ".$operatorId."
								AND 	USEROPERATION.END_DATE IS NULL
								ORDER BY 
										SYSNAV.SYSTEM_NAV_ID) ST
						WHERE 
								C.LFT BETWEEN P.LFT AND P.RGT
						AND 	C.SYSTEM_NAV_ID 						= ST.SYSTEM_NAV_ID
						AND 	LOWER(P.CONTROLLER_NAME) 				= '".strtolower($module)."'
						GROUP BY 
								C.SYSTEM_NAV_ID,
								ST.LEFTMENUE,
								ST.RIGHTMENUE
						ORDER BY 
								ST.LEFTMENUE,
								C.SYSTEM_NAV_ID
					  ";
			$statement 	= $this->adapter->createStatement($select);
			$statement->prepare();
			$result 	= $statement->execute();
			
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			
			$resultSet			= $resultSet->toArray();
			$DATA_ARRAY			= array();
			
	 		foreach($resultSet as $resultSetData) {
				$SYSTEM_NAV_ID	= $resultSetData['SYSTEM_NAV_ID'];
				$LEFTMENUE		= $resultSetData['LEFTMENUE'];
				$RIGHTMENUE		= $resultSetData['RIGHTMENUE'];
				
				$select = "
							SELECT 
									SYSTEMNAV.SYSTEM_NAV_ID		AS SYSTEM_NAV_ID,
									SYSNAV.CONTROLLER_NAME   	AS CONTROLLER,
									SYSNAV.CONTROLLER_NAME_UI   AS CONTROLLER_UI,
									SYSNAV.NODE_DEPTH     		AS NODE_DEPTH
							FROM
									s_system_nav SYSTEMNAV,
									(SELECT 
											C.SYSTEM_NAV_ID, 
											C.CONTROLLER_NAME,
											C.CONTROLLER_NAME_UI, 
											COUNT(C.CONTROLLER_NAME) AS NODE_DEPTH, 
											C.LFT AS MENULEFT, 
											C.ORDER_BY AS MENU_ORDER
									FROM 
											s_system_nav C, 
											s_system_nav P
									WHERE 
											C.LFT BETWEEN P.LFT AND P.RGT
									GROUP BY 
											C.SYSTEM_NAV_ID, 
											C.CONTROLLER_NAME, 
											C.LFT, 
											C.ORDER_BY,
											C.CONTROLLER_NAME_UI
											ORDER BY 
											MENULEFT, 
											MENU_ORDER
									) 							SYSNAV
							WHERE 
									SYSTEMNAV.LFT 			<= ".$LEFTMENUE."
							AND 	SYSTEMNAV.RGT 			>= ".$RIGHTMENUE."
							AND 	SYSNAV.SYSTEM_NAV_ID 	= SYSTEMNAV.SYSTEM_NAV_ID
							AND 	SYSTEMNAV.CONTROLLER_NAME NOT IN ('System Navigation')
							ORDER BY 
									SYSNAV.NODE_DEPTH,
									SYSTEMNAV.SYSTEM_NAV_ID
						  ";
				$statement	= $this->adapter->createStatement($select);
				$statement->prepare();
				$result 	= $statement->execute();
				
				if ($result instanceof ResultInterface && $result->isQueryResult()) {
					$resultSet = new ResultSet();
					$resultSet->initialize($result);
				}
				
				foreach($resultSet as $resultSetData) {
					$DATA_ARRAY[] = array(
						'SYSTEM_NAV_ID'		=>$resultSetData['SYSTEM_NAV_ID'],
						'CONTROLLER'		=>$resultSetData['CONTROLLER'],
						'CONTROLLER_UI'		=>$resultSetData['CONTROLLER_UI'],
						'NODE_DEPTH'		=>$resultSetData['NODE_DEPTH'],
					);
				}
			}
			$resultSet = array_map("unserialize", array_unique(array_map("serialize", $DATA_ARRAY)));
			//echo "<pre>"; print_r($resultSet); die();
			
			return $resultSet;
		}
		
		public function getSubModulesTest($module,$operatorId) {
			$select = "
						SELECT 
								C.SYSTEM_NAV_ID 		AS SYSTEM_NAV_ID,
								C.CONTROLLER_NAME 		AS CONTROLLER,
								C.CONTROLLER_NAME_UI 	AS CONTROLLER_UI,
								ST.NODE_DEPTH     		AS NODE_DEPTH
						FROM 
								s_system_nav C,
								s_system_nav P,
								(SELECT 
										C.SYSTEM_NAV_ID, 
										C.CONTROLLER_NAME, 
										COUNT(C.CONTROLLER_NAME) AS NODE_DEPTH, 
										C.LFT AS MENULEFT, 
										C.ORDER_BY AS MENU_ORDER
								FROM 
										s_system_nav C, 
										s_system_nav P
								WHERE 
										C.LFT BETWEEN P.LFT AND P.RGT
								GROUP BY 
										C.SYSTEM_NAV_ID, 
										C.CONTROLLER_NAME, 
										C.LFT, 
										C.ORDER_BY
								ORDER BY 
										MENULEFT, 
										MENU_ORDER) ST
										
						WHERE 
								C.LFT BETWEEN P.LFT AND P.RGT
						AND 	C.SYSTEM_NAV_ID 						= ST.SYSTEM_NAV_ID
						AND 	LOWER(P.CONTROLLER_NAME) 				= '".strtolower($module)."'
						GROUP BY 
								C.SYSTEM_NAV_ID, 
								C.CONTROLLER_NAME, 
								C.CONTROLLER_NAME_UI,
								ST.MENULEFT, 
								ST.MENU_ORDER, 
								ST.NODE_DEPTH
						ORDER BY 
								ST.MENULEFT, 
								ST.MENU_ORDER
					  ";				
			$stmt = $this->adapter->createStatement($select);
			$stmt->prepare();
			$result = $stmt->execute();
			
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			
			$resultSet = $resultSet->toArray();
	 
			return $resultSet;
		}
		
		public function getControllers() {
			$select = "SELECT 
							  C.SYSTEM_NAV_ID
						FROM 
							  s_system_nav C
						WHERE 
							  C.RGT = C.LFT + 1
						ORDER BY 
							  C.LFT";
			$stmt = $this->adapter->createStatement($select);
			$stmt->prepare();
			$result = $stmt->execute();
			
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			
			//$resultSet = $resultSet->toArray();
			$controllers = array();
			foreach($resultSet as $controller) {
				$controllers[] = $controller->SYSTEM_NAV_ID;
			}
	 
			return $controllers;
		}
		
		
	}
?>	