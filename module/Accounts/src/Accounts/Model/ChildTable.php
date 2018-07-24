<?php
	namespace Accounts\Model;
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	use Zend\Session\Container as SessionContainer;
	
	class ChildTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		public function fetchNarration($input,$cond) {
			$getTblDataSql   = "SELECT PC.NARRATION
								  FROM IS_PORTFOLIO_CHILD PC, IS_PORTFOLIO_MASTER PM
								 WHERE LOWER(PC.NARRATION) like '%".$input."%'
								   AND PC.TRAN_NO = PM.TRAN_NO
								   AND LOWER(PM.AUTO_TRANSACTION_FLAG) = 'n'
								 ORDER BY PC.TRAN_NO ASC";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		public function fetchGeneralAccountsNarration($input,$cond) {
			$getTblDataSql   = "SELECT TC.NARRATION
								  FROM a_transaction_child TC, a_transaction_master TM
								 WHERE LOWER(TC.NARRATION) like '%".$input."%'
								   AND TC.TRAN_NO = TM.TRAN_NO
								   AND LOWER(TM.AUTO_TRANSACTION_FLAG) = 'n'
								 ORDER BY TC.TRAN_NO ASC";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		
		public function saveChild(Child $child) {
			//return true;
			$this->session 	= new SessionContainer('post_supply');
			$businessDate 	= $this->session->businessdate;
			$businessDate 	= date("Y-m-d", strtotime($businessDate));
			$recordDate 	= $this->session->recdate;//date("Y-m-d H:i:s", strtotime($businessDate));
			$userId 		= $this->session->userid;
			
			$data = array(
				'TRAN_NO'		=> $child->TRAN_NO,
				'AC_CODE' 		=> $child->AC_CODE,
				'BRANCH_ID' 	=> $child->BRANCH_ID,
				'NTR' 			=> $child->NTR,
				'CBJT' 			=> $child->CBJT,
				'CB_CODE' 		=> $child->CB_CODE,
				'NARRATION' 	=> $child->NARRATION,
				'AMOUNT' 		=> $child->AMOUNT,
				'RECORD_DATE' 	=> $recordDate,
			);
			//echo "<pre>"; print_r($data);die();
			if($this->tableGateway->insert($data)) {
				return true;
			} else {
				return false;	
			}
		}
		
		public function saveChildContra(Child $child) {
			//return true;
			$this->session 	= new SessionContainer('post_supply');
			$businessDate 	= $this->session->businessdate;
			$businessDate 	= date("Y-m-d", strtotime($businessDate));
			$recordDate 	= $this->session->recdate;//date("Y-m-d H:i:s", strtotime($businessDate));
			$userId 		= $this->session->userid;
			
			$ntrr			= '';
			if($child->NTR	== 'D') {
				$ntrr		= 'C';
			} else {
				$ntrr		= 'D';	
			}
			
			$cbjt			= '';
			if(substr($child->CB_CODE,0,3) == '303') {
				$cbjt		= 'C';
			} else {
				$cbjt		= 'B';	
			}
			//echo substr($child->CB_CODE,0,3);die();
			
			$data = array(
				'TRAN_NO'		=> $child->TRAN_NO,
				'AC_CODE' 		=> $child->AC_CODE,
				'BRANCH_ID' 	=> $child->BRANCH_ID,
				'NTR' 			=> $ntrr,
				'CBJT' 			=> $cbjt,
				'CB_CODE' 		=> $child->CB_CODE,
				'NARRATION' 	=> $child->NARRATION,
				'AMOUNT' 		=> $child->AMOUNT,
				'RECORD_DATE' 	=> $recordDate,
			);
			//echo "<pre>"; print_r($data);die();
			if($this->tableGateway->insert($data)) {
				$getIpoAppSql = "
								update 
										a_transaction_master
								set 
										VOUCHER_NO = '".$child->v_temp_voucher_no."'
								where 
										VOUCHER_NO = '".$child->v_voucher_no_in_out."'";
								   
				$getIpoApp			= $this->tableGateway->getAdapter()->createStatement($getIpoAppSql);
				$getIpoApp->prepare();
				$getIpoAppResult 	= $getIpoApp->execute();
				//return true;
				if(strtoupper($child->v_temp_voucher_type) == 'CV'){
					$gVoucer 			= explode('-',$child->v_voucher_no_in_out);
					
					$conBranch 			= $gVoucer[0];
					$conYear 			= $gVoucer[1];
					$conVoucher 		= number_format($gVoucer[2]);
					$conBranch1 		= substr($conBranch,2);
					
					$getIpoAppSql = "delete from a_voucher
									   where a_voucher.CONTRA_VOUCHER =
											 (select ('".$conVoucher."')
												from dual)
										 and a_voucher.V_YEAR =
											 (select ('".$conYear."')
												from dual)
										 and a_voucher.BRANCH_ID =
											 (select ('".$conBranch1."') from dual)";
					$getIpoApp			= $this->tableGateway->getAdapter()->createStatement($getIpoAppSql);
					$getIpoApp->prepare();
					$getIpoAppResult 	= $getIpoApp->execute();
					return true;
				}
			} else {
				return false;	
			}
		}
	}
?>	