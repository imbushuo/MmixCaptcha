<?php

/**
 * Creates a MMIX Captcha widget. Does not return any data; handling the data submitted by the
 * widget is callers' responsibility.
 */
class HTMLMmixCaptchaWidgetField extends HTMLFormField {

	/**
     * Parameters:
     * @param array $params
     */
	public function __construct( array $params ) {
		parent::__construct( $params );
	}

	public function getInputHTML( $value ) {
		$out = $this->mParent->getOutput();
		$lang = htmlspecialchars( urlencode( $this->mParent->getLanguage()->getCode() ) );

		$out->addHeadItem(
			'mmix-dedicatedjquery',
			"<script type=\"text/javascript\" src=\"//code.jquery.com/jquery-3.1.1.min.js\" ></script>"
		);
        $out->addHeadItem(
			'mmix-coremodule',
			"<script type=\"text/javascript\" src=\"//mmixstaticassets.azureedge.net/MMIXMIN.77CF50A99DA1A3FC94AE37D09CBEF5E8FC45978A69FEE6C23BB37DD16D2B7089.js\" async defer></script>"
		);
        $out->addHeadItem(
            'mmix-corestylesheet',
            "<link rel=\"stylesheet\" href=\"//mmixstaticassets.azureedge.net/MMIXMIN.68E1DB244A9483F4D64D086304A4F8F92966074EB3DCC0AAC38415E4A4EAC874.css\" />"
        );

        $errorTitle = wfMessage( 'mmixcaptcha-error' )->text();
        $errorContent = wfMessage( 'mmixcaptcha-retry' )->text();
        $placeHolder = wfMessage( 'mmixcaptcha-textplaceholder' )->text();
        $description = wfMessage( 'mmixcaptcha-createaccount' )->parse();

		$output .= <<<HTML
    <div>
        <img id="mmix-global-captcha-progress-ring" src="//mmixstaticassets.azureedge.net/ProgressRing.gif" width="50" />
        <div class="mmix-captcha-container" id="mmix-captcha-container-1">
            <input id="telemetryCorrelationId" name="telemetryCorrelationId" type="hidden" value="" >
            <input id="telemetryInstanceId" name="telemetryInstanceId" type="hidden" value="">
            <div class="mmix-captcha-ui-container" id="mmix-captcha-ui-container-1">
                <div id="mmix-captcha-ui-image-container" class="mmix-captcha-ui-image-container">
                    <img id="mmix-captcha-progress-ring" src="//mmixstaticassets.azureedge.net/ProgressRing.gif" width="50" />
                    <img id="mmix-captcha-ui-image-control" />
                    <div id="mmix-captcha-ui-error-control" class="mmix-captcha-ui-fail-error-container" style="display: none">
                        <img class="mmix-captcha-ui-fail-error-image" src="//mmixstaticassets.azureedge.net/ErrorIcon.png">
                        <div class="mmix-captcha-ui-fail-error-text-container">
                            <h2 class="error-title">{$errorTitle}</h2>
                            <p id="mmixErrorDetailed" class="error-message">{$errorContent}</p>
                            <p id="mmixErrorRefA" class="error-message no-margin"></p>
                            <p id="mmixErrorRefB" class="error-message no-margin"></p>
                            <p id="mmixErrorRefC" class="error-message no-margin"></p>
                        </div>
                    </div>
                </div>
                <div class="mmix-captcha-ui-control-container">
                    <div class="mmix-captcha-ui-control-information-text-container">
                        <div class="mmix-captcha-ui-control-information-text">
                            {$description}
                        </div>
                        <input class="mmix-captcha-ui-control-input" name="wpCaptchaWordA" autocomplete="off" type="text" placeholder="{$placeHolder}" required tabindex="6">
                    </div>
                </div>
            </div>
        </div>
    </div>
HTML;

		return $output;
	}
}