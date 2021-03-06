<?php
	namespace GlobalSetting\Form;
	
	use Zend\InputFilter;
	use Zend\Form\Form;
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	
	use Zend\Validator\NotEmpty;
	
	class CoaForm extends Form {
		protected $coaTable;
		protected $dbAdapter;
		protected $postedValues;
		
		public function __construct($name = null, AdapterInterface $dbAdapter, $postedValues) {
			parent::__construct($name);
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return doValidationCOAForm();');			
			
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			
			$this->postedValues = $postedValues;
			
			$this->add(array(
				'name' => 'COA_ID',
				'type' => 'Hidden',
			));
			$this->add(array(
				'name' => 'COMPANY_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'COMPANY_ID',
					'class'=>'FormSelectTypeInput',
					'style'=>'padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;',
				),
				'options' => array(
					//'label' => 'Company : ',
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getCompanyForSelect(),
				),
			));
			$this->add(array(
				'name' => 'PARENT_COA',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'PARENT_COA',
					'value' => (empty($this->postedValues['PARENT_COA'])) ? "" : $this->postedValues['PARENT_COA'],
					'style'=>'padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;',
				),
				'options' => array(
					'label' => '',
					'empty_option'  => '--- please choose ---',
					'value_options' => (empty($this->postedValues['COMPANY_ID'])) ? array() : $this->getCOAForSelect($this->postedValues['COMPANY_ID']),
					//'value_options' => $this->getCOAForSelect(),
				),
			));
			$this->add(array(
				'name' => 'COA_NAME',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'COA_NAME',
					'class'=>'FormTextTypeInput',
					'maxlength'=> '300',
					'style'=>'padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;',
					'placeholder' => 'input coa head here'
				),
				'options' => array(
					//'label' => 'Sub COA : ',
				),
			));
			$this->add(array(
				'name' => 'COA_CODE',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'COA_CODE',
					'class'=>'FormTextTypeInput',
					'maxlength'=> '9',
					'onkeyup' => 'removeChar(this)',
					'style'=>'padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;',
					'placeholder' => 'auto generation coa code'
				),
				'options' => array(
					//'label' => 'COA Code : ',
				),
			));
			$this->add(array(
				'name' => 'AUTO_COA',
				'type' => 'Hidden',
				'attributes' => array(
					'id' => 'AUTO_COA',
					'value' => 'n',
					'style'=>'padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;',
				),
				'options' => array(
					//'label' => 'Auto COA Code : ',
				),
			));
			$this->add(array(
				'name' => 'submit',
				'type' => 'Submit',
				'attributes' => array(
					'value' => 'Go',
					'id' => 'submitbutton',
					'style'=>'font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
				),
			));
		}
		
		public function getCompanyForSelect() {
			$companySql 	= 'SELECT * FROM c_company ORDER BY COMPANY_NAME ASC';
			$statement 		= $this->dbAdapter->query($companySql);
			$companyData    = $statement->execute();
			
			$selectData 	= array();
			foreach ($companyData as $selectOption) {
				$selectData[$selectOption['COMPANY_ID']] = $selectOption['COMPANY_NAME'];
			}
			return $selectData;
		}
		
		public function getCOAForSelect($companyId) {
			$cOASql = "
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
								COA_DETAILS.NODE_DEPTH  < 5                                                             
						ORDER BY
								COA_DETAILS.LFT
			";
			$statement = $this->dbAdapter->query($cOASql);
			$cOAData    = $statement->execute();
			
			$selectData = array();
			foreach ($cOAData as $selectOption) {
				$selectData[$selectOption['COA_ID']] = $selectOption['COA_NAME_DOT'].$selectOption['COA_NAME'];
			}
			return $selectData;
		}
		
	}
?>