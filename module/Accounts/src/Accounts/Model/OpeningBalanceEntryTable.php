<?php
	namespace Accounts\Model;
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	
	
	class OpeningBalanceEntryTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		/*public function fetchAll() {
			$resultSet = $this->tableGateway->select(function(Select $select){
						 	$select->join('LS_INSTRUMENT_DETAILS','LS_INSTRUMENT_DETAILS.INSTRUMENT_DETAILS_ID=IS_DIVIDEND_DETAILS.INSTRUMENT_DETAILS_ID','INSTRUMENT_NAME');
							$select->where("IS_DIVIDEND_DETAILS.STATUS = 'np'");
							
						 });
			return $resultSet;
		}
		
		public function getDividend($id) {
			$id = (int) $id;
			$rowSet = $this->tableGateway->select(array('DIVIDEND_DETAILS_ID' => $id));
			$row = $rowSet->current();
			if(!$row) {
				throw new \Exception("Could not find row $id");
			}
			return $row;
		}
		
		public function dividendExist($existCheckData) {
			$rowSet 	= $this->tableGateway->select($existCheckData);
			$row 		= $rowSet->current();
			return $row;
		}*/
		
		public function saveOpeningBalance($openingbalanceentry) {
			//echo 'hi therere';die();
			//echo "<pre>"; print_r($openingbalanceentry); die();
			$this->session 		= new SessionContainer('post_supply');
			$businessDate 		= $this->session->businessdate;
			$businessDate 		= date("Y-m-d", strtotime($businessDate));
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			
			$data = array(
					'BRANCH_ID' 		=> $openingbalanceentry->BRANCH_ID,
					'AC_CODE' 			=> $openingbalanceentry->coa_code,
					'BALANCE_DATE' 		=> date("Y-m-d", strtotime($openingbalanceentry->tranDateTo)),
					'SNTB' 				=> $openingbalanceentry->COAType,
					'SA_AMOUNT' 		=> str_replace(",", "", $openingbalanceentry->amount),
					'TBNTB' 			=> $openingbalanceentry->COAType,
					'TB_AMOUNT' 		=> str_replace(",", "", $openingbalanceentry->amount),
					'CBNTB' 			=> $openingbalanceentry->COAType,
					'CB_AMOUNT' 		=> str_replace(",", "", $openingbalanceentry->amount),
					'BUSINESS_DATE' 	=> $businessDate,
					'RECORD_DATE' 		=> $recDate,
					'OPERATE_BY' 		=> $userId,
				);
			//echo "<pre>"; print_r($data);die();
			if($this->tableGateway->insert($data)) {
				return true;	
			} else {
				return false;
			}
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