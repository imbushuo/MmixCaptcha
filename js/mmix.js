// MMIX.js: MMIX Client Script
// Copyright 2016 - Present Project MMIX Authors. ALL RIGHTS RESERVED.

(function ($) {
    var lastRetrieved = new Date();
    var requestTimeThresholdDuration = 5000;
    var portalUrl = "https://mmixlaxtpscprd.moegirlpedia.moetransit.com/";
    var clientLang = "en";
    var resReqEndpoint = "https://mmixprdsjc.blob.core.windows.net/mmixstaticassets";
    
    // Local fallback resource.
    var localFallbackRes = {
        "requestThrottled": "阁下发送了太多的请求，请稍等片刻后再试（点击此区域可以重试）。",
        "refATmpl": "REF A: %s",
        "refBTmpl": "REF B: %s",
        "refCTmpl": "REF C: %s",
        "localRequestThrottledToastTitle": "请稍等",
        "localRequestThrottledToastContent": "阁下尝试请求新的验证码的速度过快，请稍等片刻后再试。"
    };

    $(document).ready(function () {
        console.log("[MMIX] Client is ready");
        console.log("[MMIX] Register events.");
        
        // Resolve traffic endpoint which will be used later
        var endpoint = $(".mmix-host").data("endpoint");
        if (!isNullOrEmpty(endpoint))
        {
            portalUrl = "https://" + endpoint + "/";
        }

        // We will resolve client language using HTML tag because MediaWiki did so
        clientLang = $("html").prop("lang");
        // Attempt to retrieve content first.
        initClientResource(clientLang, function ()
        {
            $("#mmix-global-captcha-progress-ring").hide();
            $("#mmix-captcha-container-1").show();

            document.getElementById("mmix-captcha-ui-image-container")
                .addEventListener('click', function () { reloadImage(); });

            loadImage();
        });
    });

    // Utility to check string.
    function isNullOrEmpty(str)
    {
        return (!str || 0 === str.length);
    }

    // Method that loads localization resources.
    function initClientResource(language, callback) {
        console.log("[MMIX] Attempt to resolve client resource.");
        var fileName = "MmixClient." + clientLang + ".json";

        $.ajax({
            url: resReqEndpoint + "/" + encodeURIComponent(fileName),
            dataType: "json",
            error: function (jqXHR, textStatus, errorThrown) {
                console.warn("[MMIX] Client resolution failed, fallback to default resource.");
                $.i18n.load(localFallbackRes);
                // Continue initialization.
                callback();
            },
            success: function (entity, status, request) {
                // Load resource file.
                console.log("[MMIX] Client resource was resolved.");
                $.i18n.load(entity);
                // Continue initialization.
                callback();
            }
        });
    }

    // Method that loads image.
    function loadImage() {
        console.log("[MMIX] Image requested.");
        setImageVisibility(false);
        setErrorVisibility(false);
        setProgressRingVisibility(true);

        var requestLang = clientLang.toLowerCase();
        // Some sort of workaround of language variants
        if (requestLang == "zh-hk" || requestLang == "zh-tw" ||
            requestLang == "zh-hant" || requestLang == "zh-mo" || 
            requestLang == "zh-classical" || requestLang == "zh-yue")
        {
            requestLang = "zh-HK";
        }
        else if (requestLang == "zh" || requestLang == "zh-cn" ||
            requestLang == "zh-hans" || requestLang == "zh-sg" ||
            requestLang == "zh-my")
        {
            requestLang = "zh-CN";
        }

        var endpoint = portalUrl + "questionEntry/BeginChallenge?expectedLang=" + encodeURIComponent(requestLang);

        $.ajax({
            url: endpoint,
            dataType: "json",
            error: function (jqXHR, textStatus, errorThrown) {
                // Display general error message
                // Do not provide details; Status-specific handler will provide it
                var statusCode = jqXHR.status;
                
                // See if request was throttled
                if (statusCode == 429) {
                    $("#mmixErrorDetailed").text($.i18n._('requestThrottled'));
                }

                if (statusCode != 0) {
                    // Set reference information
                    // Save instance ID for telemetry.
                    var instanceId = jqXHR.getResponseHeader('Deployment');
                    $("#telemetryInstanceId")[0].value = instanceId;
                    $("#mmixErrorRefA").text($.i18n._('refATmpl', instanceId));

                    // Save request time for telemetry.
                    var reqDate = jqXHR.getResponseHeader('Date');
                    $("#mmixErrorRefB").text($.i18n._('refBTmpl', reqDate));

                    // Save correlation ID for telemetry.
                    var correlationId = jqXHR.getResponseHeader('X-Correlation-Id');
                    $("#telemetryCorrelationId")[0].value = correlationId;
                    $("#mmixErrorRefC").text($.i18n._('refCTmpl', correlationId));
                } else {
                    $("#mmixErrorRefA").text($.i18n._('refATmpl', "PRECONNFAILURE"));
                }

                setProgressRingVisibility(false);
                setImageVisibility(false);
                setInputAvailability(false);
                setErrorVisibility(true);
            },
            success: function (entity, status, request) {
                console.log("[MMIX] Image downloaded.");
                // Put entity content to page.
                var backendServerEndpoint = portalUrl + "image/Retrieval?id=";
                backendServerEndpoint += encodeURIComponent(entity.id);
                $("#mmix-captcha-ui-image-control")[0].src = backendServerEndpoint;

                // Save challenge ID for verification.
                $("#wpCaptchaId")[0].value = entity.id;

                // Save correlation ID for telemetry.
                var correlationId = request.getResponseHeader('X-Correlation-Id');
                $("#telemetryCorrelationId")[0].value = correlationId;

                // Save instance ID for telemetry.
                var instanceId = request.getResponseHeader('Deployment');
                $("#telemetryInstanceId")[0].value = instanceId;

                setProgressRingVisibility(false);
                setErrorVisibility(false);
                setImageVisibility(true);
                setInputAvailability(true);
            }
        });
    }

    // Method taht processes reload requests.
    function reloadImage() {
        if (((new Date()) - lastRetrieved) < requestTimeThresholdDuration) {
            console.warn("[MMIX] Attempt to load image but locally throttled.");
            $.toast({
                heading: $.i18n._('localRequestThrottledToastTitle'),
                text: $.i18n._('localRequestThrottledToastContent'),
                position: 'mid-center',
                icon: 'warning',
                stack: false
            });
        } else {
            lastRetrieved = new Date();
            loadImage();
        }
    }

    function setProgressRingVisibility(visibility) {
        if (visibility) {
            $("#mmix-captcha-progress-ring").show();
        } else {
            $("#mmix-captcha-progress-ring").hide();
        }
    }

    function setImageVisibility(visibility) {
        if (visibility) {
            $("#mmix-captcha-ui-image-control").show();
        } else {
            $("#mmix-captcha-ui-image-control").hide();
        }
    }

    function setErrorVisibility(visibility) {
        if (visibility) {
            $("#mmix-captcha-ui-error-control").show();
        } else {
            $("#mmix-captcha-ui-error-control").hide();
        }
    }

    function setInputAvailability(availability) {
        if (availability) {
            $(".mmix-captcha-ui-control-input").prop('disabled', false);
        } else {
            $(".mmix-captcha-ui-control-input").prop('disabled', true);
        }
    }
})($);