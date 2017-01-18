<?php

/**
 * Creates a MMIX Captcha widget. Does not return any data; handling the data submitted by the
 * widget is callers' responsibility.
 */
class HTMLMmixCaptchaField extends HTMLFormField {
	/** @var string Error returned by MMIX in the previous round. */
	protected $error;

	/**
     * Parameters:
     * - error: (string) ReCaptcha error from previous round
     * @param array $params
     */
	public function __construct( array $params ) {
		$params += [ 'error' => null ];
		parent::__construct( $params );

		$this->error = $params['error'];

		$this->mName = 'wpCaptchaId';
	}

	public function getInputHTML( $value ) {
		return '<input id="wpCaptchaId" name="wpCaptchaId" type="hidden" value="" >';
	}
}