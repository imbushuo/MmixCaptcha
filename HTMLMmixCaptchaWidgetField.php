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
        $this->mName = "captchaWord";
	}

	public function getInputHTML( $value ) {
		$out = $this->mParent->getOutput();
		$lang = htmlspecialchars( urlencode( $this->mParent->getLanguage()->getCode() ) );

        global $wgMmixBackendEndpoint, $wgUseResourceManager, $wgClientResourceIdentifier;

        if (!isset($wgClientResourceIdentifier))
        {
            $wgClientResourceIdentifier = "";
        }

        if ($wgUseResourceManager)
        {
            $out->addModules("ext.mmixCaptcha");
            $out->addModuleStyles("ext.mmixCaptcha");
        }
        else
        {
            $out->addHeadItem(
                'mmix-corestylesheet',
                "<link rel=\"stylesheet\" href=\"//mmixstaticassets.azureedge.net/{$wgClientResourceIdentifier}/mmix.min.css\" />"
            );
        }

        // Have to load JavaScript globally
        $out->addHeadItem(
			    'mmix-coremodule',
			    "<script type=\"text/javascript\" src=\"//mmixstaticassets.azureedge.net/{$wgClientResourceIdentifier}/mmix.min.js\" async defer></script>"
		);

        $errorTitle = wfMessage( 'mmixcaptcha-error' )->text();
        $errorContent = wfMessage( 'mmixcaptcha-retry' )->text();
        $placeHolder = wfMessage( 'mmixcaptcha-textplaceholder' )->text();
        $description = wfMessage( 'mmixcaptcha-createaccount' )->parse();

		$output = <<<HTML
    <div class="mmix-host" data-lang="{$lang}" data-endpoint="{$wgMmixBackendEndpoint}">
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
                        <input class="mmix-captcha-ui-control-input" name="captchaWord" autocomplete="off" type="text" placeholder="{$placeHolder}" required tabindex="6">
                    </div>
                </div>
            </div>
        </div>
    </div>
HTML;

		return $output;
	}
}