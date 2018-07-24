<?php
	namespace Accounts\Model;
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	
	class MasterTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		
		public function saveMaster(Master $master) {
			//return $returnTransactionNo	= 1; die();
			$this->session 	= new SessionContainer('post_supply');
			$businessDate 	= $this->session->businessdate;
			$businessDate 	= date("Y-m-d", strtotime($businessDate));
			$recordDate 	= $this->session->recdate;//date("Y-m-d H:i:s", strtotime($businessDate));
			$userId 		= $this->session->userid;
			
			// Get Max Id Start By Akhand
			$maxTransaction			= "";
			$getMaxSql 				= "SELECT MAX(MASTER_ID) AS MAX_TRAN_NO FROM a_transaction_master";
			$getMaxStatement		= $this->tableGateway->getAdapter()->createStatement($getMaxSql);
			$getMaxStatement->prepare();
			$getMaxResult 			= $getMaxStatement->execute();
			
			if ($getMaxResult instanceof ResultInterface && $getMaxResult->isQueryResult()) {
				$resultSet 	= new ResultSet();
				$resultSet->initialize($getMaxResult);
			}
			foreach($resultSet as $MAX_ID) {
				$maxTransaction = $MAX_ID->MAX_TRAN_NO;
			}
			if($maxTransaction) {
				$maxTransaction	= $maxTransaction+1;	
			} else {
				$maxTransaction	= 1;	
			}
			
			// Get Max Id End By Akhand
			$data = array(
				'TRAN_NO'				=> $maxTransaction,
				'TRAN_DATE' 			=> date("Y-m-d", strtotime($master->TRAN_DATE)),
				'VOUCHER_NO' 			=> $master->VOUCHER_NO,
				'NTR' 					=> $master->NTR,
				'CBJT' 					=> $master->CBJT,
				'CB_CODE' 				=> $master->CB_CODE,
				'CHQ_NO' 				=> $master->CHEQUE_NO,
				'CHQ_DATE' 				=> date("Y-m-d", strtotime($master->CHEQUE_DATE)),
				'EFFECTED_DATE' 		=> date("Y-m-d", strtotime($master->EFFECTED_DATE)),
				'RECONCILIATION_FLAG' 	=> $master->RECONCILIATION_FLAG,
				'RECORD_DATE' 			=> $recordDate,
				'DRAWN_ON' 				=> $master->DRAWN_ON,
				'AUTO_TRANSACTION_FLAG' => $master->AUTO_TRANSACTION_FLAG,
				'INVOICE_NO' 			=> $master->INVOICE_NO,
				'MONEY_RECEIPT_NO' 		=> $master->MONEY_RECEIPT_NO,
				'BACK_DATE' 			=> $master->BACK_DATE,
				'OPERATE_BY' 			=> $userId,
			);
			//echo "<pre>"; print_r($data);die();
			if($this->tableGateway->insert($data)) {
				return $maxTransaction;
				// Get Max Id End By Akhand
			} else {
				return false;	
			}
		}
		
		
		public function saveMasterContra(Master $master) {
			//return $returnTransactionNo	= 1; die();
			$this->session 	= new SessionContainer('post_supply');
			$businessDate 	= $this->session->businessdate;
			$businessDate 	= date("Y-m-d", strtotime($businessDate));
			$recordDate 	= $this->session->recdate;//date("Y-m-d H:i:s", strtotime($businessDate));
			$userId 		= $this->session->userid;
			
			// Get Max Id Start By Akhand
			$maxTransaction			= "";
			$getMaxSql 				= "SELECT MAX(MASTER_ID) AS MAX_TRAN_NO FROM a_transaction_master";
			$getMaxStatement		= $this->tableGateway->getAdapter()->createStatement($getMaxSql);
			$getMaxStatement->prepare();
			$getMaxResult 			= $getMaxStatement->execute();
			
			if ($getMaxResult instanceof ResultInterface && $getMaxResult->isQueryResult()) {
				$resultSet 	= new ResultSet();
				$resultSet->initialize($getMaxResult);
			}
			foreach($resultSet as $MAX_ID) {
				$maxTransaction = $MAX_ID->MAX_TRAN_NO;
			}
			if($maxTransaction) {
				$maxTransaction	= $maxTransaction+1;	
			} else {
				$maxTransaction	= 1;	
			}
			//echo "<pre>"; print_r($master); die();
			// Get Max Id End By Akhand
			$ntrr			= '';
			if($master->NTR	== 'D') {
				$ntrr		= 'C';
			} else {
				$ntrr		= 'D';	
			}
			
			$cbjt			= '';
			if(substr($master->CB_CODE,0,3) == '303') {
				$cbjt		= 'C';
			} else {
				$cbjt		= 'B';	
			}
			//echo substr($master->CB_CODE,0,3);die();
			$data = array(
				'TRAN_NO'				=> 'C'.$maxTransaction,
				'TRAN_DATE' 			=> date("Y-m-d", strtotime($master->TRAN_DATE)),
				'VOUCHER_NO' 			=> $master->VOUCHER_NO,
				'NTR' 					=> $ntrr,
				'CBJT' 					=> $cbjt,
				'CB_CODE' 				=> $master->CB_CODE,
				'CHQ_NO' 				=> $master->CHEQUE_NO,
				'CHQ_DATE' 				=> date("Y-m-d", strtotime($master->CHEQUE_DATE)),
				'EFFECTED_DATE' 		=> date("Y-m-d", strtotime($master->EFFECTED_DATE)),
				'RECONCILIATION_FLAG' 	=> $master->RECONCILIATION_FLAG,
				'RECORD_DATE' 			=> $recordDate,
				'DRAWN_ON' 				=> $master->DRAWN_ON,
				'AUTO_TRANSACTION_FLAG' => $master->AUTO_TRANSACTION_FLAG,
				'INVOICE_NO' 			=> $master->INVOICE_NO,
				'MONEY_RECEIPT_NO' 		=> $master->MONEY_RECEIPT_NO,
				'BACK_DATE' 			=> $master->BACK_DATE,
				'OPERATE_BY' 			=> $userId,
			);
			//echo "<pre>"; print_r($data);die();
			if($kk = $this->tableGateway->insert($data)) {
				return 'C'.$maxTransaction;
				// Get Max Id End By Akhand
			} else {
				return false;	
			}
		}
	}
?>