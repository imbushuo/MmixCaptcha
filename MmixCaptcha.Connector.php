<?php 

/**
 * MmixCaptcha connector class
 *
 * @file
 * @author The Little Moe New LLC
 * @author Bingxing Wang <ben@lmn.cat>
 * @ingroup Extensions
 */

namespace MmixCaptcha;
include_once "MmixCaptcha.DataObjects.php";

class Connector {

    public static function getChallenge() {
        global $wgMmixBackendEndpoint;
        $reqUrl = "https://{$wgMmixBackendEndpoint}/questionEntry/BeginChallenge";

        $curl = curl_init($reqUrl);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT_MS, 1500);
        curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);

        $result = curl_exec($curl);
        $resultCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        if ( $resultCode != 200 ) {
            $err = curl_error($curl);
            wfDebug ( "[MMIX] Unexpected Error caught: $resultCode $err. ");
            curl_close($curl);
            return false;
        } else {
            // Decode entity
            $e = json_decode ( $result );
            curl_close($curl);
            return new \MmixCaptcha\DataObjects\ChallengeMetadata(
                $e->{"backendId"}, 
                $e->{"id"},
                $e->{"path"});
        }
    }

    public static function validateChallenge( $id, $content ) {
        global $wgMmixBackendEndpoint;
        $reqUrl = "https://{$wgMmixBackendEndpoint}/questionEntry/PostResponse";

        $rep = new \MmixCaptcha\DataObjects\ChallengeResponse( $id, $content );
        $serializedRep = json_encode( $rep );

        $curl = curl_init($reqUrl);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $serializedRep);
        curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($curl, CURLOPT_TIMEOUT_MS, 1500);

        $result = curl_exec($curl);
        $resultCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($resultCode != 200) {
            $err = curl_error($curl);
            wfDebug ( "[MMIX] Unexpected Error caught: $err ($resultCode). ");
        }

        curl_close($curl);

        return $resultCode == 200;
    }
}
