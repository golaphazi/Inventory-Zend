<?php
	namespace GlobalSetting\Model;
	
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	use Zend\Paginator\Adapter\DbSelect;
	use Zend\Paginator\Paginator;
	
	class ChequeBookDetailsTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		
		public function fetchAll($paginated=false, Select $select = null) {
			if($paginated) {
				$select	= new Select('gs_cheque_book_details');
				$select->join('gs_account_details','gs_cheque_book_details.ACCOUNT_DETAILS_ID=gs_account_details.ACCOUNT_DETAILS_ID',array ('ACCOUNT_NAME','ACCOUNT_NO'));
				$select->join('gs_account_type','gs_account_details.ACCOUNT_TYPE_ID=gs_account_type.ACCOUNT_TYPE_ID',array ('ACCOUNT_TYPE'));
				$select->join('gs_org_branch','gs_account_details.ORG_BRANCH_ID=gs_org_branch.ORG_BRANCH_ID',array ('BRANCH_NAME'));
				$select->join('gs_money_mkt_org','gs_org_branch.ORG_ID=gs_money_mkt_org.ORG_ID',array ('ORG_NAME'));
				$select->order('gs_money_mkt_org.ORG_NAME, gs_account_type.ACCOUNT_TYPE, gs_org_branch.BRANCH_NAME, gs_account_details.ACCOUNT_NAME ASC');
				
				// create a new result set based on the Investor Details entity
				$resultSetPrototype = new ResultSet();
				$resultSetPrototype->setArrayObjectPrototype(new ChequeBookDetails());
				// create a new pagination adapter object
				$paginatorAdapter 	= new DbSelect($select,$this->tableGateway->getAdapter(),$resultSetPrototype);
				$paginator 			= new Paginator($paginatorAdapter);
				return $paginator;
			}
			
			if (null === $select)
			$select	= new Select();
			$select->from($this->table);
			$resultSet = $this->selectWith($select);
			$resultSet->buffer();
			return $resultSet;
		}
		
		public function getChequeBookDetails($id) {
			$id = (int) $id;
			$rowSet = $this->tableGateway->select(array('CHEQUE_BOOK_DETAILS_ID' => $id));
			$row = $rowSet->current();
			if(!$row) {
				throw new \Exception("Could not find row $id");
			}
			return $row;
		}
		
		public function chequebookdetailsExist($existCheckData) {
			$rowSet = $this->tableGateway->select($existCheckData);
			$row = $rowSet->current();
			return $row;
		}
		
		public function saveChequeBookDetails(ChequeBookDetails $chequebookdetails) {
			$this->session 		= new SessionContainer('post_supply');
			$businessDate 		= $this->session->businessdate;
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			
			$data = array(
				'ACCOUNT_DETAILS_ID' 	=> $chequebookdetails->ACCOUNT_DETAILS_ID,
				'CHEQUE_NO_RANGE' 		=> $chequebookdetails->CHEQUE_NO_RANGE_FROM.'-'.$chequebookdetails->CHEQUE_NO_RANGE_TO,
				'BUSINESS_DATE' 		=> $businessDate,
				'RECORD_DATE' 			=> $recDate,
				'OPERATE_BY' 			=> $userId,
			);
						
			//echo '<pre>'; print_r($data);  die();
			
			$existCheckData = array(
				'ACCOUNT_DETAILS_ID' 	=> $chequebookdetails->ACCOUNT_DETAILS_ID,
				'CHEQUE_NO_RANGE' 		=> $chequebookdetails->CHEQUE_NO_RANGE_FROM.'-'.$chequebookdetails->CHEQUE_NO_RANGE_TO,
			);
			$id = (int) $chequebookdetails->CHEQUE_BOOK_DETAILS_ID;
			
			if($id == 0) {
				if($this->chequebookdetailsExist($existCheckData)) {
					throw new \Exception("Account ".$chequebookdetails->ACCOUNT_DETAILS_ID." already exist!");
				} else {					
					$this->tableGateway->adapter->getDriver()->getConnection()->beginTransaction();
					if($this->tableGateway->insert($data)) {
						$chequebookdetailsSql = $this->chequebookdetailsExist($existCheckData);
						$chequeBookDetailsId = (int) $chequebookdetailsSql->CHEQUE_BOOK_DETAILS_ID;						
						return $chequeBookDetailsId;
					}
				}
			} else {
				if($this->getChequeBookDetails($id)) {
					$existingChequeBookDetailsData = $this->chequebookdetailsExist($existCheckData);
					
					if(($existingChequeBookDetailsData->CHEQUE_BOOK_DETAILS_ID == $id) || !isset($existingChequeBookDetailsData->CHEQUE_BOOK_DETAILS_ID)) {
						if($this->tableGateway->update($data,array('CHEQUE_BOOK_DETAILS_ID' => $id))) {
							return $id;
						}
					} else {
						throw new \Exception("Cheque No Range ".$chequebookdetails->CHEQUE_NO_RANGE_FROM."-".$chequebookdetails->CHEQUE_NO_RANGE_TO." already exist!");
					}
				} else {
					throw new \Exception("ID $id does not exist!");
				}
			}
		}
		
		public function deleteChequeBookDetails($id) {
			$this->tableGateway->delete(array('CHEQUE_BOOK_DETAILS_ID' => $id));
		}
	}
?>	