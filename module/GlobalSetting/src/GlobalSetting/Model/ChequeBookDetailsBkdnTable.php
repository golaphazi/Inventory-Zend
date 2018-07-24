<?php
	namespace GlobalSetting\Model;
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	
	use Zend\Session\Container as SessionContainer;
	
	class ChequeBookDetailsBkdnTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		
		public function fetchAll() {
			$resultSet = $this->tableGateway->select();
			return $resultSet;
		}
			
		public function getChequeBookDetailsBkdn($id) {
			$id = (int) $id;
			$rowSet = $this->tableGateway->select(array('CHEQUE_BOOK_DETAILS_BKDN_ID' => $id));
			$row = $rowSet->current();
			if(!$row) {
				throw new \Exception("Could not find row $id");
			}
			return $row;
		}
		
		public function chequeBookDetailsBkdnExist($existCheckData) {
			$rowSet = $this->tableGateway->select($existCheckData);
			$row = $rowSet->current();
			return $row;
		}
		
		public function saveChequeBookDetailsBkdn(ChequeBookDetailsBkdn $chequeBookDetailsBkdn) {
			$chequeRange			= explode('-', $chequeBookDetailsBkdn->CHEQUE_NO);
			$chequeRangeFrom		= $chequeRange[0];
			$chequeRangeTo			= $chequeRange[1];
			
			for($i=$chequeRangeFrom;$i<=$chequeRangeTo;$i++) {
				$chequeBookDetailsBkdnData = array(
					'CHEQUE_BOOK_DETAILS_ID' => $chequeBookDetailsBkdn->CHEQUE_BOOK_DETAILS_ID,
					'CHEQUE_NO' => $i,
				);
				if($this->tableGateway->insert($chequeBookDetailsBkdnData)){
					$status = 1;
				} else {
					$status = 0;
				}
			}
			if(!$status) {
				$this->tableGateway->adapter->getDriver()->getConnection()->rollback();
				return false;
			} else {
				$this->tableGateway->adapter->getDriver()->getConnection()->commit();
				return true;	
			}
		}
		public function deleteChequeBookDetails($id) {
			$this->tableGateway->delete(array('CHEQUE_BOOK_DETAILS_BKDN_ID' => $id));
		}
	}
?>	