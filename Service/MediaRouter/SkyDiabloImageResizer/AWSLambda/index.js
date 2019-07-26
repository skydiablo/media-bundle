/**
 * DOKU:
 * für ein reibungslosen ablauf in dem aws API gateway geraffel muss man einige hacks anwenden:
 * 1. hier bin ich nicht sicher ob es wirklich gebraucht wird, dies ermöglicht aber das setzen von
 *    "CONVERT_TO_BINARY" als "contentHandling" welches über die web-gui aktuell nicht zu setzen ist.
 *    somit muss dies über aws cli umgesetzt werden:
 *       $ aws apigateway update-integration-response --rest-api-id m9l4nzaxe5 --resource-id rd6f5h --http-method GET --status-code 200 --patch-operations '[{"op" : "replace", "path" : "/contentHandling", "value" : "CONVERT_TO_BINARY"}]'
 *    die parameter "--rest-api-id m9l4nzaxe5 --resource-id rd6f5h" können der zB web-gui entnommen werden
 * 2. in der aws API gateway console muss für den "Binary Support" alles als binary erlaubt/gesetzt/"was auch immer" werden,
 */ //    hier für das value "*/*" setzen
/**
 * 3. Stage Settings (dev/prod):
 * 3.1: StageVariables:
 *    BUCKET = ***media s3 bucket name***
 * 3.2. optional aber dennoch zu empfehlen, "API cache" aktivieren.
 *
 */

var AWS = require('aws-sdk');
var S3 = new AWS.S3();
var Sharp = require('sharp');

var BUCKET = process.env.BUCKET;
var OUTPUT_QUALITY = process.env.OUTPUT_QUALITY;
var OUTPUT_FORMAT = process.env.OUTPUT_FORMAT;

exports.handler = function(event, context, callback) {
    var key = event.pathParameters.key;
    var width = parseInt(event.pathParameters.width, 10);
    var height = parseInt(event.pathParameters.height, 10);

    BUCKET = event.stageVariables.BUCKET || BUCKET;
    OUTPUT_QUALITY = parseInt(event.pathParameters.quality || event.stageVariables.OUTPUT_QUALITY || OUTPUT_QUALITY || 80, 10);
    OUTPUT_FORMAT = event.pathParameters.format || event.stageVariables.OUTPUT_FORMAT || OUTPUT_FORMAT || 'jpeg';

    // todo: add an security hash

    S3.getObject({Bucket: BUCKET, Key: key}).promise()
        .then((data) => Sharp(data.Body) // @see: http://sharp.dimens.io
        .resize(width, height)
        .max()
        .withoutEnlargement(true)
        .toFormat(OUTPUT_FORMAT, {
            quality: OUTPUT_QUALITY,
        })
        .toBuffer()
    )
    .then((buffer) => {

        let cacheKey = event.path.trim().slice(1);

    console.log('cacheKey: "' + cacheKey + "'");

    // @see: http://docs.aws.amazon.com/AWSJavaScriptSDK/latest/AWS/S3.html#putObject-property
    S3.putObject({ // store resized image to S3
        Body: buffer,
        Bucket: BUCKET,
        ContentType: 'image/jpeg',
        CacheControl: 'max-age=648000', // 6 month
        ACL: 'public-read',
        Key: cacheKey // remove spaces and first '/' from path
    }).promise();
    return buffer;
})
    .then((buffer) => {
        callback(null, {
        statusCode: 200,
            isBase64Encoded: true,
            headers: {
            ["Content-Type"]: 'image/jpeg',
                ["X-Request-Timestamp"]: Date.now(), // a value to check the cache handling
                ["X-SkyDiablo-Settings"]: JSON.stringify({
                OUTPUT_FORMAT: OUTPUT_FORMAT,
                OUTPUT_QUALITY: OUTPUT_QUALITY,
            }),
        },
        body: buffer.toString('base64')
    });
})
    .catch((err) => context.fail(err))
}
