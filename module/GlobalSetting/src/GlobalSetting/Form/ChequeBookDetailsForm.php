<?php
	namespace GlobalSetting\Form;
	use Zend\Form\Form;
	
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
		
	class ChequeBookDetailsForm extends Form {
		
		protected $chequebookdetailsTable;
		protected $dbAdapter;
		
		public function __construct($name = null, AdapterInterface $dbAdapter) {
			parent::__construct('chequebookdetails');
			$this->setAttribute('method','post');
			
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			
			$this->add(array(
				'name' => 'CHEQUE_BOOK_DETAILS_ID',
				'type' => 'Hidden',
			));
			$this->add(array(
				'name' => 'ACCOUNT_DETAILS_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'ACCOUNT_DETAILS_ID',
					'class'=>'FormSelectTypeInput',
					'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
				),
				'options' => array(
					//'label' => 'Account : ',
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getAccountForSelect(),
				),
			));
			$this->add(array(
				'name' => 'CHEQUE_NO_RANGE_FROM',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'CHEQUE_NO_RANGE_FROM',
					'class'=>'FormTextTypeInput',
					'style'=>'width:60px;padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
					'placeholder' => 'start no.',
				),
			));
			$this->add(array(
				'name' => 'CHEQUE_NO_RANGE_TO',
				'type' => 'Text',
				'attributes' => array(
					'id' => 'CHEQUE_NO_RANGE_TO',
					'class'=>'FormTextTypeInput',
					'style'=>'width:60px;padding-left:10px;font-family:Tahoma, Geneva, sans-serif;font-size:100%;',
					'placeholder' => 'end no.',
				),
				'options' => array(
					//'label' => 'Cheque Range : ',
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
		
		public function getAccountForSelect() {
			$accountSql = 'SELECT * FROM gs_account_details';
			$statement = $this->dbAdapter->query($accountSql);
			$accountData    = $statement->execute();
			
			$selectData = array();
			foreach ($accountData as $selectOption) {
				$selectData[$selectOption['ACCOUNT_DETAILS_ID']] = $selectOption['ACCOUNT_NAME'].''.$selectOption['ACCOUNT_NO'];
			}
			return $selectData;
		}
	}
?>	