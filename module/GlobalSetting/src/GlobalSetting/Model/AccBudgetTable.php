<?php
	namespace GlobalSetting\Model;
	
	use Zend\Db\TableGateway\TableGateway;
	use Zend\Db\Sql\Select;
	use Zend\Db\Adapter\Driver\ResultInterface;
	use Zend\Db\ResultSet\ResultSet;
	
	use Zend\Session\Container as SessionContainer;
	
	class AccBudgetTable {
		protected $tableGateway;
		
		public function __construct(TableGateway $tableGateway) {
			$this->tableGateway = $tableGateway;
		}
		public function fetchAll() {
			$resultSet = $this->tableGateway->select(function(Select $select){
						 	 $select->join('C_BRANCH','gs_acc_budget.BRANCH_ID=C_BRANCH.BRANCH_ID','BRANCH_NAME');
							
						 });
			return $resultSet;
		}
								
		public function budgetTreeView() {
			$this->session 		= new SessionContainer('post_supply');
			$businessDate 		= date('Y-m-d',strtotime($this->session->businessdate));
			$fiscalYear			= date('Y',strtotime($businessDate));
			$branchID 			= $this->session->branchid;
			if(empty($branchID)){
				$branchID = 1;
			}
			$fiscalYearID = '';
			$currentFiscalYear = '';
			$fiscalYearIDData = $this->fetchFYIDFromSession($fiscalYear);
			foreach($fiscalYearIDData as $fiscalYearDatas) {
				$fiscalYearID 			= $fiscalYearDatas->FISCAL_YEAR_ID;
				$currentFiscalYear 		= $fiscalYearDatas->FISCAL_YEAR;
			}
			$table_view				= '';
			$table_view .="<table width='100%' border='0' cellpadding='3' cellspacing='3' style='font-family:Tahoma, Geneva, sans-serif;font-size:100%; border:1px dotted #888;'>
								<tr>
									<td width='100%' align='right' colspan='3' style='font-weight:normal;border-bottom:1px dotted #888;'>
										<h5 style='margin:0px;padding:5px;'>
											<a onclick='if(confirm(\"Are you sure you want to print Chart of Account of Mutual Fund?\")){return true;} else {return false;};' href='/coaprint/coaprint' target='_blank'><img src='../img/print_icon.jpg' width='24' title='Print Chart of Account of Merchant Banking Operation' border='0' style='padding:0px 0px 5px 0px;' /></a>
										</h5>
									</td>
								</tr>
								<tr>
									<td width='100%' colspan='3' style='font-weight:normal;border-bottom:1px dotted #888;'>
										<h5 style='margin:0px;background:#0097cf;color:#fff;padding:5px;'>&nbsp;</h5>
									</td>
								</tr>
						  ";
				
			// Company Wise Chart of Account View Start By Akhand
			//$companyHead	= array('1','21');
			$companyHead	= array('21');	
			
			for ($i=0;$i<sizeof($companyHead);$i++) {
				$companyHeadValue			= $companyHead[$i];
				if($companyHeadValue == '21') {
					$companyHeadName		= 'IBCIA Inventory Accounts';
					$companyHeadCode		= 'IBCIA';
				}
				if($companyHeadValue == '1') {
					$companyHeadName		= 'IBCIA Inventory Accounts';
					$companyHeadCode		= 'IBCIA';
				}
				$company_wise_coa_view{$companyHeadValue}	= '';
				$company_wise_coa_view{$companyHeadValue} .="<table width='100%' border='0' cellpadding='3' cellspacing='3' style='font-family:Tahoma, Geneva, sans-serif; border:1px dotted #888;font-size:100%;'>";
				
				// Chart of Account View End By Akhand
				$COAMasterHead	= array('1','2','3','5','6');
				for ($i=0;$i<sizeof($COAMasterHead);$i++) {
					$COAMasterHeadValue			= $COAMasterHead[$i];
					if($COAMasterHeadValue == '1') {
						$COAMasterHeadName		= 'CAPITAL';
						$COAMasterHeadCode		= '100000000';
					}
					if($COAMasterHeadValue == '2') {
						$COAMasterHeadName		= 'LIABILITIES';
						$COAMasterHeadCode		= '200000000';
					}
					if($COAMasterHeadValue == '3') {
						$COAMasterHeadName		= 'ASSET';
						$COAMasterHeadCode		= '300000000';
					}
					if($COAMasterHeadValue == '5') {
						$COAMasterHeadName		= 'INCOME';
						$COAMasterHeadCode		= '500000000';
					}
					if($COAMasterHeadValue == '6') {
						$COAMasterHeadName		= 'EXPENSE';
						$COAMasterHeadCode		= '600000000';
					}
					
					$likeParameter 			= $COAMasterHeadValue;
					$parameterRange			= $likeParameter.'99';
					$viewTree 				= '';
					$COAMasterCodeValue		= array();
					$COASubMasterCodeValue	= array();
				
					$COAMasterCodeSql 		= "
												SELECT 
														DISTINCT SUBSTR(COA_CODE,1,3) AS SUBSTR_COA_CODE
												FROM 
														gs_coa
												WHERE 
														COA_CODE like '".$likeParameter."%'
												AND 	SUBSTR(COA_CODE, 1,3) <= ".$parameterRange."
												AND 	SUBSTR( COA_CODE, 2, 3 ) !=00
												ORDER BY 
													SUBSTR(COA_CODE,1,3) ASC 
											  ";
					$COAMasterCodeStatement = $this->tableGateway->getAdapter()->createStatement($COAMasterCodeSql);
					$COAMasterCodeStatement->prepare();
					$COAMasterCodeResult = $COAMasterCodeStatement->execute();
					
					if ($COAMasterCodeResult instanceof ResultInterface && $COAMasterCodeResult->isQueryResult()) {
						$resultSet = new ResultSet();
						$resultSet->initialize($COAMasterCodeResult);
					}
					foreach($resultSet as $resultSetValue) {
						$COAMasterCode 			= $resultSetValue->SUBSTR_COA_CODE;
						$COAMasterCodeValue[]	= $COAMasterCode."000";
					}
					
					for ($j=0;$j<sizeof($COAMasterCodeValue);$j++) {
						$input				= $COAMasterCodeValue[$j];
						$getCOASql			= "
												SELECT 
														COA_CODE, 
														COA_NAME,
														COA_ID
														 
												FROM 
														gs_coa
												WHERE
														COA_CODE like '".$input."%' 
												AND 	SUBSTR(COA_CODE,-3) = '000' 
												ORDER BY 
														COA_CODE ASC
											  ";
						$getCOAStatement 	= $this->tableGateway->getAdapter()->createStatement($getCOASql);
						$getCOAStatement->prepare();
						$getCOAResult 		= $getCOAStatement->execute();
						
						if ($getCOAResult instanceof ResultInterface && $getCOAResult->isQueryResult()) {
							$resultSetCOA = new ResultSet();
							$resultSetCOA->initialize($getCOAResult);
						}
						if($resultSetCOA) {
							$coa_code			= '';
							$coa_name			= '';
							$coa_id				= "";
							$budgetAmount 		= '';
							$budgetFSYear 		= '';
							foreach($resultSetCOA as $resultSetCOAVAlue) {
								$coa_code 	= $resultSetCOAVAlue->COA_CODE;
								$coa_name 	= $resultSetCOAVAlue->COA_NAME;
								$coa_id 	= $resultSetCOAVAlue->COA_ID;
								$budgetAmountData = $this->fetchBudgetAmount($coa_code,$fiscalYearID,$branchID);
								foreach($budgetAmountData as $bAData) {
									$budgetAmount = $bAData['BUDGET_AMOUNT'];
									$budgetFSYear = $bAData['FISCAL_YEAR'];
								}
								$viewSubTree= '';
								
								$mainHead 	= '';
								$mainpRange	= '';
								$firstHead	= '';
								$mainHead 	= substr($input, 0,3);
								$mainpRange	= $parameterRange."999";
								$firstHead	= $mainHead."000";
								
								$COASubMasterCodeSql 	= "
															SELECT 
																	DISTINCT SUBSTR(COA_CODE,1,6) AS SUBMST_COA_CODE
															FROM 
																	gs_coa
															WHERE 
																	COA_CODE like '".$mainHead."%'
															AND 	SUBSTR(COA_CODE, 1,6) <= ".$mainpRange."
															AND		SUBSTR(COA_CODE, 1,6) != ".$firstHead."
															ORDER BY 
																SUBSTR(COA_CODE,1,6) ASC 
														  ";
								$COASubMasterCodeStatement 	= $this->tableGateway->getAdapter()->createStatement($COASubMasterCodeSql);
								$COASubMasterCodeStatement->prepare();
								$COASubMasterCodeResult 	= $COASubMasterCodeStatement->execute();
								
								if ($COASubMasterCodeResult instanceof ResultInterface && $COASubMasterCodeResult->isQueryResult()) {
									$resultCOASubMaster = new ResultSet();
									$resultCOASubMaster->initialize($COASubMasterCodeResult);
								}
								
								foreach($resultCOASubMaster as $resultCOASubMasterVAlue) {
									$COASubMasterCode 			= $resultCOASubMasterVAlue->SUBMST_COA_CODE;
									$COASubMasterCodeValue[]	= $COASubMasterCode;
								}
								
								for ($k=0;$k<sizeof($COASubMasterCodeValue);$k++) {
									$subInput		= $COASubMasterCodeValue[$k];								
									$getSubCOASql	= "
														SELECT 
																COA_CODE, 
																COA_NAME,
																COA_ID 
														FROM 
																gs_coa 
														WHERE 	
																COA_CODE like '%".$subInput."%' 
														AND 	SUBSTR(COA_CODE,-3) = '000' 
														ORDER BY
																COA_CODE ASC
													  ";				
									$getSubCOAStatement 	= $this->tableGateway->getAdapter()->createStatement($getSubCOASql);
									$getSubCOAStatement->prepare();
									$getSubCOAResult 	= $getSubCOAStatement->execute();
									
									if ($getSubCOAResult instanceof ResultInterface && $getSubCOAResult->isQueryResult()) {
										$resultSubCOA = new ResultSet();
										$resultSubCOA->initialize($getSubCOAResult);
									}
									
									$coa_sub_code		= '';
									$coa_sub_name		= '';
									if($resultSubCOA) {
										foreach($resultSubCOA as $resultSubCOAValue) {
											$coa_sub_code 		= $resultSubCOAValue->COA_CODE;
											$coa_sub_name 		= $resultSubCOAValue->COA_NAME;
											$coa_sub_id 		= $resultSubCOAValue->COA_ID;
											$viewSubSubTree		= '';
											$getSubSubCOASql	= "
																	SELECT 
																			COA_CODE, 
																			COA_NAME,
																			COA_ID 
																	FROM 
																			gs_coa 
																	WHERE 
																			COA_CODE like '%".$subInput."%' 
																	AND 	SUBSTR(COA_CODE,-3) != '000' 
																	ORDER BY
																			COA_CODE ASC
																  ";	
											$getSubSubCOAStatement 	= $this->tableGateway->getAdapter()->createStatement($getSubSubCOASql);
											$getSubSubCOAStatement->prepare();
											$getSubSubCOAResult 	= $getSubSubCOAStatement->execute();
											
											if ($getSubSubCOAResult instanceof ResultInterface && $getSubSubCOAResult->isQueryResult()) {
												$resultSubSubCOA = new ResultSet();
												$resultSubSubCOA->initialize($getSubSubCOAResult);
											}
											$coa_subsub_code		= '';
											$coa_subsub_name		= '';
											$counter				= 1;
											if($resultSubSubCOA) {
												foreach($resultSubSubCOA as $resultSubSubCOAValue) {
													if($counter%2==0) {
														$class			= "even_row";
													} else {
														$class			= "odd_row";
													}
													$coa_subsub_code 	= $resultSubSubCOAValue->COA_CODE;
													$coa_subsub_name 	= $resultSubSubCOAValue->COA_NAME;
													$coa_subsub_id 		= $resultSubSubCOAValue->COA_ID;
													$budgetAmount 			= '';
													$budgetAmountData = $this->fetchBudgetAmount($coa_subsub_code,$fiscalYearID,$branchID);
													foreach($budgetAmountData as $bAData) {
														$budgetAmount = number_format($bAData['BUDGET_AMOUNT'],2);
														$budgetFSYear = $bAData['FISCAL_YEAR'];
													}
													$viewSubSubTree .="
																		<tr class='{$class}' style='cursor:pointer;'>
																			<td width='70%' style='font-weight:normal;border-bottom:1px dotted #888;'>	
																				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																					".$coa_subsub_name."
																			</td>
																			<td width='15%' align='center' style='font-weight:normal;border-bottom:1px dotted #888;'>	
																					".$coa_subsub_code."
																			</td>
																			<td width='15%' align='right' style='font-weight:normal;border-bottom:1px dotted #888;padding-right:10px;'>	
																				".$budgetAmount."
																			</td>
																		 </tr>
																	  ";
												$counter++;
												}
											}
											
											$viewSubTree .="<tr style='cursor:pointer' onmouseover='this.style.background=\"#ccffcc\"' onmouseout='this.style.background=\"#F7F4F4\"' onclick='ShowHide(\"ShowCOASubSubTree{$coa_sub_name}\");'>
																<td width='70%' style='font-weight:normal;border-bottom:1px dotted #888;'>															
																	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																	".$coa_sub_name."
																</td>
																<td width='15%' align='center' style='font-weight:normal;border-bottom:1px dotted #888;'>															
																	".$coa_sub_code."
																</td>
																<td width='15%' align='center' style='font-weight:normal;border-bottom:1px dotted #888;'>															
																	&nbsp;
																</td>
															 </tr>
															 <tr id='ShowCOASubSubTree{$coa_sub_name}'>
																<td colspan='3'>
																	<table width='100%' border='0' cellpadding='3' cellspacing='3' style='font-size:100%;'>
																		{$viewSubSubTree}
																	</table>
																</td>
															 </tr>
														  ";
										}
									}
								}
								$COASubMasterCodeValue	= array();
							}
						}
						if($COAMasterHeadValue)						
						$viewTree .= "
										<tr style='cursor:pointer' onmouseover='this.style.background=\"#ccffcc\"' onmouseout='this.style.background=\"#F7F4F4\"' onclick='ShowHide(\"ShowCOASubTree{$coa_name}\");'>
											<td width='70%' style='font-weight:normal;border-bottom:1px dotted #888;'>
												&nbsp;&nbsp;&nbsp;&nbsp;".$coa_name."
											</td>
											<td width='15%' align='center' style='font-weight:normal;border-bottom:1px dotted #888;'>
												".$coa_code."
											</td>
											<td width='15%' align='center' style='font-weight:normal;border-bottom:1px dotted #888;'>
												&nbsp;						
											</td>
										</tr>
										<tr id='ShowCOASubTree{$coa_name}'>
											<td colspan='3'>
												<table width='100%' border='0' cellpadding='3' cellspacing='3' style='font-size:100%;'>
													{$viewSubTree}
												</table>
											</td>
										 </tr>
									";
						// First Head View End by Akhand
					}
					$COAMasterCodeValue		= array();
					
					$company_wise_coa_view{$companyHeadValue} .= "
									<tr style='cursor:pointer;' onclick='ShowHide(\"ShowCOATree{$COAMasterHeadName}\");'>
										<td width='70%' style='font-weight:normal;border-bottom:1px dotted #888;'>
											".strtoupper($COAMasterHeadName)."
										</td>
										<td width='15%' align='center' style='font-weight:normal;border-bottom:1px dotted #888;'>
											".strtoupper($COAMasterHeadCode)."
										</td>
										<td width='15%' align='right' style='font-weight:normal;border-bottom:1px dotted #888;'>
											&nbsp;
										</td>
									</tr>
									<tr id='ShowCOATree{$COAMasterHeadName}'>
										<td colspan='3'>
											<table width='100%' border='0' cellpadding='3' cellspacing='3' style='font-size:100%;'>
												{$viewTree}
											</table>
										</td>
									 </tr>
								 ";
				}
				// Chart of Account View End By Akhand
				
				$company_wise_coa_view{$companyHeadValue} .="</table>";
				
				$table_view .= "<tr style='cursor:pointer;'>
									<td width='70%' style='font-weight:normal;border-bottom:1px dotted #888;'>FISCAL YEAR : ".$currentFiscalYear."</td>
									<td width='15%' align='center' style='font-weight:normal;border-bottom:1px dotted #888;'>ACC CODE</td>
									<td width='15%' align='right' style='font-weight:normal;border-bottom:1px dotted #888;'>Amount (BDT)</td>
								</tr>
								<tr id='ShowCompanyWiseCOATree{$companyHeadValue}'>
									<td colspan='3'>
										<table width='100%' border='0' cellpadding='0' cellspacing='0' style='font-size:100%;'>
											".$company_wise_coa_view{$companyHeadValue}."
										</table>
									</td>
								 </tr>
							 ";										  
			}
			// Company Wise Chart of Account View End By Akhand
			$table_view .="<tr>
								<td width='100%' colspan='3' style='font-weight:normal;border-bottom:1px dotted #888;'>
									<h5 style='margin:0px;padding:5px;'>&nbsp;</h5>
								</td>
							</tr>
						  </table>
						  ";
			
			return $table_view;
		}
		
		public function coaPrintTreeView() {
			$table_view				= '';
			$table_view .="<table width='100%' border='0' cellpadding='0' cellspacing='0' style='font-family:Tahoma, Geneva, sans-serif;font-size:85%; border:1px dotted #888;'>";
				
			// Company Wise Chart of Account View Start By Akhand
			//$companyHead	= array('1','21');
			$companyHead	= array('21');	
			
			for ($i=0;$i<sizeof($companyHead);$i++) {
				$companyHeadValue			= $companyHead[$i];
				if($companyHeadValue == '21') {
					$companyHeadName		= 'NSA Needysoft Accounts';
					$companyHeadCode		= 'NSA';
				}
				if($companyHeadValue == '1') {
					$companyHeadName		= 'NSA Needysoft Accounts';
					$companyHeadCode		= 'NSA';
				}
				$company_wise_coa_view{$companyHeadValue}	= '';
				$company_wise_coa_view{$companyHeadValue} .="<table width='100%' border='0' cellpadding='0' cellspacing='0' style='font-family:Tahoma, Geneva, sans-serif; border:1px dotted #888;'>";
				
				// Chart of Account View End By Akhand
				$COAMasterHead	= array('1','2','3','5','6');
				for ($i=0;$i<sizeof($COAMasterHead);$i++) {
					$COAMasterHeadValue			= $COAMasterHead[$i];
					if($COAMasterHeadValue == '1') {
						$COAMasterHeadName		= 'CAPITAL';
						$COAMasterHeadCode		= '100000000';
					}
					if($COAMasterHeadValue == '2') {
						$COAMasterHeadName		= 'LIABILITIES';
						$COAMasterHeadCode		= '200000000';
					}
					if($COAMasterHeadValue == '3') {
						$COAMasterHeadName		= 'ASSET';
						$COAMasterHeadCode		= '300000000';
					}
					if($COAMasterHeadValue == '5') {
						$COAMasterHeadName		= 'INCOME';
						$COAMasterHeadCode		= '500000000';
					}
					if($COAMasterHeadValue == '6') {
						$COAMasterHeadName		= 'EXPENSE';
						$COAMasterHeadCode		= '600000000';
					}
					
					$likeParameter 			= $COAMasterHeadValue;
					$parameterRange			= $likeParameter.'99';
					$viewTree 				= '';
					$COAMasterCodeValue		= array();
					$COASubMasterCodeValue	= array();
				
					$COAMasterCodeSql 		= "
												SELECT 
														DISTINCT SUBSTR(COA_CODE,1,3) AS SUBSTR_COA_CODE
												FROM 
														gs_coa
												WHERE 
														COA_CODE like '".$likeParameter."%'
												AND 	SUBSTR(COA_CODE, 1,3) <= ".$parameterRange."
												ORDER BY 
													SUBSTR(COA_CODE,1,3) ASC 
											  ";
					$COAMasterCodeStatement = $this->tableGateway->getAdapter()->createStatement($COAMasterCodeSql);
					$COAMasterCodeStatement->prepare();
					$COAMasterCodeResult = $COAMasterCodeStatement->execute();
					
					if ($COAMasterCodeResult instanceof ResultInterface && $COAMasterCodeResult->isQueryResult()) {
						$resultSet = new ResultSet();
						$resultSet->initialize($COAMasterCodeResult);
					}
					foreach($resultSet as $resultSetValue) {
						$COAMasterCode 			= $resultSetValue->SUBSTR_COA_CODE;
						$COAMasterCodeValue[]	= $COAMasterCode."000";
					}
					
					for ($j=0;$j<sizeof($COAMasterCodeValue);$j++) {
						$input				= $COAMasterCodeValue[$j];
						$getCOASql			= "
												SELECT 
														COA_CODE, 
														COA_NAME 
												FROM 
														gs_coa 
												WHERE
														COA_CODE like '".$input."%' 
												AND 	SUBSTR(COA_CODE,-3) = '000' 
												ORDER BY 
														COA_CODE ASC
											  ";
						$getCOAStatement 	= $this->tableGateway->getAdapter()->createStatement($getCOASql);
						$getCOAStatement->prepare();
						$getCOAResult 		= $getCOAStatement->execute();
						
						if ($getCOAResult instanceof ResultInterface && $getCOAResult->isQueryResult()) {
							$resultSetCOA = new ResultSet();
							$resultSetCOA->initialize($getCOAResult);
						}
						if($resultSetCOA) {
							$coa_code			= '';
							$coa_name			= '';
							foreach($resultSetCOA as $resultSetCOAVAlue) {
								$coa_code 	= $resultSetCOAVAlue->COA_CODE;
								$coa_name 	= $resultSetCOAVAlue->COA_NAME;
								$viewSubTree= '';
								
								$mainHead 	= '';
								$mainpRange	= '';
								$firstHead	= '';
								$mainHead 	= substr($input, 0,3);
								$mainpRange	= $parameterRange."999";
								$firstHead	= $mainHead."000";
								
								$COASubMasterCodeSql 	= "
															SELECT 
																	DISTINCT SUBSTR(COA_CODE,1,6) AS SUBMST_COA_CODE
															FROM 
																	gs_coa
															WHERE 
																	COA_CODE like '".$mainHead."%'
															AND 	SUBSTR(COA_CODE, 1,6) <= ".$mainpRange."
															AND		SUBSTR(COA_CODE, 1,6) != ".$firstHead."
															ORDER BY 
																SUBSTR(COA_CODE,1,6) ASC 
														  ";
								$COASubMasterCodeStatement 	= $this->tableGateway->getAdapter()->createStatement($COASubMasterCodeSql);
								$COASubMasterCodeStatement->prepare();
								$COASubMasterCodeResult 	= $COASubMasterCodeStatement->execute();
								
								if ($COASubMasterCodeResult instanceof ResultInterface && $COASubMasterCodeResult->isQueryResult()) {
									$resultCOASubMaster = new ResultSet();
									$resultCOASubMaster->initialize($COASubMasterCodeResult);
								}
								
								foreach($resultCOASubMaster as $resultCOASubMasterVAlue) {
									$COASubMasterCode 			= $resultCOASubMasterVAlue->SUBMST_COA_CODE;
									$COASubMasterCodeValue[]	= $COASubMasterCode;
								}
								
								for ($k=0;$k<sizeof($COASubMasterCodeValue);$k++) {
									$subInput		= $COASubMasterCodeValue[$k];								
									$getSubCOASql	= "
														SELECT 
																COA_CODE, 
																COA_NAME,
																COA_ID 
														FROM 
																gs_coa 
														WHERE 	
																COA_CODE like '%".$subInput."%' 
														AND 	SUBSTR(COA_CODE,-3) = '000' 
														ORDER BY
																COA_CODE ASC
													  ";				
									$getSubCOAStatement 	= $this->tableGateway->getAdapter()->createStatement($getSubCOASql);
									$getSubCOAStatement->prepare();
									$getSubCOAResult 	= $getSubCOAStatement->execute();
									
									if ($getSubCOAResult instanceof ResultInterface && $getSubCOAResult->isQueryResult()) {
										$resultSubCOA = new ResultSet();
										$resultSubCOA->initialize($getSubCOAResult);
									}
									
									$coa_sub_code		= '';
									$coa_sub_name		= '';
									if($resultSubCOA) {
										foreach($resultSubCOA as $resultSubCOAValue) {
											$coa_sub_code 		= $resultSubCOAValue->COA_CODE;
											$coa_sub_name 		= $resultSubCOAValue->COA_NAME;
											$coa_sub_id 		= $resultSubCOAValue->COA_ID;
											$viewSubSubTree		= '';
											$getSubSubCOASql	= "
																	SELECT 
																			COA_CODE, 
																			COA_NAME,
																			COA_ID 
																	FROM 
																			gs_coa 
																	WHERE 
																			COA_CODE like '%".$subInput."%' 
																	AND 	SUBSTR(COA_CODE,-3) != '000' 
																	ORDER BY
																			COA_CODE ASC
																  ";	
											$getSubSubCOAStatement 	= $this->tableGateway->getAdapter()->createStatement($getSubSubCOASql);
											$getSubSubCOAStatement->prepare();
											$getSubSubCOAResult 	= $getSubSubCOAStatement->execute();
											
											if ($getSubSubCOAResult instanceof ResultInterface && $getSubSubCOAResult->isQueryResult()) {
												$resultSubSubCOA = new ResultSet();
												$resultSubSubCOA->initialize($getSubSubCOAResult);
											}
											$coa_subsub_code		= '';
											$coa_subsub_name		= '';
											$counter				= 1;
											if($resultSubSubCOA) {
												foreach($resultSubSubCOA as $resultSubSubCOAValue) {
													if($counter%2==0) {
														$class			= "even_row";
													} else {
														$class			= "odd_row";
													}
													$coa_subsub_code 	= $resultSubSubCOAValue->COA_CODE;
													$coa_subsub_name 	= $resultSubSubCOAValue->COA_NAME;
													$coa_subsub_id 		= $resultSubSubCOAValue->COA_ID;
			
													$viewSubSubTree .="
																		<tr style='cursor:pointer;'>
																			<td width='100%' style='font-weight:normal;border-bottom:1px dotted #888;'>															
																				<h5 style='margin:0px;padding:5px;'>																	
																				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																					".$coa_subsub_name."
																				</h5>
																			</td>
																			<td width='100%' style='font-weight:normal;border-bottom:1px dotted #888;'>															
																				<h5 style='margin:0px;padding:5px;'>
																					".$coa_subsub_code."<a onclick='if(confirm(\"Are you sure you want to delete General chart of accounts?\")){return true;} else {return false;};' style='display:none' href='/coa/delete/{$coa_subsub_id}' id='{$coa_subsub_id}'>D</a>
																				</h5>
																			</td>
																		 </tr>
																	  ";
												$counter++;
												}
											}
											
											$viewSubTree .="<tr style='cursor:pointer' onclick='ShowHide(\"ShowCOASubSubTree{$coa_sub_name}\");'>
																<td width='100%' style='font-weight:normal;border-bottom:1px dotted #888;'>															
																	<h5 style='margin:0px;padding:5px;'>
																	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																		".$coa_sub_name."
																	</h5>
																</td>
																<td width='100%' style='font-weight:normal;border-bottom:1px dotted #888;'>															
																	<h5 style='margin:0px;padding:5px;'>
																		".$coa_sub_code."<a onclick='if(confirm(\"Are you sure you want to delete General chart of accounts?\")){return true;} else {return false;};' style='display:none' href='/coa/delete/{$coa_sub_id}' id='{$coa_sub_id}'>D</a>
																	</h5>
																</td>
															 </tr>
															 <tr id='ShowCOASubSubTree{$coa_sub_name}'>
																<td colspan='2'>
																	<table width='100%' border='0' cellpadding='0' cellspacing='0'>
																		{$viewSubSubTree}
																	</table>
																</td>
															 </tr>
														  ";
										}
									}
								}
								$COASubMasterCodeValue	= array();
							}
						}
						
						// First Head View Start by Akhand
						if($COAMasterHeadValue)
						
						$viewTree .= "
										<tr style='cursor:pointer' onclick='ShowHide(\"ShowCOASubTree{$coa_name}\");'>
											<td width='100%' style='font-weight:normal;border-bottom:1px dotted #888;'>
												<h5 style='margin:0px;padding:5px;'>
												&nbsp;&nbsp;&nbsp;&nbsp;".$coa_name."
												</h5>								
											</td>
											<td width='100%' style='font-weight:normal;border-bottom:1px dotted #888;'>
												<h5 style='margin:0px;padding:5px;'>
													".$coa_code."
												</h5>								
											</td>
										</tr>
										<tr id='ShowCOASubTree{$coa_name}'>
											<td colspan='2'>
												<table width='100%' border='0' cellpadding='0' cellspacing='0'>
													{$viewSubTree}
												</table>
											</td>
										 </tr>
									";
						// First Head View End by Akhand
					}
					$COAMasterCodeValue		= array();
					
					$company_wise_coa_view{$companyHeadValue} .= "<tr style='cursor:pointer' onclick='ShowHide(\"ShowCOATree{$COAMasterHeadName}\");'>
										<td width='100%' style='font-weight:normal;border-bottom:1px dotted #888;'>
											<h5 style='margin:0px;padding:5px;cursor:pointer;' >
												".strtoupper($COAMasterHeadName)."
											</h5>
										</td>
										<td width='100%' style='font-weight:normal;border-bottom:1px dotted #888;'>
											<h5 style='margin:0px;padding:5px;cursor:pointer;' >
												".strtoupper($COAMasterHeadCode)."
											</h5>
										</td>
									</tr>
									<tr id='ShowCOATree{$COAMasterHeadName}'>
										<td colspan='2'>
											<table width='100%' border='0' cellpadding='0' cellspacing='0'>
												{$viewTree}
											</table>
										</td>
									 </tr>
								 ";
				}
				// Chart of Account View End By Akhand
				
				$company_wise_coa_view{$companyHeadValue} .="</table>";
				
				$table_view .= "<tr style='cursor:pointer' onclick='ShowHide(\"ShowCompanyWiseCOATree{$companyHeadValue}\");'>
									<td width='65%' style='font-weight:normal;border-bottom:1px dotted #888;'>
										<h5 style='margin:0px;padding:5px;cursor:pointer;' >
											".strtoupper($companyHeadName)."
										</h5>
									</td>
									<td width='35%' align='right' style='font-weight:normal;border-bottom:1px dotted #888;'>
										<h5 style='margin:0px;padding:5px;cursor:pointer;' >
											".strtoupper($companyHeadCode)."
										</h5>
									</td>
								</tr>
								<tr id='ShowCompanyWiseCOATree{$companyHeadValue}'>
									<td colspan='2'>
										<table width='100%' border='0' cellpadding='0' cellspacing='0'>
											".$company_wise_coa_view{$companyHeadValue}."
										</table>
									</td>
								 </tr>
							 ";										  
			}
			// Company Wise Chart of Account View End By Akhand
			$table_view .="<tr>
								<td width='100%' colspan='2' style='font-weight:normal;border-bottom:1px dotted #888;'>
									<h5 style='margin:0px;padding:5px;'>&nbsp;</h5>
								</td>
							</tr>
						  </table>
						  ";
			
			return $table_view;
		}
		
		public function getSpecificCOACode($COA_ID) {
			$selectChartOfAccount = "		
									SELECT 
											C.COA_ID								AS COA_ID,
											C.COMPANY_ID							AS COMPANY_ID, 
											C.COA_NAME 								AS COA_NAME,
											C.COA_CODE								AS COA_CODE,
											C.MOTHER_ACCOUNT						AS MOTHER_ACCOUNT,
											C.CASH_FLOW_HEAD						AS CASH_FLOW_HEAD,
											C.LFT									AS LFT,
											C.RGT									AS RGT,
											(SELECT 
												MAX(GS_COA.COA_ID)	
											FROM
												gs_coa GS_COA
											WHERE
												C.LFT > GS_COA.LFT
											AND C.RGT < GS_COA.RGT
											AND GS_COA.LFT+1 < GS_COA.RGT
											AND GS_COA.LFT > 1  
											)										AS MOTHER_COA_ID,
											(SELECT 
												GSCOA.COA_ID	
											FROM
												gs_coa GSCOA
											WHERE
												GSCOA.LFT=1  
											) 										AS FIRST_COA_ID 
									FROM 
											gs_coa C
									WHERE 
											C.COA_ID	= ".$COA_ID."
			";
			$chartOfAccountStatement	= $this->tableGateway->getAdapter()->createStatement($selectChartOfAccount);
			$chartOfAccountStatement->prepare();
			$chartOfAccountResult		= $chartOfAccountStatement->execute();
			
			if ($chartOfAccountResult instanceof ResultInterface && $chartOfAccountResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($chartOfAccountResult);
			}
			
			$MOTHER_COA_ID	= '';
			foreach($resultSet as $resultSetData) {
				$MOTHER_COA_ID	= $resultSetData->MOTHER_COA_ID;
				/*$DATA_ARRAY	= array(
					'COA_ID'			=> $resultSetData->COA_ID,
					'COMPANY_ID'		=> $resultSetData->COMPANY_ID,
					'COA_NAME'			=> $resultSetData->COA_NAME,
					'COA_CODE'			=> $resultSetData->COA_CODE,
					'MOTHER_ACCOUNT'	=> $resultSetData->MOTHER_ACCOUNT,
					'CASH_FLOW_HEAD'	=> $resultSetData->CASH_FLOW_HEAD,
					'LFT'				=> $resultSetData->LFT,
					'RGT'				=> $resultSetData->RGT,
					'MOTHER_COA_ID'		=> $resultSetData->MOTHER_COA_ID,
					'FIRST_COA_ID'		=> $resultSetData->FIRST_COA_ID,	
				);*/
			}
			//echo "<pre>"; print_r($COA_DATA_ARRAY); die(); 
			return $MOTHER_COA_ID;
		}
		public function getCoaNameAndCode($id) {
			$selectChartOfAccount = "
									SELECT 
											C.COA_CODE		AS COA_CODE,
											C.COA_NAME      AS COA_NAME
									FROM 
											gs_coa 		C
									WHERE                
											C.COA_ID  = '".$id."'
			";
			$chartOfAccountStatement	= $this->tableGateway->getAdapter()->createStatement($selectChartOfAccount);
			$chartOfAccountStatement->prepare();
			$chartOfAccountResult		= $chartOfAccountStatement->execute();
			
			if ($chartOfAccountResult instanceof ResultInterface && $chartOfAccountResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($chartOfAccountResult);
			}
			
			/*$selectData = array();
			foreach ($cOAData as $selectOption) {
				$selectData[$selectOption['COA_ID']] = $selectOption['COA_NAME_DOT'].$selectOption['COA_NAME'];
			}*/
			return $resultSet;
		}
		
		public function getCoa($id) {
			$id = (int) $id;
			$rowSet = $this->tableGateway->select(array('COA_ID' => $id));
			$row = $rowSet->current();
			if(!$row) {
				throw new \Exception("Could not find row $id");
			}
			return $row;
		}
		
		public function getCOAForSelect() {
			$selectChartOfAccount = "
									SELECT 
											COA_DETAILS.COA_ID			AS COA_ID,
											COA_DETAILS.COA_NAME        AS COA_NAME,
											COA_DETAILS.COA_NAME_DOT    AS COA_NAME_DOT,
											COA_DETAILS.LFT             AS LFT,   
											COA_DETAILS.NODE_DEPTH      AS NODE_DEPTH
									FROM 
									(
										SELECT 
												C.COA_ID								AS COA_ID,
												rpad(' ', COUNT(C.COA_NAME) * 5, '-') 	AS COA_NAME_DOT,
												C.COA_NAME 								AS COA_NAME,
												COUNT(C.COA_NAME) 						AS NODE_DEPTH,
												C.LFT									AS LFT
										FROM 
												gs_coa 		C,
												gs_coa 		P,
												c_company 	CN
										WHERE 
												C.LFT BETWEEN P.LFT AND P.RGT
										AND 	C.COMPANY_ID 	= CN.COMPANY_ID
										GROUP BY 
												C.COA_ID,
												C.COA_NAME,
												C.COMPANY_ID,
												C.LFT
										ORDER BY 
												C.COMPANY_ID,
												C.LFT 
									) 									COA_DETAILS
									WHERE                
											COA_DETAILS.NODE_DEPTH  < 6                                                             
									ORDER BY
											COA_DETAILS.LFT
			";
			$chartOfAccountStatement	= $this->tableGateway->getAdapter()->createStatement($selectChartOfAccount);
			$chartOfAccountStatement->prepare();
			$chartOfAccountResult		= $chartOfAccountStatement->execute();
			
			if ($chartOfAccountResult instanceof ResultInterface && $chartOfAccountResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($chartOfAccountResult);
			}
			
			/*$selectData = array();
			foreach ($cOAData as $selectOption) {
				$selectData[$selectOption['COA_ID']] = $selectOption['COA_NAME_DOT'].$selectOption['COA_NAME'];
			}*/
			return $resultSet;
		}
		
		public function budgetExist($existCheckData) {
			$rowSet = $this->tableGateway->select($existCheckData);
			$row = $rowSet->current();
			return $row;
		}
		
		public function saveBudget(AccBudget $accbudget) {
			$this->session 		= new SessionContainer('post_supply');
			$businessDate 		= $this->session->businessdate;
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			$msg = '';
			if($accbudget->PARENT_COA > 0) {
				$coaDetails = $this->getCoaNameAndCode($accbudget->PARENT_COA);
				foreach($coaDetails as $row) {
					$coaCode 	= $row['COA_CODE'];
					$coaName	= $row['COA_NAME'];	
				}
			}
			$fiscalYearData = $this->fetchFSYYear($accbudget->FISCAL_YEAR);
			$fiscalYear = '';
			foreach($fiscalYearData as $row) {
				$fiscalYear = $row['FISCAL_YEAR'];
			}
			$childData = array(
				'BRANCH_ID' 		=> $accbudget->BRANCH_ID,
				'BUDGET_ACC_NAME' 	=> $coaName,
				'BUDGET_ACC_CODE' 	=> $coaCode,
				'FISCAL_YEAR' 		=> $accbudget->FISCAL_YEAR,
				'BUDGET_AMOUNT' 	=> $accbudget->BUDGET_AMOUNT,
				'BUSINESS_DATE' 	=> date ('Y-m-d', strtotime($businessDate)),
				'RECORD_DATE' 		=> $recDate,
				'OPERATE_BY' 		=> $userId,
			);
			//echo "<pre>"; print_r($childData); die();
			$existCheckData = array(
				'BRANCH_ID' 	=> $accbudget->BRANCH_ID,
				'BUDGET_ACC_CODE'  => $coaCode,
				'FISCAL_YEAR' => $accbudget->FISCAL_YEAR,
			);
			$id = (int) $accbudget->BUDGET_ID;
			if($id == 0) {
				if($this->budgetExist($existCheckData)) {
					//throw new \Exception("Budget Head ".$coaName." for the fiscal year ".$accbudget->FISCAL_YEAR." already exist!");
					$msg = "Budget Head ".$coaName." for the fiscal year ".$fiscalYear." already exist!";
					return $msg;
				} else {
					//$this->tableGateway->adapter->getDriver()->getConnection()->beginTransaction();
					if($this->tableGateway->insert($childData)) {
						//$this->tableGateway->adapter->getDriver()->getConnection()->commit();
						$msg = "Success";
						return $msg;
						//return true;	
					} else {
						//$this->tableGateway->adapter->getDriver()->getConnection()->rollback();
						//throw new \Exception("Error: during budget insert!");
						$msg = "Failed";
						return $msg;
						//return false;
					}
				}
			} else {
				if($this->getcoa($id)) {
					if($eData 	= $this->budgetExist($existCheckData)) {
						$eId 	= $eData->BUDGET_ID;
						if($eId == $id){
							$childData1 = array(
									'BUDGET_ACC_NAME'=> $coaName,
							);
							if($this->tableGateway->update($childData1,array('BUDGET_ID' => $eId))) {
								return true;	
							} else {
								return false;
							}
						}
					} else {
						
					}
				} else {
					throw new \Exception("ID $id does not exist!");
				}
			}
		}
		
		public function getCOACode($companyId,$COAId) {
			$select = "		
						
						SELECT 
								  	COUNT(C.COA_NAME) AS NODE_DEPTH,
									C.COA_CODE
						  FROM 
								  	gs_coa C, 
								  	gs_coa P
						  WHERE 
								  	C.LFT BETWEEN P.LFT AND P.RGT
									AND   C.COA_ID = ".$COAId."
						  GROUP BY 
								  	C.COA_CODE
						  ORDER BY 
								  	C.LFT
			";
			$stmt = $this->tableGateway->getAdapter()->createStatement($select);
			$stmt->prepare();
			$result = $stmt->execute();
			
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			
			foreach($resultSet as $resultMaxRcvCOA) {
				$nodeDepth		= $resultMaxRcvCOA->NODE_DEPTH;
				$coaCode		= $resultMaxRcvCOA->COA_CODE;
			}
			//echo $nodeDepth;die();
			$maxCode = 0;
			if($nodeDepth == '2'){
				$coaC 		= substr($coaCode,0,3);
				$coaS 		= substr($coaCode,0,1);
				$coaSf		= $coaS."00";
				$coaSt		= $coaS."99";
				$selectMaxRcvCOA = "SELECT 
											COALESCE(MAX(substr(COA_CODE,1,3)),$coaC)+1  AS MCOA_CODE
										FROM
												gs_coa 		
										WHERE
											  substr(COA_CODE,1,3) BETWEEN $coaSf AND $coaSt";
				$selectMaxRcvCOAStatement 		= $this->tableGateway->getAdapter()->createStatement($selectMaxRcvCOA);
				$selectMaxRcvCOAStatement->prepare();
				$selectMaxRcvCOAResult 	= $selectMaxRcvCOAStatement->execute();
				
				if ($selectMaxRcvCOAResult instanceof ResultInterface && $selectMaxRcvCOAResult->isQueryResult()) {
					$resultSet = new ResultSet();
					$resultSet->initialize($selectMaxRcvCOAResult);
				}
				
				foreach($resultSet as $resultMaxRcvCOA) {
					$maxCOACode		= $resultMaxRcvCOA->MCOA_CODE;
					$maxCode		= $maxCOACode."000000";
				}
				//echo $maxCode;die(); 
			} else if($nodeDepth == '3'){
				$coaC 			= substr($coaCode,0,6);
				$coaSt			= $coaC + 999;
				$selectMaxRcvCOA = "SELECT 
											COALESCE(MAX(substr(COA_CODE,1,6)),$coaC)+1  AS MCOA_CODE
										FROM
												gs_coa 		
										WHERE
											  substr(COA_CODE,1,6) BETWEEN $coaC AND $coaSt";
				$selectMaxRcvCOAStatement 		= $this->tableGateway->getAdapter()->createStatement($selectMaxRcvCOA);
				$selectMaxRcvCOAStatement->prepare();
				$selectMaxRcvCOAResult 	= $selectMaxRcvCOAStatement->execute();
				
				if ($selectMaxRcvCOAResult instanceof ResultInterface && $selectMaxRcvCOAResult->isQueryResult()) {
					$resultSet = new ResultSet();
					$resultSet->initialize($selectMaxRcvCOAResult);
				}
				
				foreach($resultSet as $resultMaxRcvCOA) {
					$maxCOACode		= $resultMaxRcvCOA->MCOA_CODE;
					$maxCode		= $maxCOACode."000";
				}
				//echo $maxCode;die(); 
			} else if($nodeDepth == '4'){
				$coaC 			= substr($coaCode,0,9);
				$coaSt			= $coaC + 999;
				$selectMaxRcvCOA = "SELECT 
											COALESCE(MAX(substr(COA_CODE,1,9)),$coaC)+1  AS MCOA_CODE
										FROM
												gs_coa 		
										WHERE
											  substr(COA_CODE,1,9) BETWEEN $coaC AND $coaSt";
				$selectMaxRcvCOAStatement 		= $this->tableGateway->getAdapter()->createStatement($selectMaxRcvCOA);
				$selectMaxRcvCOAStatement->prepare();
				$selectMaxRcvCOAResult 	= $selectMaxRcvCOAStatement->execute();
				
				if ($selectMaxRcvCOAResult instanceof ResultInterface && $selectMaxRcvCOAResult->isQueryResult()) {
					$resultSet = new ResultSet();
					$resultSet->initialize($selectMaxRcvCOAResult);
				}
				
				foreach($resultSet as $resultMaxRcvCOA) {
					$maxCOACode		= $resultMaxRcvCOA->MCOA_CODE;
					$maxCode		= $maxCOACode;
				}
			}
			$childData1 = array();
			$childData1 = array(
				'COA_CODE'			=> $maxCode,
				'NODE_DEPTH'		=> $nodeDepth,
			);
			return $childData1;
		}
		
		public function getCOTable($id) {
			$id = (int) $id;
			$rowSet = $this->tableGateway->select(array('COA_ID' => $id));
			$row = $rowSet->current();
			if(!$row) {
				throw new \Exception("Could not find row $id");
			}
			return $row;
		}
		
		public function getCheckChild($lft,$rgt) {
			$select = "select count(COA_ID) as TOTALNO from gs_coa where LFT between ".$lft." and ".$rgt."";
			$stmt = $this->tableGateway->getAdapter()->createStatement($select);
			$stmt->prepare();
			$totVal = 0;
			$result = $stmt->execute();
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			foreach($resultSet as $rightShareVals) {
				$totVal =  $rightShareVals['TOTALNO'];
			}
			return $totVal;
		}
		
		public function deleteCoaTable($id) {
			if($this->tableGateway->delete(array('COA_ID' => $id))){
				return true;
			} else {
				return false;
			}
		}
		
		public function deleteCOA($LFT,$RGT) {
			$this->session 		= new SessionContainer('post_supply');
			$businessDate 		= $this->session->businessdate;
			$recDate 			= $this->session->recdate;
			$userId 			= $this->session->userid;
			
			$coaList 	= "	
							SELECT 
									C.COA_ID,
									C.LFT,
									C.RGT
							FROM 
									gs_coa C
							WHERE 
									C.LFT	>= ".$LFT."
							AND		C.RGT	<= ".$RGT."
			";
			$coaListStatement	= $this->tableGateway->getAdapter()->createStatement($coaList);
			$coaListStatement->prepare();
			$coaListResult		= $coaListStatement->execute();
			
			if ($coaListResult instanceof ResultInterface && $coaListResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($coaListResult);
			}
			
			foreach ($coaListResult as $coaListResultData) {
				$COA_ID			= $coaListResultData['COA_ID'];
				$LFT			= $coaListResultData['LFT'];
				$RGT			= $coaListResultData['RGT'];
				
				$coaFound 	= "	
								SELECT 
										TRANMASTER.CB_CODE
								FROM 
										gs_coa 					GS_COA,
										a_transaction_master 	TRANMASTER
								WHERE 
										GS_COA.COA_ID	= ".$COA_ID."
								AND		GS_COA.COA_CODE	= TRANMASTER.CB_CODE
				";
				$coaFoundStatement	= $this->tableGateway->getAdapter()->createStatement($coaFound);
				$coaFoundStatement->prepare();
				$coaFoundResult		= $coaFoundStatement->execute();
				
				if ($coaFoundResult instanceof ResultInterface && $coaFoundResult->isQueryResult()) {
					$resultSet = new ResultSet();
					$resultSet->initialize($coaFoundResult);
				}
				$SUCCESS	= 0;
				$CB_CODE	= '';
				foreach ($coaFoundResult as $coaFoundResultData) {
					$CB_CODE	= $coaFoundResultData['CB_CODE'];
				}
				if($CB_CODE) {
					$SUCCESS	= 0;
					break;	
				} else {
					$deleteCoa = "	
									DELETE
										FROM 
											gs_coa
										WHERE 
											COA_ID	=  ".$COA_ID."
										AND COA_CODE IS NOT NULL	
									";
					$deleteCoaStatement	= $this->tableGateway->getAdapter()->createStatement($deleteCoa);
					$deleteCoaStatement->prepare();
					if($deleteCoaStatement->execute()) {
						$updateLeftNode = "	
											UPDATE 
													gs_coa
											SET 
													LFT	= LFT-2
											WHERE 
													LFT	> ".$LFT."
											";
						$updateLeftNodeStatement	= $this->tableGateway->getAdapter()->createStatement($updateLeftNode);
						$updateLeftNodeStatement->prepare();
						if($updateLeftNodeStatement->execute()) {
							$updateRightNode = "	
												UPDATE 
														gs_coa
												SET 
														RGT	= RGT-2
												WHERE 
														RGT	> ".$RGT."
												";
							$updateRightNodeStatement	= $this->tableGateway->getAdapter()->createStatement($updateRightNode);
							$updateRightNodeStatement->prepare();
							if($updateRightNodeStatement->execute()) {
								$SUCCESS		= 1;
							} else {
								$SUCCESS		= 0;
							}	
						} else {
							$SUCCESS		= 0;
						}	
					} else {
						$SUCCESS		= 0;
					}
				}
			}
			
			if($SUCCESS) {
				return true;	
			} else {
				return false;	
			}
		}
		
		public function getAllChildId($lft,$rgt) {
			$select = "select COA_ID from gs_coa where LFT between ".$lft." and ".$rgt." ORDER BY COA_ID DESC";
			$stmt = $this->tableGateway->getAdapter()->createStatement($select);
			$stmt->prepare();
			$totVal11 = array();;
			$result = $stmt->execute();
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			foreach($resultSet as $rightShareValsss) {
				$totVal11[] =  $rightShareValsss['COA_ID'];
			}
			return $totVal11;
		}
		
		
		public function getCompanyWiseCOA($companyId) {
			$select = "		
						SELECT 
								COA_DETAILS.COA_ID			AS COA_ID,
								COA_DETAILS.COA_NAME_DOT    AS COA_NAME_DOT,
								COA_DETAILS.COA_NAME        AS COA_NAME,
								COA_DETAILS.LFT             AS LFT,   
								COA_DETAILS.NODE_DEPTH      AS NODE_DEPTH,
								COA_DETAILS.COA_CODE      	AS COA_CODE
						FROM 
						(
							SELECT 
									C.COA_ID								AS COA_ID,
									rpad(' ',COUNT(C.COA_NAME)*5,'-') 		AS COA_NAME_DOT,
									C.COA_NAME 								AS COA_NAME,
									COUNT(C.COA_NAME) AS NODE_DEPTH,
									C.LFT,
									C.COA_CODE
							FROM 
									gs_coa 		C,
									gs_coa 		P,
									c_company 	CN
							WHERE 
									C.LFT BETWEEN P.LFT AND P.RGT
							AND 	C.COMPANY_ID 	= CN.COMPANY_ID
							AND 	CN.COMPANY_ID 	= ".$companyId."
							GROUP BY 
									C.COA_ID,
									C.COA_NAME,
									C.COMPANY_ID,
									C.LFT
							ORDER BY 
									C.COMPANY_ID,
									C.LFT 
						) 									COA_DETAILS
						WHERE                
								COA_DETAILS.NODE_DEPTH  < 6  
								AND   COA_DETAILS.COA_ID !=1                                                           
						ORDER BY
								COA_DETAILS.LFT
			";
			$stmt = $this->tableGateway->getAdapter()->createStatement($select);
			$stmt->prepare();
			$result = $stmt->execute();
			
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($result);
			}
			
			return $resultSet;
		}
		public function getCOAListForPaymentEntry($switch) {	
			$getCOASql 	= "SELECT COA_CODE, COA_NAME FROM gs_coa where {$switch} order by COA_CODE, COA_NAME ASC";
			$stmt 		= $this->tableGateway->getAdapter()->createStatement($getCOASql);
			$stmt->prepare();
			$result 	= $stmt->execute();
			if ($result instanceof ResultInterface && $result->isQueryResult()) {
				$resultSet 	= new ResultSet();
				$resultSet->initialize($result);
			}
			return $resultSet;
		}
		public function getTrialBalPayReceiptAmount($coaCode,$frm,$fundCode) {
			if($frm=='p') {
				$getTBAmountSql   = "SELECT nvl(CB_AMOUNT, 0)
									FROM   a_trial_bal
									WHERE  BALANCE_DATE = (SELECT nvl(MAX(BALANCE_DATE),to_date('16-12-1971','dd-mm-yyyy')) FROM a_trial_bal)
									AND    AC_CODE = '".$coaCode."'
									AND    substr(AC_CODE,0,1) = '2'
									AND	   PORTFOLIO_CODE = '".$fundCode."'";
			} else if($frm=='r') {
				$getTBAmountSql   = "SELECT nvl(CB_AMOUNT, 0)
									FROM   IS_PORTFOLIO_TRIAL_BAL
									WHERE  BALANCE_DATE  = (SELECT nvl(MAX(BALANCE_DATE),to_date('16-12-1971','dd-mm-yyyy')) FROM a_trial_bal)
									AND    AC_CODE = '".$coaCode."'
									AND    substr(AC_CODE,0,3) = '305'
									AND	   PORTFOLIO_CODE = '".$fundCode."'";
			} else {
				$getTBAmountSql = "";
			}
			echo $getTBAmountSql;
			die();
			if(strlen($getTBAmountSql)>0) {
			  	$stmt = $this->tableGateway->getAdapter()->createStatement($getCOASql);
				$stmt->prepare();
				$result = $stmt->execute();
				if ($result instanceof ResultInterface && $result->isQueryResult()) {
					$resultSet = new ResultSet();
					$resultSet->initialize($result);
				}
				return $resultSet;
			}
		}
		public function fetchMotherAccount($coaCode) {
			$getTblDataSql   = "SELECT MOTHER_ACCOUNT
								FROM   gs_coa
								WHERE   COA_CODE = '".$coaCode."'";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		public function fetchCOABankOrCash($cbCode) {
			$getCOASql = '';
			if($cbCode == 'cash'){
				$getCOASql="
							SELECT gs_coa.COA_CODE, initcap(gs_coa.COA_NAME) COA_NAME
								  FROM gs_coa
								 WHERE SUBSTR(COA_CODE, 1, 3) = 303
								   AND SUBSTR(COA_CODE, -1, 1) != 0
								 ORDER BY initcap(gs_coa.COA_CODE)
						   ";
			} elseif($cbCode=='bank'){
				$getCOASql="
							SELECT gs_coa.COA_CODE, initcap(gs_coa.COA_NAME) COA_NAME
							  FROM gs_coa, gs_account_details
							 WHERE SUBSTR(COA_CODE, 1, 3) = 304
							   AND SUBSTR(COA_CODE, -1, 1) != 0
							   AND gs_account_details.ACCOUNT_DETAILS_COA = gs_coa.COA_CODE
							   AND LOWER(gs_account_details.ACTIVE_DEACTIVE) = 'y'
							 ORDER BY initcap(gs_coa.COA_CODE)";
			}
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		public function fetchCOACode($input) {
			$getTblDataSql   = "SELECT COA_CODE, COA_NAME FROM gs_coa where COA_CODE like '%".$input."%' AND SUBSTR(COA_CODE,-3) != '000' order by COA_CODE asc";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		public function fetchCOAName($input) {
			$getTblDataSql   = "SELECT COA_CODE, COA_NAME FROM gs_coa where LOWER(COA_NAME) like '".$input."%' AND SUBSTR(COA_CODE,-3) != '000' order by COA_NAME asc";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		
		public function fetchModelName($input,$suppInfoId) {
			$getTblDataSql   = "SELECT  ls_supp_wise_category.CATEGORY_ID,
										ls_category.CATEGORY_NAME, 
										ls_cat_price.BUY_PRICE,
										ls_cat_price.CAT_PRICE_ID,
										ls_category.COA_CODE,
										gs_coa.COA_NAME
										FROM ls_category, ls_cat_price, gs_coa, ls_supplier_info, ls_supp_wise_category
										WHERE LOWER( ls_category.CATEGORY_NAME ) LIKE '".$input."%'
										AND ls_category.active_inactive = 'yes'
										AND ls_cat_price.CATEGORY_ID = ls_category.CATEGORY_ID
										AND ls_cat_price.END_DATE IS NULL
										AND ls_supp_wise_category.END_DATE IS NULL
										AND gs_coa.COA_CODE = ls_category.COA_CODE
										AND ls_supp_wise_category.CATEGORY_ID = ls_category.CATEGORY_ID
										AND ls_supplier_info.SUPPLIER_INFO_ID = ls_supp_wise_category.SUPPLIER_INFO_ID
										AND ls_supp_wise_category.SUPPLIER_INFO_ID = '".$suppInfoId."'
										AND ls_supp_wise_category.IS_SUPPLY = 'yes'
										ORDER BY ls_category.CATEGORY_NAME ASC
										LIMIT 0 , 10";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		public function fetchCOANameForGEntry($input,$switch) {
			$getTblDataSql   = "SELECT COA_CODE, COA_NAME FROM gs_coa where {$switch} AND LOWER(COA_NAME) like '%".$input."%' AND SUBSTR(COA_CODE,-3) != '000' order by COA_NAME asc";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		
		public function fetchBudgetAmount($coaCode,$fiscalYear,$branchID) {
			$getTblDataSql   = "SELECT 	BUDGET_AMOUNT,
										FISCAL_YEAR
								FROM 
										gs_acc_budget
								WHERE
										gs_acc_budget.BUDGET_ACC_CODE = '".$coaCode."'
								AND		gs_acc_budget.FISCAL_YEAR = '".$fiscalYear."'
								AND		gs_acc_budget.BRANCH_ID = '".$branchID."'
								";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		public function fetchAdvanceSearch($cond) {
			$getTblDataSql   = "SELECT 	BUDGET_AMOUNT,
										accbudget.FISCAL_YEAR AS FISCAL_YEAR_ID,
										accbudget.BRANCH_ID,
										c_branch.BRANCH_NAME,
										l_fiscal_year.FISCAL_START,
										l_fiscal_year.FISCAL_END,
										l_fiscal_year.FISCAL_YEAR AS FISCAL_PERIOD,
										l_fiscal_year.FISCAL_MONTH,
										accbudget.BUDGET_ACC_NAME
								FROM 
										gs_acc_budget accbudget,gs_coa,c_branch, l_fiscal_year 
								WHERE
										accbudget.BUDGET_ACC_CODE = gs_coa.COA_CODE
								AND		c_branch.BRANCH_ID = accbudget.BRANCH_ID
								AND 	l_fiscal_year.FISCAL_YEAR_ID = accbudget.FISCAL_YEAR
								{$cond}
								";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		public function fetchBudgetAccCode($branchID) {
			$this->session 	= new SessionContainer('post_supply');
			$businessDate 	= $this->session->businessdate;
			$getTblDataSql   = "SELECT DISTINCT
									  BUDGET_ACC_CODE AS ACCOUNT_CODE,
									  BUDGET_ACC_NAME AS HEAD
								  FROM   gs_acc_budget 
								  WHERE   
									  gs_acc_budget.BRANCH_ID='".$branchID."'
								  ORDER BY gs_acc_budget.BUDGET_ACC_CODE ASC
			";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		public function fetchFSYStartEndDate($fromDate) {
			$getFSYSDSql   = "SELECT T.FISCAL_START,T.FISCAL_END,T.FISCAL_YEAR_ID FROM l_fiscal_year T WHERE '".$fromDate."' BETWEEN T.FISCAL_START AND T.FISCAL_END";
			$getFSYSDStatement = $this->tableGateway->getAdapter()->createStatement($getFSYSDSql);
			$getFSYSDStatement->prepare();
			$getFSYSDResult 	= $getFSYSDStatement->execute();
			if ($getFSYSDResult instanceof ResultInterface && $getFSYSDResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getFSYSDResult);
			}
			$fiscalYearStartDate = '';
			$data = array();
			foreach ($resultSet as $fiscalYearStartDateData) {
				$data[] = array(
									'fiscalYearStartDate' => date('Y-m-d',strtotime($fiscalYearStartDateData['FISCAL_START'])),
									'fiscalYearEndDate' 	=> date('Y-m-d',strtotime($fiscalYearStartDateData['FISCAL_END'])),
									'fiscalYearID'			=> $fiscalYearStartDateData['FISCAL_YEAR_ID'],
								);
				
			}
			return $data;
		}
		public function fetchFSYYear($id) {
			$getFSYSDSql   = "SELECT T.FISCAL_YEAR FROM l_fiscal_year T WHERE FISCAL_YEAR_ID  = '".$id."'";
			$getFSYSDStatement = $this->tableGateway->getAdapter()->createStatement($getFSYSDSql);
			$getFSYSDStatement->prepare();
			$getFSYSDResult 	= $getFSYSDStatement->execute();
			if ($getFSYSDResult instanceof ResultInterface && $getFSYSDResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getFSYSDResult);
			}
			return $resultSet;
		}
		public function fetchFYIDFromSession($businessYear) {
			$getFSYSDSql   = "SELECT T.FISCAL_YEAR_ID, T.FISCAL_YEAR FROM l_fiscal_year T WHERE SUBSTR(FISCAL_YEAR , 6, 9) = '".$businessYear."'";
			$getFSYSDStatement = $this->tableGateway->getAdapter()->createStatement($getFSYSDSql);
			$getFSYSDStatement->prepare();
			$getFSYSDResult 	= $getFSYSDStatement->execute();
			if ($getFSYSDResult instanceof ResultInterface && $getFSYSDResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getFSYSDResult);
			}
			return $resultSet;
		}
		public function getBudgetAccCode($input) {
			$getTblDataSql   = "SELECT DISTINCT BUDGET_ACC_CODE, BUDGET_ACC_NAME FROM gs_acc_budget where BUDGET_ACC_CODE like '%".$input."%' AND SUBSTR(BUDGET_ACC_CODE,-3) != '000' order by BUDGET_ACC_CODE asc limit 10";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
		}
		public function getBudgetAccName($input) {
			$getTblDataSql   = "SELECT DISTINCT BUDGET_ACC_CODE, BUDGET_ACC_NAME FROM gs_acc_budget where LOWER(BUDGET_ACC_NAME) like '".$input."%' AND SUBSTR(BUDGET_ACC_CODE,-3) != '000' order by BUDGET_ACC_NAME asc limit 10";
			$getTblDataStatement = $this->tableGateway->getAdapter()->createStatement($getTblDataSql);
			$getTblDataStatement->prepare();
			$getTblDataResult 	= $getTblDataStatement->execute();
			if ($getTblDataResult instanceof ResultInterface && $getTblDataResult->isQueryResult()) {
				$resultSet = new ResultSet();
				$resultSet->initialize($getTblDataResult);
			}
			return $resultSet;
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