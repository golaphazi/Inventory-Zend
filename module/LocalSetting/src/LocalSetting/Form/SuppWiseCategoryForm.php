<?php
	namespace LocalSetting\Form;

	use Zend\Form\Form;
	use Zend\Db\Adapter\Adapter;
	use Zend\Db\Adapter\AdapterInterface;
	
	class SuppWiseCategoryForm extends Form {
		protected $categoryTable;
		protected $dbAdapter;
		
		public function __construct($name = null, AdapterInterface $dbAdapter) {
			parent::__construct($name);
			$this->setAttribute('method','post');
			if(!$this->dbAdapter) {
				$this->dbAdapter = $dbAdapter;
			}
			$this->add(array(
				'name' => 'SUPPLIER_INFO_ID',
				'type' => 'Select',
				'attributes' => array(
					'id' => 'SUPPLIER_INFO_ID',
					'class' => 'FormSelectTypeInput', 
					//'onchange'=> 'getSuppCOACode(this.value,1);',
					'style' => 'width:220px;padding-left:10px;font-family:Tahoma, Geneva, sans-serif;',
				),
				'options' => array(
					'empty_option'  => '--- please choose ---',
					'value_options' => $this->getCBCodeList(),
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
		public function getCBCodeList() {
			$getCbCode   = "SELECT DISTINCT SUPPLIER_INFO_ID ,NAME
							  FROM ls_supplier_info
							 ";
			$statement = $this->dbAdapter->query($getCbCode);
			$portTypeData    = $statement->execute();
			$selectData = array();
			foreach ($portTypeData as $selectOption) {
				$selectData[$selectOption['SUPPLIER_INFO_ID']] = $selectOption['NAME'];
			}
			return $selectData;
		}
		public function getSystemCategorySelect() {
			$systemNavSql = "
								SELECT 
										C.CATEGORY_ID, 
										rpad(' ',COUNT(C.CATEGORY_NAME)*5,'-')  AS CONTROLLER_DOT, 
										C.CATEGORY_NAME,
										C.LFT
								FROM 
										ls_category C, 
										ls_category P
								WHERE 
										C.LFT BETWEEN P.LFT AND P.RGT
								GROUP BY 
										C.CATEGORY_ID, C.CATEGORY_NAME, C.LFT 
								ORDER BY 
										C.LFT
							  ";
			$systemNavStatement = $this->dbAdapter->query($systemNavSql);
			$systemNavResult	= $systemNavStatement->execute();
			
			$selectData = array();
			foreach ($systemNavResult as $selectOption) {
				$selectData[$selectOption['CATEGORY_ID']] = $selectOption['CONTROLLER_DOT'].$selectOption['CATEGORY_NAME'];
			}
			return $selectData;
		}
	}
?>	