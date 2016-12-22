<?php 

/**
 * MmixCaptcha Data object class
 *
 * @file
 * @author The Little Moe New LLC
 * @author Bingxing Wang <ben@lmn.cat>
 * @ingroup Extensions
 */

namespace MmixCaptcha\DataObjects;

class ChallengeMetadata {

    // Backend identifier
    public $backendId;

    // Image one-time token (ID)
    public $id;

    // Reserved for further use.
    public $path;

    // Class constructor.
    public function __construct( $backendId, $id, $path ) {
        $this->backendId = $backendId;
        $this->id = $id;
        $this->path = $path;
    }
}

class ChallengeResponse {

    // Session ID
    public $id;

    // Challenge content
    public $content;

    public function __construct ( $id, $content )
    {
        $this->id = $id;
        $this->content = $content;
    }
}