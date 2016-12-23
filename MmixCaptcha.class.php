<?php

/**
 * MmixCaptcha class
 *
 * @file
 * @author The Little Moe New LLC
 * @author Bingxing Wang <ben@lmn.cat>
 * @ingroup Extensions
 */

use MediaWiki\Auth\AuthenticationRequest;
use \MmixCaptcha\Connector;

include_once "MmixCaptcha.DataObjects.php";
include_once "MmixCaptcha.Connector.php";

class MmixCaptcha extends SimpleCaptcha {
	// used for mmixcaptcha-edit, mmixcaptcha-addurl, mmixcaptcha-badlogin,
	// mmixcaptcha-createaccount, mmixcaptcha-create, mmixcaptcha-sendemail via getMessage()
	protected static $messagePrefix = 'mmixcaptcha-';

    /**
	 * Given a required captcha run, test form input for correct
	 * input on the open session.
	 * @param string $index Captcha idenitifier
	 * @param string $word Captcha solution
	 * @return bool if passed, false if failed or new session
	 */
	protected function passCaptcha( $index, $word ) {
        if ( !$index || $index == "") {
            return false;
        }
        if ( !$word || $word == "") {
            return false;
        }
        
        // API and Mobile will fire twice. Each key can be used twice.
        global $wgRequest;
        $pCounterData = $wgRequest->getSessionData("$index.$word");
        if ( $pCounterData == 1 ) {
            $wgRequest->setSessionData("$index.$word", null);
            return true;
        }

        // Validate with backend.
        $result = Connector::validateChallenge( $index, $word );
        if ( $result ) {
            // Save + counter
            $wgRequest->setSessionData("$index.$word", 1);
        }

        return $result;
    }

	function addCaptchaAPI( &$resultArr ) {
        $captcha = Connector::getChallenge();
        global $wgMmixBackendEndpoint;

        if ($captcha) {
            $resultArr['captcha'] = $this->describeCaptchaType();
            $resultArr['captcha']['id'] = $captcha->id;
            $resultArr['captcha']['captchaId'] = $captcha->id;
            $resultArr['captcha']['questionId'] = $captcha->id;

            $imageIdEncoded = urlencode($captcha->id);
            $description = wfMessage( 'mmixcaptcha-edit' )->parse();
            $imageUrl = "https://{$wgMmixBackendEndpoint}/image/Retrieval?id={$imageIdEncoded}";

            $resultArr['captcha']['question'] = "<p>{$description}</p><img style=\"display: block;\" src=\"{$imageUrl}\" alt=\"MMIX Captcha\" />";
        }
	}

	public function describeCaptchaType() {
		return [
			'type' => 'question',
			'mime' => 'text/html',
		];
	}

    function generateForm() {
        $errorTitle = wfMessage( 'mmixcaptcha-error' )->text();
        $errorContent = wfMessage( 'mmixcaptcha-retry' )->text();
        $placeHolder = wfMessage( 'mmixcaptcha-textplaceholder' )->text();
        $description = wfMessage( 'mmixcaptcha-edit' )->parse();

        $content = <<<HTML
    <div>
        <link rel="stylesheet" href="//mmixstaticassets.azureedge.net/MMIXMIN.68E1DB244A9483F4D64D086304A4F8F92966074EB3DCC0AAC38415E4A4EAC874.css" />
        <img id="mmix-global-captcha-progress-ring" src="//mmixstaticassets.azureedge.net/ProgressRing.gif" width="50" />
        <div class="mmix-captcha-container" id="mmix-captcha-container-1">
            <input id="wpCaptchaId" name="wpCaptchaId" type="hidden" value="" >
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
                        <input class="mmix-captcha-ui-control-input" autocomplete="off" name="wpCaptchaWord" type="text" placeholder="{$placeHolder}" required >
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript" src="https://code.jquery.com/jquery-3.1.1.min.js" ></script>
        <script src="//mmixstaticassets.azureedge.net/MMIXMIN.77CF50A99DA1A3FC94AE37D09CBEF5E8FC45978A69FEE6C23BB37DD16D2B7089.js" async defer></script>
    </div>
HTML;

        return $content;
    }

    function getForm( OutputPage $out, $tabIndex = 1 ) {
        return $this->generateForm();
    }

	function getFormInformation( $tabIndex = 1 ) {
		return [
			'html' => $this->generateForm()
		];
	}

    protected function getCaptchaParamsFromRequest( WebRequest $request ) {
		$index = $request->getVal( 'wpCaptchaId', $request->getVal( 'captchaId' ) );
		$response = $request->getVal( 'wpCaptchaWord', $request->getVal( 'captchaWord' ) );
		return [ $index, $response ];
	}

    public function storeCaptcha( $info ) {
		// ReCaptcha is stored by Google; the ID will be generated at that time as well, and
		// the one returned here won't be used. Just pretend this worked.
		return 'not used';
	}

	function showHelp() {
		global $wgOut;
		$wgOut->setPageTitle( wfMessage( 'captchahelp-title' )->text() );
		$wgOut->addWikiMsg( 'mmixcaptchahelp-text' );
		if ( CaptchaStore::get()->cookiesNeeded() ) {
			$wgOut->addWikiMsg( 'captchahelp-cookies-needed' );
		}
	}

	public function retrieveCaptcha( $index ) {
        return [ 'index' => $index ];
    }

    public function getCaptcha() {
        return [];
    }

    public function getCaptchaInfo( $captchaData, $id ) {
        return wfMessage( 'mmixcaptcha-info' );
    }

	public function onAuthChangeFormFields( array $requests, array $fieldInfo,
		array &$formDescriptor, $action ) {
		/** @var CaptchaAuthenticationRequest $req */
		$req =
			AuthenticationRequest::getRequestByClass( $requests,
				CaptchaAuthenticationRequest::class, true );
		if ( !$req ) {
			return;
		}

        // ugly way to retrieve error information
		$captcha = ConfirmEditHooks::getInstance();

        // Inject widget and ID
        $formDescriptor['captchaId'] = [
			'class' => HTMLMmixCaptchaField::class,
			'error' => $captcha->getError(),
            'label-message' => null
		] + $formDescriptor['captchaId'];

        $formDescriptor['captchaInfo']['label-message'] = null;

		$formDescriptor['captchaWord'] = [
			'class' => HTMLMmixCaptchaWidgetField::class,
            'label-message' => null
		];
	}
}
