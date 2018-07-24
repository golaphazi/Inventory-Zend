<?php
	namespace Ibcia\Model;
		
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Sql\Expression;
	
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	
	// Include Start By Akhand
	use Zend\Paginator\Adapter\DbSelect;
	use Zend\Paginator\Paginator;
	// Include End By Akhand
	
	class HolidayTable {
		
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		
		public function getHolidays($lastBusinessDate) {
			$selectHolidays 	= $this->tableGateway->getSql()->select();
			$selectHolidays->columns(array(
				'HOLIDAY_DATE' 	=> 'HOLIDAY_DATE',
				'JSHD' 			=> 'HOLIDAY_DATE',
			));
			//echo '<pre/>';
			//$t = new Expression("to_char(HOLIDAY_DATE,'yyyy') = to_char(".$lastBusinessDate." + 1, 'yyyy')");
			//print_r($t);
			//die();
			//$sql = new \Zend\Db\Sql\Sql();
    		//var_dump($sql->getSqlStringForSqlObject($selectHolidays));
			//die();

			//$selectHolidays->where(new Expression("to_char(HOLIDAY_DATE,'yyyy') = ?", "to_char(".$lastBusinessDate." + 1, 'yyyy')"));
			//$selectHolidays->where->addPredicate( new \Zend\Db\Sql\Predicate\Expression( "to_char(HOLIDAY_DATE,'yyyy') = ?", "to_char(".$lastBusinessDate." + 1, 'yyyy')" ) );
			//$selectHolidays->where(array( "HOLIDAY_DATE = '".$lastBusinessDate."'" )); //http://stackoverflow.com/questions/14190872/how-to-add-sql-expression-in-front-of-column-name-in-zf2
			$resultSet 	= $this->tableGateway->selectWith($selectHolidays);
			/*foreach($resultSet as $row){
					print_r($row->JSHD."\n");
			};
			die();*/
			return $resultSet;
		}
		
		public function holidayExist($existCheckData) {
			$rowSet 	= $this->tableGateway->select($existCheckData);
			$row 		= $rowSet->current();
			return $row;
		}
		
		public function saveHoliday(Holiday $holiday) {
			$data = array(
				'HOLIDAY_DATE' 			=> $holiday->HOLIDAY_DATE,
				'HOLIDAY_TYPE' 			=> $holiday->HOLIDAY_TYPE,
				'HOLIDAY_DESCRIPTION' 	=> $holiday->HOLIDAY_DESCRIPTION,
			);
				
			//echo "<pre>"; print_r($data); die();
			$existCheckData = array(
				'HOLIDAY_DATE' 	=> $holiday->HOLIDAY_DATE,
			);
			
			if($this->holidayExist($existCheckData)) {
				//echo '1';die();
				//return $this->tableGateway->update($data,array('HOLIDAY_DATE' => new Expression("to_date('$holiday->BUSINESS_DATE', 'dd-mm-yyyy')")));
			} else {
				if($this->tableGateway->insert($data)){
					return $status = 1;
				} else {
					return $status = 0;
				}
			}
		}
		
		public function sodUnprocessedHolidays($businessDate) {
			$businessDate	= date("Y-m-d", strtotime($businessDate));
			$sodUHSql = "SELECT HDD HD
						  FROM (SELECT HD.HOLIDAY_DATE HDD
								  FROM l_ls_holiday HD
								 WHERE HD.HOLIDAY_DATE BETWEEN
									   (SELECT max(BUSINESS_DATE)
										  FROM l_business_date BD
										 where lower(BD.SOD_FLAG) = 'y') AND
									   '$businessDate'
								UNION
								SELECT '$businessDate' HDD FROM DUAL) HDD
						 ORDER BY HDD ASC
			";
			$sodUHStmt = $this->tableGateway->getAdapter()->createStatement($sodUHSql);
			$sodUHStmt->prepare();
			$sodUHResult = $sodUHStmt->execute();
			
			$returnData = array();
			if ($sodUHResult instanceof ResultInterface && $sodUHResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($sodUHResult);
				
				foreach($resultSet as $resultSets) {
					$returnData[]	= $resultSets->HD;
				}
			}
			
			return $returnData;
		}
		
		// Holiay Insert Start By Akhand
		public function fetchAll($paginated=false, Select $select = null) {
			if($paginated) {
				$select				= new Select('l_ls_holiday');
				//$select->where(array('HOLIDAY_TYPE != ?' => 'Weekend'));
				$select->order('HOLIDAY_DATE ASC');
				$resultSetPrototype = new ResultSet();
				$resultSetPrototype->setArrayObjectPrototype(new Holiday());
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
		
		public function getHolidayDate($id) {
			$id 	= (int) $id;
			$rowSet = $this->tableGateway->select(array('HOLIDAY_ID' => $id));
			$row 	= $rowSet->current();
			if(!$row) {
				throw new \Exception("Could not find row $id");
			}
			return $row;
		}
		
		public function editHoliday(Holiday $holiday) {
			$data = array(
				'HOLIDAY_ID' 			=> $holiday->HOLIDAY_ID,
				'HOLIDAY_DATE' 			=> new Expression("to_date('$holiday->HOLIDAY_DATE', 'dd-mm-yyyy')"),
				'HOLIDAY_TYPE' 			=> $holiday->HOLIDAY_TYPE,
				'HOLIDAY_DESCRIPTION' 	=> $holiday->HOLIDAY_DESCRIPTION,
			);
				
			//echo "<pre>"; print_r($data); die();
			$existCheckData = array(
				'HOLIDAY_DATE' 	=> new Expression("to_date('$holiday->HOLIDAY_DATE', 'dd-mm-yyyy')"),
			);
			
			if($holiday->HOLIDAY_ID) {
				$existingHolidayId	= '';
				$holidayExist		= '';
				$holidayExist 		= $this->holidayExist($existCheckData);
				$existingHolidayId 	= $holidayExist->HOLIDAY_ID;
				if((!empty($holidayExist)) && ($holiday->HOLIDAY_ID!=$existingHolidayId)) {
					return false;
				} else {
					if($this->tableGateway->update($data,array('HOLIDAY_ID' => $holiday->HOLIDAY_ID))) {
						return true;
					} else {
						return false;	
					}
				}
			} else {
				throw new \Exception("ID $holiday->HOLIDAY_ID does not exist!");
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
		// Holiay Insert End By Akhand
	}
?>	