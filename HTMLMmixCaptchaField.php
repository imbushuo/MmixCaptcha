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
		$out = $this->mParent->getOutput();
		$lang = htmlspecialchars( urlencode( $this->mParent->getLanguage()->getCode() ) );

		// Insert reCAPTCHA script, in display language, if available.
		// Language falls back to the browser's display language.
		// See https://developers.google.com/recaptcha/docs/faq

		$output = Html::element( 'div', [
			'class' => [
				'mw-confirmedit-captcha-fail' => !!$this->error,
			]
		] );

		return '<input id="wpCaptchaId" name="wpCaptchaId" type="hidden" value="" >';
	}
}