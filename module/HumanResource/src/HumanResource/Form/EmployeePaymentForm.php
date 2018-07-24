<?php
	namespace HumanResource\Form;
	use Zend\Form\Form;
	
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	
	use Zend\Session\Container as SessionContainer;
		
	class EmployeePaymentForm extends Form {
		protected $dbAdapter;
		protected $postedValues;
		
		public function __construct($name = null, AdapterInterface $dbAdapter, $postedValues) {
			$this->session = new SessionContainer('post_supply');
			$businessDate = $this->session->businessdate;			
			date_default_timezone_set("Asia/Dhaka");
			
			parent::__construct($name);
			$this->setAttribute('method','post');
			$this->setAttribute('onsubmit','return employeePayment');
			
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			
			$this->postedValues = $postedValues;
				
			$this->add(array(
				'name' => 'ORG_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' 	=> 'ORG_ID',
					'class'	=>'FormSelectTypeInput',
					'style'	=>'width:210px;padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;',
				),
				'options' => array(
					//'label' => 'Bank : ',
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getMoneyMarketOrganizationForSelect(),
				),
			));
			$this->add(array(
				'name' => 'CHQ_NO',
				'type' => 'text',
				'attributes' => array(
					'id'	=> 'CHQ_NO',
					'class'=>'FormTextTypeInput',
					'style'=>'padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;',
				),
				'options' => array(
					//'label' => 'Cheque Number: ',
				),
			));
			
			$this->add(array(
				'name' => 'CHQ_DATE',
				'type' => 'Text',
				'required'	=> true,
				'attributes' => array(
					'id' => 'CHQ_DATE',
					'size' => '10',
					'readonly' => 'readonly',
					'class' => 'FormDateTypeInput',
					//'onkeyup' => 'removeChar(this)',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;',
					'value' => date('d-m-Y',strtotime($businessDate)),
				),
			));
			
			$this->add(array(
				'name' => 'AMOUNT',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'AMOUNT',
					'value' => '0.00',
					'class' => 'FormNumericTypeInput',
					'onkeyup' => 'removeChar(this);',
					'style'=>'padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;',
					'maxlength'=> '20',
					'onblur' => 'numberFormat("AMOUNT",this.value.toString(),"2",",","."); if((this.value=="") || (this.value==0)) {this.value="0.00";}',		
					'onfocus' => 'if(this.value==0) this.value="";'
				),
				'options' => array(
					//'label' => 'Amount : ',
				),
			));
			$this->add(array(
				'name' => 'REMARKS',
				'type' => 'textarea',
				'attributes' => array(
					'id'	=> 'REMARKS',
					'class'=>'FormTextTypeInput',
					'style'=>'padding-left:10px;font-family:Tahoma, Geneva, sans-serif; font-size:100%;',
				),
				'options' => array(
					//'label' => 'Cheque Number: ',
				),
			));
			$this->add(array(
				'name' => 'submit',
				'type' => 'Submit',
				'attributes' => array(
					'value' => 'Go',
					'id' => 'submitbutton',
					'style' => 'font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
				),
			));
		}
		
		public function getMoneyMarketOrganizationForSelect() {
			$moneyMarketOrganizationSql = 'SELECT ORG_ID, ORG_NAME FROM gs_money_mkt_org ORDER BY ORG_NAME ASC';
			$statement = $this->dbAdapter->query($moneyMarketOrganizationSql);
			$moneyMarketOrganizationData    = $statement->execute();
			
			$selectData = array();
			foreach ($moneyMarketOrganizationData as $selectOption) {
				$selectData[$selectOption['ORG_ID']] = $selectOption['ORG_NAME'];
			}
			return $selectData;
		}
	}
?>	