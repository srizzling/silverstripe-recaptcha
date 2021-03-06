<?php
/**
 * @package recaptcha
 */

/**
 * @author Ingo Schommer, SilverStripe Ltd.
 */
class RecaptchaFieldFunctionalTest extends FunctionalTest {
	
	protected $orig = array();
	
	function setUp() {
		parent::setUp();
		
		$this->orig['public_api_key'] = RecaptchaField::$public_api_key;
		$this->orig['public_api_key'] = RecaptchaField::$private_api_key;
		RecaptchaField::$public_api_key = 'test';
		RecaptchaField::$private_api_key = 'test';
	}
	
	function tearDown() {
		RecaptchaField::$public_api_key = $this->orig['public_api_key'];
		RecaptchaField::$private_api_key = $this->orig['public_api_key'];
		
		parent::tearDown();
	}
	
	function testValidSubmission() {
		$this->get('RecaptchaFieldFunctionalTest_Controller');
		$data = array(
			'recaptcha_challenge_field' => 'valid',
			'recaptcha_response_field' => 'response'
		);
		$response = $this->submitForm('Form_Form', null, $data);
		$this->assertEquals('submitted', $response->getBody());
	}
	
	function testInvalidSubmission() {
		$this->get('RecaptchaFieldFunctionalTest_Controller');
		$data = array(
			'recaptcha_challenge_field' => 'invalid',
			'recaptcha_response_field' => 'response'
		);
		$response = $this->submitForm('Form_Form', null, $data);
		$els = $this->cssParser()->getBySelector('#MyRecaptchaField span.validation');
		// TODO Messed up newlines, no idea why...
		$this->assertEquals(
			"Your answer didn't match the captcha words, please try again.",
			str_replace(PHP_EOL, ' ', (string)$els[0])
		);
	}
	
}

class RecaptchaFieldFunctionalTest_Controller extends Controller implements TestOnly {

	private static $allowed_actions = array(
		'Form'
	);

	protected $template = 'BlankPage';
	
	function Link($action = null) {
		return Controller::join_links('RecaptchaFieldFunctionalTest_Controller', $action);
	}
	
	function Form() {
		$form = new Form(
			$this,
			'Form',
			new FieldList(
				$f = new RecaptchaField('MyRecaptchaField')
			),
			new FieldList(
				new FormAction('doSubmit', 'submit')
			)
		);
		$f->setHTTPClient(new RecaptchaFieldTest_HTTPClient());
		
		return $form;
	}
	
	function doSubmit($data, $form) {
		return 'submitted';
	}
}