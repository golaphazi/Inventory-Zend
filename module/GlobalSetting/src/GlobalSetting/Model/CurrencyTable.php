<?php
	namespace GlobalSetting\Model;
	
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	use Zend\Paginator\Adapter\DbSelect;
	use Zend\Paginator\Paginator;
	
	class CurrencyTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		
		public function fetchAll($paginated=false, Select $select = null) {
			//echo 'hi therer';die();
			if($paginated) {
				$select	= new Select('gs_list_currency');
				$select->order('CURRENCY_NAME ASC');				
				$resultSetPrototype = new ResultSet();
				$resultSetPrototype->setArrayObjectPrototype(new Currency());
				
				// Create a new pagination adapter object
				$paginatorAdapter 	= new DbSelect($select,$this->tableGateway->getAdapter(),$resultSetPrototype);
				$paginator 			= new Paginator($paginatorAdapter);
				//echo "<pre>"; print_r($paginator); die();
				return $paginator;
			}
			
			/*if (null === $select)
			$select	= new Select();
			$select->from($this->table);
			$resultSet = $this->selectWith($select);
			$resultSet->buffer();
			return $resultSet;*/
		}
		
		public function getCurrency($id) {
			$id 	= (int) $id;
			$rowSet = $this->tableGateway->select(array('CURRENCY_ID' => $id));
			$row	= $rowSet->current();
			if(!$row) {
				throw new \Exception("Could not find row $id");
			}
			return $row;
		}
		
		public function currencyExist($existCheckData) {
			$rowSet = $this->tableGateway->select($existCheckData);
			$row 	= $rowSet->current();
			return $row;
		}
		public function getCurrencyForSelect() {
			$getTblDataSql   = "SELECT * FROM gs_list_currency ORDER BY CURRENCY_NAME ASC";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		public function saveCurrency(Currency $currency) {
			$this->session 	= new SessionContainer('post_supply');
			$businessdate 	= $this->session->businessdate;
			$recdate 		= $this->session->recdate;
			$userid 		= $this->session->userid;
			$data = array(
				'CURRENCY_NAME'	=> $currency->CURRENCY_NAME,
				'ACTIVE_STATUS'	=> 'Active',
				'CREATE_DATE' => $recdate,
				'LAST_UPDATE'	=> $recdate,
			);
			//echo "<pre>"; print_r($data); die();
			$existCheckData = array(
				'CURRENCY_NAME' => $currency->CURRENCY_NAME,
			);
			$id = (int) $currency->CURRENCY_ID;
			
			if($id == 0) {
				if($this->currencyExist($existCheckData)) {
					throw new \Exception("Currency ".$currency->CURRENCY_NAME." already exist!");
				} else {
					if($this->tableGateway->insert($data)) {
						return true;	
					} else {
						return false;
					}
				}
			} else {
				if($this->getCurrency($id)) {
					$existingCurrencyId	= '';
					$currencyExist		= '';
					$currencyExist 		= $this->currencyExist($existCheckData);
					$existingCurrencyId	=  $currencyExist->CURRENCY_ID;
					if((!empty($currencyExist)) && ($id!=$existingCurrencyId)) {
						throw new \Exception("Currency ".$currency->CURRENCY_NAME." already exist!");
					} else {
						if($this->tableGateway->update($data,array('CURRENCY_ID' => $id))) {
							return true;
						} else {
							return false;	
						}
					}
				} else {
					throw new \Exception("ID $id does not exist!");
				}
			}
		}
		
		public function deleteCurrency($id) {
			$this->tableGateway->delete(array('CURRENCY_ID' => $id));
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