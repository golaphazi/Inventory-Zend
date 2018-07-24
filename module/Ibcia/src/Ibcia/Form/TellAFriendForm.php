<?php
//filename : module/Ibcia/src/Ibcia/Form/HolidayForm.php
namespace Ibcia\Form;
 
use Zend\Form\Form;
use Zend\InputFilter;
 
class TellAFriendForm extends Form
{
    public function __construct()
    {
        parent::__construct('tellafriend');
          
        $this->setAttribute('method', 'post');
        $this->setAttribute('onsubmit', 'return check()');
		$this->add(array(
			'name' => 'YOURNAME',
			'type' => 'text',
			'attributes' => array(
				'class' => 'FormTextTypeInput', 
				'id' =>'YOURNAME',
				'autocomplete' => 'off', 
				'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;',
				'maxlength' => '100',
			),
		));
		$this->add(array(
			'name' => 'YOUREMAIL',
			'type' => 'email',
			'attributes' => array(
				'class' => 'FormNumericTypeInput', 
				'id' =>'YOUREMAIL',
				'autocomplete' => 'off', 
				'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;', 
				'onchange' => 'checkEmail(this.value);',
				'maxlength' => '30',
			),
		));
		$this->add(array(
			'name' => 'TONAME',
			'type' => 'text',
			'attributes' => array(
				'class' => 'FormTextTypeInput', 
				'id' =>'TONAME',
				'autocomplete' => 'off', 
				'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;',
				'maxlength' => '100',
			),
		));
		$this->add(array(
			'name' => 'TOEMAIL',
			'type' => 'email',
			'attributes' => array(
				'class' => 'FormNumericTypeInput', 
				'id' =>'TOEMAIL',
				'autocomplete' => 'off', 
				'style' => 'padding-left:10px;font-family:Tahoma, Geneva, sans-serif;', 
				'onchange' => 'checkEmail(this.value);',
				'maxlength' => '30',
			),
		));
        $this->add(array(
			'name' => 'submit',
			'type' => 'Submit',
			'attributes' => array(
				'value' => 'Send',
				'id' => 'submitbutton',
			),
		));
		$this->add(array(
			'name' => 'Close',
			'type' => 'Submit',
			'attributes' => array(
				'value' => 'Close',
				'id' => '',
				'onclick' => 'window.close();',
			),
		));
    }
    
    
}