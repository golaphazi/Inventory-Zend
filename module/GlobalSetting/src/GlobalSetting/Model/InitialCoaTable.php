<?php
	namespace GlobalSetting\Model;
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	
		
	use Zend\Session\Container as SessionContainer;
	
	class InitialCoaTable {
		protected $tableGateway;
		protected $portfolioCoaTable;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		
		public function fetchAll() {
			$resultSet = $this->tableGateway->select();
			return $resultSet;
		}
		
		public function initialCoaExist($existCheckData) {
			$rowSet = $this->tableGateway->select($existCheckData);
			$row = $rowSet->current();
			return $row;
		}
		
		public function getInitialCoa() {
			$this->session 	= new SessionContainer('post_supply');
			$businessdate 	= $this->session->businessdate;
			$recdate 		= $this->session->recdate;
			$userid 		= $this->session->userid;
			//Main 5 head Generate Start
			$COMPANY_ID 		= '1';
			$COA_NAME     		= 'Chart of Accounts';
			$COA_CODE 			= '';
			$AUTO_COA 			= 'n';
			$MOTHER_ACCOUNT 	= '';
			$CASH_FLOW_HEAD 	= 'n';
			$LFT        		= '1';
			$RGT     			= '36';
			$data = array(
				'COMPANY_ID' 		=> $COMPANY_ID,
				'COA_NAME' 			=> $COA_NAME,
				'COA_CODE' 			=> $COA_CODE,
				'AUTO_COA' 			=> $AUTO_COA,
				'MOTHER_ACCOUNT' 	=> $MOTHER_ACCOUNT,
				'CASH_FLOW_HEAD' 	=> $CASH_FLOW_HEAD,
				'LFT' 				=> $LFT,
				'RGT' 				=> $RGT,
				'BUSINESS_DATE' 	=> date ('Y-m-d', strtotime($businessdate)),
				'RECORD_DATE' 		=> date ('Y-m-d H:i:s', strtotime($recdate)),
				'OPERATE_BY' 		=> $userid,
			);
			$existCheckData = array(
				'COA_CODE' => $COA_CODE,
			);
			if(!($this->initialCoaExist($existCheckData))) {
				$this->tableGateway->insert($data);
			}
			//Main 5 head Generate End
			
			
			
			
			
				//Capital Start
				$COMPANY_ID 		= '1';
				$COA_NAME     		= 'CAPITAL';
				$COA_CODE 			= '100000000';
				$AUTO_COA 			= 'n';
				$MOTHER_ACCOUNT 	= 'cr';
				$CASH_FLOW_HEAD 	= 'n';
				$LFT        		= '2';
				$RGT     			= '5';
				$data = array(
					'COMPANY_ID' 		=> $COMPANY_ID,
					'COA_NAME' 			=> $COA_NAME,
					'COA_CODE' 			=> $COA_CODE,
					'AUTO_COA' 			=> $AUTO_COA,
					'MOTHER_ACCOUNT' 	=> $MOTHER_ACCOUNT,
					'CASH_FLOW_HEAD' 	=> $CASH_FLOW_HEAD,
					'LFT' 				=> $LFT,
					'RGT' 				=> $RGT,
					'BUSINESS_DATE' 	=> date ('Y-m-d', strtotime($businessdate)),
					'RECORD_DATE' 		=> date ('Y-m-d H:i:s', strtotime($recdate)),
					'OPERATE_BY' 		=> $userid,
				);
				$existCheckData = array(
					'COA_CODE' => $COA_CODE,
				);
				if(!($this->initialCoaExist($existCheckData))) {
					$this->tableGateway->insert($data);
				}
				//Capital Section.
				$COMPANY_ID 		= '1';
				$COA_NAME     		= 'Equity Attributable to Owners';
				$COA_CODE 			= '101000000';
				$AUTO_COA 			= 'n';
				$MOTHER_ACCOUNT 	= 'cr';
				$CASH_FLOW_HEAD 	= 'n';
				$LFT        		= '3';
				$RGT     			= '4';
				$data = array(
					'COMPANY_ID' 		=> $COMPANY_ID,
					'COA_NAME' 			=> $COA_NAME,
					'COA_CODE' 			=> $COA_CODE,
					'AUTO_COA' 			=> $AUTO_COA,
					'MOTHER_ACCOUNT' 	=> $MOTHER_ACCOUNT,
					'CASH_FLOW_HEAD' 	=> $CASH_FLOW_HEAD,
					'LFT' 				=> $LFT,
					'RGT' 				=> $RGT,
					'BUSINESS_DATE' 	=> date ('Y-m-d', strtotime($businessdate)),
					'RECORD_DATE' 		=> date ('Y-m-d H:i:s', strtotime($recdate)),
					'OPERATE_BY' 		=> $userid,
				);
				$existCheckData = array(
					'COA_CODE' => $COA_CODE,
				);
				if(!($this->initialCoaExist($existCheckData))) {
					$this->tableGateway->insert($data);
				}
			//echo "<pre>"; print_r($data); die();
			//////////////////////////////////////////////////////////////////////
				//Liabilities Start
				$COMPANY_ID 		= '1';
				$COA_NAME     		= 'LIABILITIES';
				$COA_CODE 			= '200000000';
				$AUTO_COA 			= 'n';
				$MOTHER_ACCOUNT 	= 'cr';
				$CASH_FLOW_HEAD 	= 'n';
				$LFT        		= '6';
				$RGT     			= '9';
				$data = array(
					'COMPANY_ID' 		=> $COMPANY_ID,
					'COA_NAME' 			=> $COA_NAME,
					'COA_CODE' 			=> $COA_CODE,
					'AUTO_COA' 			=> $AUTO_COA,
					'MOTHER_ACCOUNT' 	=> $MOTHER_ACCOUNT,
					'CASH_FLOW_HEAD' 	=> $CASH_FLOW_HEAD,
					'LFT' 				=> $LFT,
					'RGT' 				=> $RGT,
					'BUSINESS_DATE' 	=> date ('Y-m-d', strtotime($businessdate)),
					'RECORD_DATE' 		=> date ('Y-m-d H:i:s', strtotime($recdate)),
					'OPERATE_BY' 		=> $userid,
				);
				$existCheckData = array(
					'COA_CODE' => $COA_CODE,
				);
				if(!($this->initialCoaExist($existCheckData))) {
					$this->tableGateway->insert($data);
				}
				//Liabilities Section.
				$COMPANY_ID 		= '1';
				$COA_NAME     		= 'Current liabilities';
				$COA_CODE 			= '201000000';
				$AUTO_COA 			= 'n';
				$MOTHER_ACCOUNT 	= 'cr';
				$CASH_FLOW_HEAD 	= 'n';
				$LFT        		= '7';
				$RGT     			= '8';
				$data = array(
					'COMPANY_ID' 		=> $COMPANY_ID,
					'COA_NAME' 			=> $COA_NAME,
					'COA_CODE' 			=> $COA_CODE,
					'AUTO_COA' 			=> $AUTO_COA,
					'MOTHER_ACCOUNT' 	=> $MOTHER_ACCOUNT,
					'CASH_FLOW_HEAD' 	=> $CASH_FLOW_HEAD,
					'LFT' 				=> $LFT,
					'RGT' 				=> $RGT,
					'BUSINESS_DATE' 	=> date ('Y-m-d', strtotime($businessdate)),
					'RECORD_DATE' 		=> date ('Y-m-d H:i:s', strtotime($recdate)),
					'OPERATE_BY' 		=> $userid,
				);
				$existCheckData = array(
					'COA_CODE' => $COA_CODE,
				);
				if(!($this->initialCoaExist($existCheckData))) {
					$this->tableGateway->insert($data);
				}	
				///////////////////////////////////////////////////////////////////////////////////////////
				//Asset Start
					$COMPANY_ID 		= '1';
					$COA_NAME     		= 'ASSET';
					$COA_CODE 			= '300000000';
					$AUTO_COA 			= 'n';
					$MOTHER_ACCOUNT 	= 'dr';
					$CASH_FLOW_HEAD 	= 'n';
					$LFT        		= '10';
					$RGT     			= '25';
					$data = array(
						'COMPANY_ID' 		=> $COMPANY_ID,
						'COA_NAME' 			=> $COA_NAME,
						'COA_CODE' 			=> $COA_CODE,
						'AUTO_COA' 			=> $AUTO_COA,
						'MOTHER_ACCOUNT' 	=> $MOTHER_ACCOUNT,
						'CASH_FLOW_HEAD' 	=> $CASH_FLOW_HEAD,
						'LFT' 				=> $LFT,
						'RGT' 				=> $RGT,
						'BUSINESS_DATE' 	=> date ('Y-m-d', strtotime($businessdate)),
						'RECORD_DATE' 		=> date ('Y-m-d H:i:s', strtotime($recdate)),
						'OPERATE_BY' 		=> $userid,
					);
					$existCheckData = array(
						'COA_CODE' => $COA_CODE,
					);
					if(!($this->initialCoaExist($existCheckData))) {
						$this->tableGateway->insert($data);
					}		
				//Asset Section.
					//Asset Block - 1
					$COMPANY_ID 		= '1';
					$COA_NAME     		= 'Non Current Assets';
					$COA_CODE 			= '301000000';
					$AUTO_COA 			= 'n';
					$MOTHER_ACCOUNT 	= 'dr';
					$CASH_FLOW_HEAD 	= 'n';
					$LFT        		= '11';
					$RGT     			= '12';
					$data = array(
						'COMPANY_ID' 		=> $COMPANY_ID,
						'COA_NAME' 			=> $COA_NAME,
						'COA_CODE' 			=> $COA_CODE,
						'AUTO_COA' 			=> $AUTO_COA,
						'MOTHER_ACCOUNT' 	=> $MOTHER_ACCOUNT,
						'CASH_FLOW_HEAD' 	=> $CASH_FLOW_HEAD,
						'LFT' 				=> $LFT,
						'RGT' 				=> $RGT,
						'BUSINESS_DATE' 	=> date ('Y-m-d', strtotime($businessdate)),
						'RECORD_DATE' 		=> date ('Y-m-d H:i:s', strtotime($recdate)),
						'OPERATE_BY' 		=> $userid,
					);				
					$existCheckData = array(
						'COA_CODE' => $COA_CODE,
					);
					if(!($this->initialCoaExist($existCheckData))) {
						$this->tableGateway->insert($data);
					}
					//Asset Block - 2
					$COMPANY_ID 		= '1';
					$COA_NAME     		= 'Current Asset';
					$COA_CODE 			= '302000000';
					$AUTO_COA 			= 'n';
					$MOTHER_ACCOUNT 	= 'dr';
					$CASH_FLOW_HEAD 	= 'n';
					$LFT        		= '13';
					$RGT     			= '14';
					$data = array(
						'COMPANY_ID' 		=> $COMPANY_ID,
						'COA_NAME' 			=> $COA_NAME,
						'COA_CODE' 			=> $COA_CODE,
						'AUTO_COA' 			=> $AUTO_COA,
						'MOTHER_ACCOUNT' 	=> $MOTHER_ACCOUNT,
						'CASH_FLOW_HEAD' 	=> $CASH_FLOW_HEAD,
						'LFT' 				=> $LFT,
						'RGT' 				=> $RGT,
						'BUSINESS_DATE' 	=> date ('Y-m-d', strtotime($businessdate)),
						'RECORD_DATE' 		=> date ('Y-m-d H:i:s', strtotime($recdate)),
						'OPERATE_BY' 		=> $userid,
					);				
					$existCheckData = array(
						'COA_CODE' => $COA_CODE,
					);
					if(!($this->initialCoaExist($existCheckData))) {
						$this->tableGateway->insert($data);
					}
					//Asset Block - 3
					$COMPANY_ID 		= '1';
					$COA_NAME     		= 'Cash in Hand';
					$COA_CODE 			= '303000000';
					$AUTO_COA 			= 'n';
					$MOTHER_ACCOUNT 	= 'dr';
					$CASH_FLOW_HEAD 	= 'n';
					$LFT        		= '15';
					$RGT     			= '16';
					$data = array(
						'COMPANY_ID' 		=> $COMPANY_ID,
						'COA_NAME' 			=> $COA_NAME,
						'COA_CODE' 			=> $COA_CODE,
						'AUTO_COA' 			=> $AUTO_COA,
						'MOTHER_ACCOUNT' 	=> $MOTHER_ACCOUNT,
						'CASH_FLOW_HEAD' 	=> $CASH_FLOW_HEAD,
						'LFT' 				=> $LFT,
						'RGT' 				=> $RGT,
						'BUSINESS_DATE' 	=> date ('Y-m-d', strtotime($businessdate)),
						'RECORD_DATE' 		=> date ('Y-m-d H:i:s', strtotime($recdate)),
						'OPERATE_BY' 		=> $userid,
					);				
					$existCheckData = array(
						'COA_CODE' => $COA_CODE,
					);
					if(!($this->initialCoaExist($existCheckData))) {
						$this->tableGateway->insert($data);
					}
					//Asset Block - 4
					$COMPANY_ID 		= '1';
					$COA_NAME     		= 'Cash at Bank';
					$COA_CODE 			= '304000000';
					$AUTO_COA 			= 'n';
					$MOTHER_ACCOUNT 	= 'dr';
					$CASH_FLOW_HEAD 	= 'n';
					$LFT        		= '17';
					$RGT     			= '18';
					$data = array(
						'COMPANY_ID' 		=> $COMPANY_ID,
						'COA_NAME' 			=> $COA_NAME,
						'COA_CODE' 			=> $COA_CODE,
						'AUTO_COA' 			=> $AUTO_COA,
						'MOTHER_ACCOUNT' 	=> $MOTHER_ACCOUNT,
						'CASH_FLOW_HEAD' 	=> $CASH_FLOW_HEAD,
						'LFT' 				=> $LFT,
						'RGT' 				=> $RGT,
						'BUSINESS_DATE' 	=> date ('Y-m-d', strtotime($businessdate)),
						'RECORD_DATE' 		=> date ('Y-m-d H:i:s', strtotime($recdate)),
						'OPERATE_BY' 		=> $userid,
					);				
					$existCheckData = array(
						'COA_CODE' => $COA_CODE,
					);
					if(!($this->initialCoaExist($existCheckData))) {
						$this->tableGateway->insert($data);
					}
					//Asset Block - 5
					$COMPANY_ID 		= '1';
					$COA_NAME     		= 'Investment In Products';
					$COA_CODE 			= '305000000';
					$AUTO_COA 			= 'n';
					$MOTHER_ACCOUNT 	= 'dr';
					$CASH_FLOW_HEAD 	= 'n';
					$LFT        		= '19';
					$RGT     			= '20';
					$data = array(
						'COMPANY_ID' 		=> $COMPANY_ID,
						'COA_NAME' 			=> $COA_NAME,
						'COA_CODE' 			=> $COA_CODE,
						'AUTO_COA' 			=> $AUTO_COA,
						'MOTHER_ACCOUNT' 	=> $MOTHER_ACCOUNT,
						'CASH_FLOW_HEAD' 	=> $CASH_FLOW_HEAD,
						'LFT' 				=> $LFT,
						'RGT' 				=> $RGT,
						'BUSINESS_DATE' 	=> date ('Y-m-d', strtotime($businessdate)),
						'RECORD_DATE' 		=> date ('Y-m-d H:i:s', strtotime($recdate)),
						'OPERATE_BY' 		=> $userid,
					);				
					$existCheckData = array(
						'COA_CODE' => $COA_CODE,
					);
					if(!($this->initialCoaExist($existCheckData))) {
						$this->tableGateway->insert($data);
					}
					//Asset Block - 6
					$COMPANY_ID 		= '1';
					$COA_NAME     		= 'Term Deposit Receipt-FDR';
					$COA_CODE 			= '306000000';
					$AUTO_COA 			= 'n';
					$MOTHER_ACCOUNT 	= 'dr';
					$CASH_FLOW_HEAD 	= 'n';
					$LFT        		= '21';
					$RGT     			= '22';
					$data = array(
						'COMPANY_ID' 		=> $COMPANY_ID,
						'COA_NAME' 			=> $COA_NAME,
						'COA_CODE' 			=> $COA_CODE,
						'AUTO_COA' 			=> $AUTO_COA,
						'MOTHER_ACCOUNT' 	=> $MOTHER_ACCOUNT,
						'CASH_FLOW_HEAD' 	=> $CASH_FLOW_HEAD,
						'LFT' 				=> $LFT,
						'RGT' 				=> $RGT,
						'BUSINESS_DATE' 	=> date ('Y-m-d', strtotime($businessdate)),
						'RECORD_DATE' 		=> date ('Y-m-d H:i:s', strtotime($recdate)),
						'OPERATE_BY' 		=> $userid,
					);				
					$existCheckData = array(
						'COA_CODE' => $COA_CODE,
					);
					if(!($this->initialCoaExist($existCheckData))) {
						$this->tableGateway->insert($data);
					}
					//Asset Block - 7
					$COMPANY_ID 		= '1';
					$COA_NAME     		= 'Other Advance Deposits & Securities';
					$COA_CODE 			= '307000000';
					$AUTO_COA 			= 'n';
					$MOTHER_ACCOUNT 	= 'dr';
					$CASH_FLOW_HEAD 	= 'n';
					$LFT        		= '23';
					$RGT     			= '24';
					$data = array(
						'COMPANY_ID' 		=> $COMPANY_ID,
						'COA_NAME' 			=> $COA_NAME,
						'COA_CODE' 			=> $COA_CODE,
						'AUTO_COA' 			=> $AUTO_COA,
						'MOTHER_ACCOUNT' 	=> $MOTHER_ACCOUNT,
						'CASH_FLOW_HEAD' 	=> $CASH_FLOW_HEAD,
						'LFT' 				=> $LFT,
						'RGT' 				=> $RGT,
						'BUSINESS_DATE' 	=> date ('Y-m-d', strtotime($businessdate)),
						'RECORD_DATE' 		=> date ('Y-m-d H:i:s', strtotime($recdate)),
						'OPERATE_BY' 		=> $userid,
					);				
					$existCheckData = array(
						'COA_CODE' => $COA_CODE,
					);
					if(!($this->initialCoaExist($existCheckData))) {
						$this->tableGateway->insert($data);
					}
			/////////////////////////////////////////////////////////////////////////////////////////////////
				//Income Start
					$COMPANY_ID 		= '1';
					$COA_NAME     		= 'INCOME';
					$COA_CODE 			= '500000000';
					$AUTO_COA 			= 'n';
					$MOTHER_ACCOUNT 	= 'cr';
					$CASH_FLOW_HEAD 	= 'n';
					$LFT        		= '26';
					$RGT     			= '29';
					$data = array(
						'COMPANY_ID' 		=> $COMPANY_ID,
						'COA_NAME' 			=> $COA_NAME,
						'COA_CODE' 			=> $COA_CODE,
						'AUTO_COA' 			=> $AUTO_COA,
						'MOTHER_ACCOUNT' 	=> $MOTHER_ACCOUNT,
						'CASH_FLOW_HEAD' 	=> $CASH_FLOW_HEAD,
						'LFT' 				=> $LFT,
						'RGT' 				=> $RGT,
						'BUSINESS_DATE' 	=> date ('Y-m-d', strtotime($businessdate)),
						'RECORD_DATE' 		=> date ('Y-m-d H:i:s', strtotime($recdate)),
						'OPERATE_BY' 		=> $userid,
					);
					$existCheckData = array(
						'COA_CODE' => $COA_CODE,
					);
					if(!($this->initialCoaExist($existCheckData))) {
						$this->tableGateway->insert($data);
					}
				//Income Section.
				$COMPANY_ID 		= '1';
				$COA_NAME     		= 'Interest Income';
				$COA_CODE 			= '501000000';
				$AUTO_COA 			= 'n';
				$MOTHER_ACCOUNT 	= 'cr';
				$CASH_FLOW_HEAD 	= 'n';
				$LFT        		= '27';
				$RGT     			= '28';
				
				$data = array(
					'COMPANY_ID' 		=> $COMPANY_ID,
					'COA_NAME' 			=> $COA_NAME,
					'COA_CODE' 			=> $COA_CODE,
					'AUTO_COA' 			=> $AUTO_COA,
					'MOTHER_ACCOUNT' 	=> $MOTHER_ACCOUNT,
					'CASH_FLOW_HEAD' 	=> $CASH_FLOW_HEAD,
					'LFT' 				=> $LFT,
					'RGT' 				=> $RGT,
					'BUSINESS_DATE' 	=> date ('Y-m-d', strtotime($businessdate)),
					'RECORD_DATE' 		=> date ('Y-m-d H:i:s', strtotime($recdate)),
					'OPERATE_BY' 		=> $userid,
				);
				$existCheckData = array(
					'COA_CODE' => $COA_CODE,
				);
				if(!($this->initialCoaExist($existCheckData))) {
					$this->tableGateway->insert($data);
				}
	/////////////////////////////////////////////////////////////////////////////////////////////////////////
					//Expense Start
					$COMPANY_ID 		= '1';
					$COA_NAME     		= 'EXPENSE';
					$COA_CODE 			= '600000000';
					$AUTO_COA 			= 'n';
					$MOTHER_ACCOUNT 	= 'dr';
					$CASH_FLOW_HEAD 	= 'n';
					$LFT        		= '30';
					$RGT     			= '35';
					$data = array(
						'COMPANY_ID' 		=> $COMPANY_ID,
						'COA_NAME' 			=> $COA_NAME,
						'COA_CODE' 			=> $COA_CODE,
						'AUTO_COA' 			=> $AUTO_COA,
						'MOTHER_ACCOUNT' 	=> $MOTHER_ACCOUNT,
						'CASH_FLOW_HEAD' 	=> $CASH_FLOW_HEAD,
						'LFT' 				=> $LFT,
						'RGT' 				=> $RGT,
						'BUSINESS_DATE' 	=> date ('Y-m-d', strtotime($businessdate)),
						'RECORD_DATE' 		=> date ('Y-m-d H:i:s', strtotime($recdate)),
						'OPERATE_BY' 		=> $userid,
					);
					$existCheckData = array(
						'COA_CODE' => $COA_CODE,
					);
					if(!($this->initialCoaExist($existCheckData))) {
						$this->tableGateway->insert($data);
					}
				//Expense Section.
					//Expense Block - 1
					$COMPANY_ID 		= '1';
					$COA_NAME     		= 'Operating expenses';
					$COA_CODE 			= '601000000';
					$AUTO_COA 			= 'n';
					$MOTHER_ACCOUNT 	= 'dr';
					$CASH_FLOW_HEAD 	= 'n';
					$LFT        		= '31';
					$RGT     			= '32';
					$data = array(
						'COMPANY_ID' 		=> $COMPANY_ID,
						'COA_NAME' 			=> $COA_NAME,
						'COA_CODE' 			=> $COA_CODE,
						'AUTO_COA' 			=> $AUTO_COA,
						'MOTHER_ACCOUNT' 	=> $MOTHER_ACCOUNT,
						'CASH_FLOW_HEAD' 	=> $CASH_FLOW_HEAD,
						'LFT' 				=> $LFT,
						'RGT' 				=> $RGT,
						'BUSINESS_DATE' 	=> date ('Y-m-d', strtotime($businessdate)),
						'RECORD_DATE' 		=> date ('Y-m-d H:i:s', strtotime($recdate)),
						'OPERATE_BY' 		=> $userid,
					);
					$existCheckData = array(
						'COA_CODE' => $COA_CODE,
					);
					if(!($this->initialCoaExist($existCheckData))) {
						$this->tableGateway->insert($data);
					}
					//Expense Block - 2
					$COMPANY_ID 		= '1';
					$COA_NAME     		= 'Non-operating Expense';
					$COA_CODE 			= '602000000';
					$AUTO_COA 			= 'n';
					$MOTHER_ACCOUNT 	= 'dr';
					$CASH_FLOW_HEAD 	= 'n';
					$LFT        		= '33';
					$RGT     			= '34';
					$data = array(
						'COMPANY_ID' 		=> $COMPANY_ID,
						'COA_NAME' 			=> $COA_NAME,
						'COA_CODE' 			=> $COA_CODE,
						'AUTO_COA' 			=> $AUTO_COA,
						'MOTHER_ACCOUNT' 	=> $MOTHER_ACCOUNT,
						'CASH_FLOW_HEAD' 	=> $CASH_FLOW_HEAD,
						'LFT' 				=> $LFT,
						'RGT' 				=> $RGT,
						'BUSINESS_DATE' 	=> date ('Y-m-d', strtotime($businessdate)),
						'RECORD_DATE' 		=> date ('Y-m-d H:i:s', strtotime($recdate)),
						'OPERATE_BY' 		=> $userid,
					);
					$existCheckData = array(
						'COA_CODE' => $COA_CODE,
					);
					if(!($this->initialCoaExist($existCheckData))) {
						$this->tableGateway->insert($data);
					}				
			
			//echo "<pre>"; print_r($data); die();
///////////////////////////////////////////////////////////////////////////////////////////			
			//Start Initial Supplier Payable Head Entry
			$PAYABLE_COA_NAME_1 		= "Payable To Supplier";
			$PAYABLE_COA_1 				= "201001000";
			$PAYABLE_AUTO_COA_1 		= 'y';
			$coaSql = "SELECT 
											CN.COMPANY_ID AS PAYABLE_COMPANY_ID_1,
											C.COA_ID AS PAYABLE_MHEAD_COA_ID_1,
											C.CASH_FLOW_HEAD AS PAYABLE_MHD_C_F_H_1
									FROM 
											gs_coa C,
											c_company CN
									WHERE 
											C.COMPANY_ID  = CN.COMPANY_ID     
									AND   	C.COA_CODE  = '201000000'
									ORDER BY 
											C.RGT		
								";
			$coaSqlStmt	= $this->tableGateway->getAdapter()->createStatement($coaSql);
			$coaSqlStmt->prepare();
			
			$coaSqlStmtResult = $coaSqlStmt->execute();
			
			if ($coaSqlStmtResult instanceof ResultInterface && $coaSqlStmtResult->isQueryResult()) {
				$resultSet 			= new ResultSet();
				$resultSet->initialize($coaSqlStmtResult);
			}
			//echo sizeof($resultSet); die();
			foreach($resultSet as $resultCOA) {
				$PAYABLE_COMPANY_ID_1			= $resultCOA->PAYABLE_COMPANY_ID_1;
				$PAYABLE_MHEAD_COA_ID_1			= $resultCOA->PAYABLE_MHEAD_COA_ID_1;
				$PAYABLE_MHD_C_F_H_1			= $resultCOA->PAYABLE_MHD_C_F_H_1;
			}
			
			
			if(sizeof($resultSet)==1) {
			$generalCoaData = array();
			$generalCoaData = array(
									"COMPANY_ID"=>array(
															$PAYABLE_COMPANY_ID_1,
														),
									"PARENT_COA"=>array(
															$PAYABLE_MHEAD_COA_ID_1,
														),
									"CASH_FLOW_HEAD"=>array(
															$PAYABLE_MHD_C_F_H_1,
														),
									"COA_CODE"=>array(
															$PAYABLE_COA_1,
														),
									"COA_NAME"=>array(
															$PAYABLE_COA_NAME_1,
														),
									"AUTO_COA"=>array(
															$PAYABLE_AUTO_COA_1,
														),
								);
								
			}			
			//End Initial Supplier Payable Head Entry
			//Start Initial Employee Payable Head Entry
			$PAYABLE_COA_NAME_2 		= "Payable To Employee";
			$PAYABLE_COA_2 				= "201002000";
			$PAYABLE_AUTO_COA_2 		= 'y';
			$coaSql = "SELECT 
											CN.COMPANY_ID AS PAYABLE_COMPANY_ID_2,
											C.COA_ID AS PAYABLE_MHEAD_COA_ID_2,
											C.CASH_FLOW_HEAD AS PAYABLE_MHD_C_F_H_2
									FROM 
											gs_coa C,
											c_company CN
									WHERE 
											C.COMPANY_ID  = CN.COMPANY_ID     
									AND   	C.COA_CODE  = '201000000'
									ORDER BY 
											C.RGT		
								";
			$coaSqlStmt	= $this->tableGateway->getAdapter()->createStatement($coaSql);
			$coaSqlStmt->prepare();
			
			$coaSqlStmtResult = $coaSqlStmt->execute();
			
			if ($coaSqlStmtResult instanceof ResultInterface && $coaSqlStmtResult->isQueryResult()) {
				$resultSet 			= new ResultSet();
				$resultSet->initialize($coaSqlStmtResult);
			}
			//echo sizeof($resultSet); die();
			foreach($resultSet as $resultCOA) {
				$PAYABLE_COMPANY_ID_2			= $resultCOA->PAYABLE_COMPANY_ID_2;
				$PAYABLE_MHEAD_COA_ID_2			= $resultCOA->PAYABLE_MHEAD_COA_ID_2;
				$PAYABLE_MHD_C_F_H_2			= $resultCOA->PAYABLE_MHD_C_F_H_2;
			}
			
			if(sizeof($resultSet)==1) {
				$generalCoaData = array();
				$generalCoaData = array(
										"COMPANY_ID"=>array(
																$PAYABLE_COMPANY_ID_1,
																$PAYABLE_COMPANY_ID_2,
															),
										"PARENT_COA"=>array(
																$PAYABLE_MHEAD_COA_ID_1,
																$PAYABLE_MHEAD_COA_ID_2,
															),
										"CASH_FLOW_HEAD"=>array(
																$PAYABLE_MHD_C_F_H_1,
																$PAYABLE_MHD_C_F_H_2,
															),
										"COA_CODE"=>array(
																$PAYABLE_COA_1,
																$PAYABLE_COA_2,
															),
										"COA_NAME"=>array(
																$PAYABLE_COA_NAME_1,
																$PAYABLE_COA_NAME_2,
															),
										"AUTO_COA"=>array(
																$PAYABLE_AUTO_COA_1,
																$PAYABLE_AUTO_COA_2,
															),
									);
								
			}			
			//End Initial Employee Payable Head Entry
			//Start Initial Supplier Receivable Head Entry
			$RECEIVABLE_COA_NAME_1 		= "Receivable From Supplier";
			$RECEIVABLE_COA_1 				= "302001000";
			$RECEIVABLE_AUTO_COA_1 		= 'y';
			$coaSql = "SELECT 
											CN.COMPANY_ID AS RECEIVABLE_COMPANY_ID_1,
											C.COA_ID AS RECEIVABLE_MHEAD_COA_ID_1,
											C.CASH_FLOW_HEAD AS RECEIVABLE_MHD_C_F_H_1
									FROM 
											gs_coa C,
											c_company CN
									WHERE 
											C.COMPANY_ID  = CN.COMPANY_ID     
									AND   	C.COA_CODE  = '302000000'
									ORDER BY 
											C.RGT		
								";
			$coaSqlStmt	= $this->tableGateway->getAdapter()->createStatement($coaSql);
			$coaSqlStmt->prepare();
			
			$coaSqlStmtResult = $coaSqlStmt->execute();
			
			if ($coaSqlStmtResult instanceof ResultInterface && $coaSqlStmtResult->isQueryResult()) {
				$resultSet 			= new ResultSet();
				$resultSet->initialize($coaSqlStmtResult);
			}
			//echo sizeof($resultSet); die();
			foreach($resultSet as $resultCOA) {
				$RECEIVABLE_COMPANY_ID_1		= $resultCOA->RECEIVABLE_COMPANY_ID_1;
				$RECEIVABLE_MHEAD_COA_ID_1		= $resultCOA->RECEIVABLE_MHEAD_COA_ID_1;
				$RECEIVABLE_MHD_C_F_H_1			= $resultCOA->RECEIVABLE_MHD_C_F_H_1;
			}
			
			if(sizeof($resultSet)==1) {
			$generalCoaData = array();
			$generalCoaData = array(
									"COMPANY_ID"=>array(
															$PAYABLE_COMPANY_ID_1,
															$PAYABLE_COMPANY_ID_2,
															$RECEIVABLE_COMPANY_ID_1,
														),
									"PARENT_COA"=>array(
															$PAYABLE_MHEAD_COA_ID_1,
															$PAYABLE_MHEAD_COA_ID_2,
															$RECEIVABLE_MHEAD_COA_ID_1,
														),
									"CASH_FLOW_HEAD"=>array(
															$PAYABLE_MHD_C_F_H_1,
															$PAYABLE_MHD_C_F_H_2,
															$RECEIVABLE_MHD_C_F_H_1,
														),
									"COA_CODE"=>array(
															$PAYABLE_COA_1,
															$PAYABLE_COA_2,
															$RECEIVABLE_COA_1,
														),
									"COA_NAME"=>array(
															$PAYABLE_COA_NAME_1,
															$PAYABLE_COA_NAME_2,
															$RECEIVABLE_COA_NAME_1,
														),
									"AUTO_COA"=>array(
															$PAYABLE_AUTO_COA_1,
															$PAYABLE_AUTO_COA_2,
															$RECEIVABLE_AUTO_COA_1,
														),
								);
								
			}			
			//End Initial Supplier Receivable Head Entry
			//Start Initial Employee Receivable Head Entry
			$RECEIVABLE_COA_NAME_2		= "Receivable From Employee";
			$RECEIVABLE_COA_2 				= "302002000";
			$RECEIVABLE_AUTO_COA_2 		= 'y';
			$coaSql = "SELECT 
											CN.COMPANY_ID AS RECEIVABLE_COMPANY_ID_2,
											C.COA_ID AS RECEIVABLE_MHEAD_COA_ID_2,
											C.CASH_FLOW_HEAD AS RECEIVABLE_MHD_C_F_H_2
									FROM 
											gs_coa C,
											c_company CN
									WHERE 
											C.COMPANY_ID  = CN.COMPANY_ID     
									AND   	C.COA_CODE  = '302000000'
									ORDER BY 
											C.RGT		
								";
			$coaSqlStmt	= $this->tableGateway->getAdapter()->createStatement($coaSql);
			$coaSqlStmt->prepare();
			
			$coaSqlStmtResult = $coaSqlStmt->execute();
			
			if ($coaSqlStmtResult instanceof ResultInterface && $coaSqlStmtResult->isQueryResult()) {
				$resultSet 			= new ResultSet();
				$resultSet->initialize($coaSqlStmtResult);
			}
			//echo sizeof($resultSet); die();
			foreach($resultSet as $resultCOA) {
				$RECEIVABLE_COMPANY_ID_2		= $resultCOA->RECEIVABLE_COMPANY_ID_2;
				$RECEIVABLE_MHEAD_COA_ID_2		= $resultCOA->RECEIVABLE_MHEAD_COA_ID_2;
				$RECEIVABLE_MHD_C_F_H_2			= $resultCOA->RECEIVABLE_MHD_C_F_H_2;
			}
			
			if(sizeof($resultSet)==1) {
			$generalCoaData = array();
			$generalCoaData = array(
									"COMPANY_ID"=>array(
															$PAYABLE_COMPANY_ID_1,
															$PAYABLE_COMPANY_ID_2,
															$RECEIVABLE_COMPANY_ID_1,
															$RECEIVABLE_COMPANY_ID_2,
														),
									"PARENT_COA"=>array(
															$PAYABLE_MHEAD_COA_ID_1,
															$PAYABLE_MHEAD_COA_ID_2,
															$RECEIVABLE_MHEAD_COA_ID_1,
															$RECEIVABLE_MHEAD_COA_ID_2,
														),
									"CASH_FLOW_HEAD"=>array(
															$PAYABLE_MHD_C_F_H_1,
															$PAYABLE_MHD_C_F_H_2,
															$RECEIVABLE_MHD_C_F_H_1,
															$RECEIVABLE_MHD_C_F_H_2,
														),
									"COA_CODE"=>array(
															$PAYABLE_COA_1,
															$PAYABLE_COA_2,
															$RECEIVABLE_COA_1,
															$RECEIVABLE_COA_2,
														),
									"COA_NAME"=>array(
															$PAYABLE_COA_NAME_1,
															$PAYABLE_COA_NAME_2,
															$RECEIVABLE_COA_NAME_1,
															$RECEIVABLE_COA_NAME_2,
														),
									"AUTO_COA"=>array(
															$PAYABLE_AUTO_COA_1,
															$PAYABLE_AUTO_COA_2,
															$RECEIVABLE_AUTO_COA_1,
															$RECEIVABLE_AUTO_COA_2,
														),
								);
								
			}
			//End Initial Employee Receivable Head Entry
			//Start Initial Interest Receivable from STD Head Entry
			$RECEIVABLE_COA_NAME_3		= "Interest Receivable from STD";
			$RECEIVABLE_COA_3 			= "302003000";
			$RECEIVABLE_AUTO_COA_3 		= 'y';
			$coaSql = "SELECT 
											CN.COMPANY_ID AS RECEIVABLE_COMPANY_ID_3,
											C.COA_ID AS RECEIVABLE_MHEAD_COA_ID_3,
											C.CASH_FLOW_HEAD AS RECEIVABLE_MHD_C_F_H_3
									FROM 
											gs_coa C,
											c_company CN
									WHERE 
											C.COMPANY_ID  = CN.COMPANY_ID     
									AND   	C.COA_CODE  = '302000000'
									ORDER BY 
											C.RGT		
								";
			$coaSqlStmt	= $this->tableGateway->getAdapter()->createStatement($coaSql);
			$coaSqlStmt->prepare();
			
			$coaSqlStmtResult = $coaSqlStmt->execute();
			
			if ($coaSqlStmtResult instanceof ResultInterface && $coaSqlStmtResult->isQueryResult()) {
				$resultSet 			= new ResultSet();
				$resultSet->initialize($coaSqlStmtResult);
			}
			//echo sizeof($resultSet); die();
			foreach($resultSet as $resultCOA) {
				$RECEIVABLE_COMPANY_ID_3		= $resultCOA->RECEIVABLE_COMPANY_ID_3;
				$RECEIVABLE_MHEAD_COA_ID_3		= $resultCOA->RECEIVABLE_MHEAD_COA_ID_3;
				$RECEIVABLE_MHD_C_F_H_3			= $resultCOA->RECEIVABLE_MHD_C_F_H_3;
			}
			
			if(sizeof($resultSet)==1) {
			$generalCoaData = array();
			$generalCoaData = array(
									"COMPANY_ID"=>array(
															$PAYABLE_COMPANY_ID_1,
															$PAYABLE_COMPANY_ID_2,
															$RECEIVABLE_COMPANY_ID_1,
															$RECEIVABLE_COMPANY_ID_2,
															$RECEIVABLE_COMPANY_ID_3,
														),
									"PARENT_COA"=>array(
															$PAYABLE_MHEAD_COA_ID_1,
															$PAYABLE_MHEAD_COA_ID_2,
															$RECEIVABLE_MHEAD_COA_ID_1,
															$RECEIVABLE_MHEAD_COA_ID_2,
															$RECEIVABLE_MHEAD_COA_ID_3,
														),
									"CASH_FLOW_HEAD"=>array(
															$PAYABLE_MHD_C_F_H_1,
															$PAYABLE_MHD_C_F_H_2,
															$RECEIVABLE_MHD_C_F_H_1,
															$RECEIVABLE_MHD_C_F_H_2,
															$RECEIVABLE_MHD_C_F_H_3,
														),
									"COA_CODE"=>array(
															$PAYABLE_COA_1,
															$PAYABLE_COA_2,
															$RECEIVABLE_COA_1,
															$RECEIVABLE_COA_2,
															$RECEIVABLE_COA_3,
														),
									"COA_NAME"=>array(
															$PAYABLE_COA_NAME_1,
															$PAYABLE_COA_NAME_2,
															$RECEIVABLE_COA_NAME_1,
															$RECEIVABLE_COA_NAME_2,
															$RECEIVABLE_COA_NAME_3,
														),
									"AUTO_COA"=>array(
															$PAYABLE_AUTO_COA_1,
															$PAYABLE_AUTO_COA_2,
															$RECEIVABLE_AUTO_COA_1,
															$RECEIVABLE_AUTO_COA_2,
															$RECEIVABLE_AUTO_COA_3,
														),
								);
								
			}
			//End Initial Interest Receivable from STD Head Entry
			//Start Initial Cash at Bank Head Entry
			$COA_NAME_4		= "Cash at Bank";
			$COA_4 			= "304001000";
			$AUTO_COA_4 		= 'y';
			$coaSql = "SELECT 
											CN.COMPANY_ID AS COMPANY_ID_4,
											C.COA_ID AS MHEAD_COA_ID_4,
											C.CASH_FLOW_HEAD AS MHD_C_F_H_4
									FROM 
											gs_coa C,
											c_company CN
									WHERE 
											C.COMPANY_ID  = CN.COMPANY_ID     
									AND   	C.COA_CODE  = '304000000'
									ORDER BY 
											C.RGT		
								";
			$coaSqlStmt	= $this->tableGateway->getAdapter()->createStatement($coaSql);
			$coaSqlStmt->prepare();
			
			$coaSqlStmtResult = $coaSqlStmt->execute();
			
			if ($coaSqlStmtResult instanceof ResultInterface && $coaSqlStmtResult->isQueryResult()) {
				$resultSet 			= new ResultSet();
				$resultSet->initialize($coaSqlStmtResult);
			}
			//echo sizeof($resultSet); die();
			foreach($resultSet as $resultCOA) {
				$COMPANY_ID_4		= $resultCOA->COMPANY_ID_4;
				$MHEAD_COA_ID_4		= $resultCOA->MHEAD_COA_ID_4;
				$MHD_C_F_H_4		= $resultCOA->MHD_C_F_H_4;
			}
			
			if(sizeof($resultSet)==1) {
			$generalCoaData = array();
			$generalCoaData = array(
									"COMPANY_ID"=>array(
															$PAYABLE_COMPANY_ID_1,
															$PAYABLE_COMPANY_ID_2,
															$RECEIVABLE_COMPANY_ID_1,
															$RECEIVABLE_COMPANY_ID_2,
															$RECEIVABLE_COMPANY_ID_3,
															$COMPANY_ID_4,
														),
									"PARENT_COA"=>array(
															$PAYABLE_MHEAD_COA_ID_1,
															$PAYABLE_MHEAD_COA_ID_2,
															$RECEIVABLE_MHEAD_COA_ID_1,
															$RECEIVABLE_MHEAD_COA_ID_2,
															$RECEIVABLE_MHEAD_COA_ID_3,
															$MHEAD_COA_ID_4,
														),
									"CASH_FLOW_HEAD"=>array(
															$PAYABLE_MHD_C_F_H_1,
															$PAYABLE_MHD_C_F_H_2,
															$RECEIVABLE_MHD_C_F_H_1,
															$RECEIVABLE_MHD_C_F_H_2,
															$RECEIVABLE_MHD_C_F_H_3,
															$MHD_C_F_H_4,
														),
									"COA_CODE"=>array(
															$PAYABLE_COA_1,
															$PAYABLE_COA_2,
															$RECEIVABLE_COA_1,
															$RECEIVABLE_COA_2,
															$RECEIVABLE_COA_3,
															$COA_4,
														),
									"COA_NAME"=>array(
															$PAYABLE_COA_NAME_1,
															$PAYABLE_COA_NAME_2,
															$RECEIVABLE_COA_NAME_1,
															$RECEIVABLE_COA_NAME_2,
															$RECEIVABLE_COA_NAME_3,
															$COA_NAME_4,
														),
									"AUTO_COA"=>array(
															$PAYABLE_AUTO_COA_1,
															$PAYABLE_AUTO_COA_2,
															$RECEIVABLE_AUTO_COA_1,
															$RECEIVABLE_AUTO_COA_2,
															$RECEIVABLE_AUTO_COA_3,
															$AUTO_COA_4,
														),
								);
								
			}			
			//End Initial Cash at Bank Head Entry
			//Start Initial Cash in Hand Head Entry
			$COA_NAME_5		= "Cash in Hand ";
			$COA_5 			= "303001000";
			$AUTO_COA_5 		= 'y';
			$coaSql = "SELECT 
											CN.COMPANY_ID AS COMPANY_ID_5,
											C.COA_ID AS MHEAD_COA_ID_5,
											C.CASH_FLOW_HEAD AS MHD_C_F_H_5
									FROM 
											gs_coa C,
											c_company CN
									WHERE 
											C.COMPANY_ID  = CN.COMPANY_ID     
									AND   	C.COA_CODE  = '303000000'
									ORDER BY 
											C.RGT		
								";
			$coaSqlStmt	= $this->tableGateway->getAdapter()->createStatement($coaSql);
			$coaSqlStmt->prepare();
			
			$coaSqlStmtResult = $coaSqlStmt->execute();
			
			if ($coaSqlStmtResult instanceof ResultInterface && $coaSqlStmtResult->isQueryResult()) {
				$resultSet 			= new ResultSet();
				$resultSet->initialize($coaSqlStmtResult);
			}
			//echo sizeof($resultSet); die();
			foreach($resultSet as $resultCOA) {
				$COMPANY_ID_5		= $resultCOA->COMPANY_ID_5;
				$MHEAD_COA_ID_5		= $resultCOA->MHEAD_COA_ID_5;
				$MHD_C_F_H_5		= $resultCOA->MHD_C_F_H_5;
			}
			
			if(sizeof($resultSet)==1) {
			$generalCoaData = array();
			$generalCoaData = array(
									"COMPANY_ID"=>array(
															$PAYABLE_COMPANY_ID_1,
															$PAYABLE_COMPANY_ID_2,
															$RECEIVABLE_COMPANY_ID_1,
															$RECEIVABLE_COMPANY_ID_2,
															$RECEIVABLE_COMPANY_ID_3,
															$COMPANY_ID_4,
															$COMPANY_ID_5,
														),
									"PARENT_COA"=>array(
															$PAYABLE_MHEAD_COA_ID_1,
															$PAYABLE_MHEAD_COA_ID_2,
															$RECEIVABLE_MHEAD_COA_ID_1,
															$RECEIVABLE_MHEAD_COA_ID_2,
															$RECEIVABLE_MHEAD_COA_ID_3,
															$MHEAD_COA_ID_4,
															$MHEAD_COA_ID_5,
														),
									"CASH_FLOW_HEAD"=>array(
															$PAYABLE_MHD_C_F_H_1,
															$PAYABLE_MHD_C_F_H_2,
															$RECEIVABLE_MHD_C_F_H_1,
															$RECEIVABLE_MHD_C_F_H_2,
															$RECEIVABLE_MHD_C_F_H_3,
															$MHD_C_F_H_4,
															$MHD_C_F_H_5,
														),
									"COA_CODE"=>array(
															$PAYABLE_COA_1,
															$PAYABLE_COA_2,
															$RECEIVABLE_COA_1,
															$RECEIVABLE_COA_2,
															$RECEIVABLE_COA_3,
															$COA_4,
															$COA_5,
														),
									"COA_NAME"=>array(
															$PAYABLE_COA_NAME_1,
															$PAYABLE_COA_NAME_2,
															$RECEIVABLE_COA_NAME_1,
															$RECEIVABLE_COA_NAME_2,
															$RECEIVABLE_COA_NAME_3,
															$COA_NAME_4,
															$COA_NAME_5,
														),
									"AUTO_COA"=>array(
															$PAYABLE_AUTO_COA_1,
															$PAYABLE_AUTO_COA_2,
															$RECEIVABLE_AUTO_COA_1,
															$RECEIVABLE_AUTO_COA_2,
															$RECEIVABLE_AUTO_COA_3,
															$AUTO_COA_4,
															$AUTO_COA_5,
														),
								);
								
			}			
			//End Initial Cash in Hand Head Entry
			//Start Initial Retailer Payable Head Entry
			$PAYABLE_COA_NAME_3 		= "Payable To Retailer";
			$PAYABLE_COA_3 				= "201003000";
			$PAYABLE_AUTO_COA_3 		= 'y';
			$coaSql = "SELECT 
											CN.COMPANY_ID AS PAYABLE_COMPANY_ID_3,
											C.COA_ID AS PAYABLE_MHEAD_COA_ID_3,
											C.CASH_FLOW_HEAD AS PAYABLE_MHD_C_F_H_3
									FROM 
											gs_coa C,
											c_company CN
									WHERE 
											C.COMPANY_ID  = CN.COMPANY_ID     
									AND   	C.COA_CODE  = '201000000'
									ORDER BY 
											C.RGT		
								";
			$coaSqlStmt	= $this->tableGateway->getAdapter()->createStatement($coaSql);
			$coaSqlStmt->prepare();
			
			$coaSqlStmtResult = $coaSqlStmt->execute();
			
			if ($coaSqlStmtResult instanceof ResultInterface && $coaSqlStmtResult->isQueryResult()) {
				$resultSet 			= new ResultSet();
				$resultSet->initialize($coaSqlStmtResult);
			}
			foreach($resultSet as $resultCOA) {
				$PAYABLE_COMPANY_ID_3			= $resultCOA->PAYABLE_COMPANY_ID_3;
				$PAYABLE_MHEAD_COA_ID_3			= $resultCOA->PAYABLE_MHEAD_COA_ID_3;
				$PAYABLE_MHD_C_F_H_3			= $resultCOA->PAYABLE_MHD_C_F_H_3;
			}
			
			
			if(sizeof($resultSet)==1) {
					
				$generalCoaData = array();
				$generalCoaData = array(
										"COMPANY_ID"=>array(
																$PAYABLE_COMPANY_ID_1,
																$PAYABLE_COMPANY_ID_2,
																$PAYABLE_COMPANY_ID_3,
																$RECEIVABLE_COMPANY_ID_1,
																$RECEIVABLE_COMPANY_ID_2,
																$RECEIVABLE_COMPANY_ID_3,
																$COMPANY_ID_4,
																$COMPANY_ID_5,
															),
										"PARENT_COA"=>array(
																$PAYABLE_MHEAD_COA_ID_1,
																$PAYABLE_MHEAD_COA_ID_2,
																$PAYABLE_MHEAD_COA_ID_3,
																$RECEIVABLE_MHEAD_COA_ID_1,
																$RECEIVABLE_MHEAD_COA_ID_2,
																$RECEIVABLE_MHEAD_COA_ID_3,
																$MHEAD_COA_ID_4,
																$MHEAD_COA_ID_5,
															),
										"CASH_FLOW_HEAD"=>array(
																$PAYABLE_MHD_C_F_H_1,
																$PAYABLE_MHD_C_F_H_2,
																$PAYABLE_MHD_C_F_H_3,
																$RECEIVABLE_MHD_C_F_H_1,
																$RECEIVABLE_MHD_C_F_H_2,
																$RECEIVABLE_MHD_C_F_H_3,
																$MHD_C_F_H_4,
																$MHD_C_F_H_5,
															),
										"COA_CODE"=>array(
																$PAYABLE_COA_1,
																$PAYABLE_COA_2,
																$PAYABLE_COA_3,
																$RECEIVABLE_COA_1,
																$RECEIVABLE_COA_2,
																$RECEIVABLE_COA_3,
																$COA_4,
																$COA_5,
															),
										"COA_NAME"=>array(
																$PAYABLE_COA_NAME_1,
																$PAYABLE_COA_NAME_2,
																$PAYABLE_COA_NAME_3,
																$RECEIVABLE_COA_NAME_1,
																$RECEIVABLE_COA_NAME_2,
																$RECEIVABLE_COA_NAME_3,
																$COA_NAME_4,
																$COA_NAME_5,
															),
										"AUTO_COA"=>array(
																$PAYABLE_AUTO_COA_1,
																$PAYABLE_AUTO_COA_2,
																$PAYABLE_AUTO_COA_3,
																$RECEIVABLE_AUTO_COA_1,
																$RECEIVABLE_AUTO_COA_2,
																$RECEIVABLE_AUTO_COA_3,
																$AUTO_COA_4,
																$AUTO_COA_5,
															),
									);
									
					
			}			
			//End Initial Retailer Payable Head Entry
			//Start Initial Retailer Receivable Head Entry
			$RECEIVABLE_COA_NAME_4 		= "Receivable From Retailer";
			$RECEIVABLE_COA_4 			= "302004000";
			$RECEIVABLE_AUTO_COA_4 		= 'y';
			$coaSql = "SELECT 
											CN.COMPANY_ID AS RECEIVABLE_COMPANY_ID_4,
											C.COA_ID AS RECEIVABLE_MHEAD_COA_ID_4,
											C.CASH_FLOW_HEAD AS RECEIVABLE_MHD_C_F_H_4
									FROM 
											gs_coa C,
											c_company CN
									WHERE 
											C.COMPANY_ID  = CN.COMPANY_ID     
									AND   	C.COA_CODE  = '302000000'
									ORDER BY 
											C.RGT		
								";
			$coaSqlStmt	= $this->tableGateway->getAdapter()->createStatement($coaSql);
			$coaSqlStmt->prepare();
			
			$coaSqlStmtResult = $coaSqlStmt->execute();
			
			if ($coaSqlStmtResult instanceof ResultInterface && $coaSqlStmtResult->isQueryResult()) {
				$resultSet 			= new ResultSet();
				$resultSet->initialize($coaSqlStmtResult);
			}
			//echo sizeof($resultSet); die();
			foreach($resultSet as $resultCOA) {
				$RECEIVABLE_COMPANY_ID_4		= $resultCOA->RECEIVABLE_COMPANY_ID_4;
				$RECEIVABLE_MHEAD_COA_ID_4		= $resultCOA->RECEIVABLE_MHEAD_COA_ID_4;
				$RECEIVABLE_MHD_C_F_H_4			= $resultCOA->RECEIVABLE_MHD_C_F_H_4;
			}
			
			if(sizeof($resultSet)==1) {
				$generalCoaData = array();
				$generalCoaData = array(
						"COMPANY_ID"=>array(
												$PAYABLE_COMPANY_ID_1,
												$PAYABLE_COMPANY_ID_2,
												$PAYABLE_COMPANY_ID_3,
												$RECEIVABLE_COMPANY_ID_1,
												$RECEIVABLE_COMPANY_ID_2,
												$RECEIVABLE_COMPANY_ID_3,
												$RECEIVABLE_COMPANY_ID_4,
												$COMPANY_ID_4,
												$COMPANY_ID_5,
											),
						"PARENT_COA"=>array(
												$PAYABLE_MHEAD_COA_ID_1,
												$PAYABLE_MHEAD_COA_ID_2,
												$PAYABLE_MHEAD_COA_ID_3,
												$RECEIVABLE_MHEAD_COA_ID_1,
												$RECEIVABLE_MHEAD_COA_ID_2,
												$RECEIVABLE_MHEAD_COA_ID_3,
												$RECEIVABLE_MHEAD_COA_ID_4,
												$MHEAD_COA_ID_4,
												$MHEAD_COA_ID_5,
											),
						"CASH_FLOW_HEAD"=>array(
												$PAYABLE_MHD_C_F_H_1,
												$PAYABLE_MHD_C_F_H_2,
												$PAYABLE_MHD_C_F_H_3,
												$RECEIVABLE_MHD_C_F_H_1,
												$RECEIVABLE_MHD_C_F_H_2,
												$RECEIVABLE_MHD_C_F_H_3,
												$RECEIVABLE_MHD_C_F_H_4,
												$MHD_C_F_H_4,
												$MHD_C_F_H_5,
											),
						"COA_CODE"=>array(
												$PAYABLE_COA_1,
												$PAYABLE_COA_2,
												$PAYABLE_COA_3,
												$RECEIVABLE_COA_1,
												$RECEIVABLE_COA_2,
												$RECEIVABLE_COA_3,
												$RECEIVABLE_COA_4,
												$COA_4,
												$COA_5,
											),
						"COA_NAME"=>array(
												$PAYABLE_COA_NAME_1,
												$PAYABLE_COA_NAME_2,
												$PAYABLE_COA_NAME_3,
												$RECEIVABLE_COA_NAME_1,
												$RECEIVABLE_COA_NAME_2,
												$RECEIVABLE_COA_NAME_3,
												$RECEIVABLE_COA_NAME_4,
												$COA_NAME_4,
												$COA_NAME_5,
											),
						"AUTO_COA"=>array(
												$PAYABLE_AUTO_COA_1,
												$PAYABLE_AUTO_COA_2,
												$PAYABLE_AUTO_COA_3,
												$RECEIVABLE_AUTO_COA_1,
												$RECEIVABLE_AUTO_COA_2,
												$RECEIVABLE_AUTO_COA_3,
												$RECEIVABLE_AUTO_COA_4,
												$AUTO_COA_4,
												$AUTO_COA_5,
											),
					);
								
			}			
			//End Initial Retailer Receivable Head Entry
			
			//Return array Start
			//echo "<pre>"; print_r($generalCoaData); die();
			$returnData	= array(
								"GCOA_DATA" 	=> $generalCoaData,
						  );
			//echo "<pre>"; print_r($returnData);die();
			return $returnData;
			//Return array End
			
			
			
		}
		
		
		
		public function transectionStart() {
			return $this->tableGateway->adapter->getDriver()->getConnection()->beginTransaction();
		}
		
		public function transectionEnd() {
			return $this->tableGateway->adapter->getDriver()->getConnection()->commit();
		}
		
		public function transectionInterrupted() {
			return $this->tableGateway->adapter->getDriver()->getConnection()->rollback();
		}
	}
?>	