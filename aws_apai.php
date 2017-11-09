<?php
/*
	amazonからオンラインで情報を取得する処理
*/

function aws_apai_get_product_by_asin($asin) {
	// get signed URL
  $url = aws_apai_create_signed_url($asin);

	// parse XML from Amazon API
	$xml = aws_apai_parse_xml($url);

	// build product object
	 $product = array(
	 	'isEligibleForPrime' => (boolean)$xml->Items->Item[0]->Offers->Offer->OfferListing->IsEligibleForPrime,
	 	'name' => (string)$xml->Items->Item[0]->ItemAttributes->Title,
	 	'fprice' => (string)$xml->Items->Item[0]->Offers->Offer->OfferListing->Price->FormattedPrice,
    'ListFprice' => (string)$xml->Items->Item[0]->ItemAttributes->ListPrice->FormattedPrice,
	 	'url' => (string)$xml->Items->Item[0]->DetailPageURL,
    'image' => (string)$xml->Items->Item[0]->SmallImage->URL,
    'imageHeight' => (string)$xml->Items->Item[0]->SmallImage->Height,
    'imageWidth' => (string)$xml->Items->Item[0]->SmallImage->Width,
    'publicationDate' => (string)$xml->Items->Item[0]->ItemAttributes->PublicationDate,
    'releaseDate' => (string)$xml->Items->Item[0]->ItemAttributes->ReleaseDate,
    'publisher' => (string)$xml->Items->Item[0]->ItemAttributes->Publisher,
    'salesRank' => (string)$xml->Items->Item[0]->SalesRank,
    'asin' => $asin
	 	);

	return $product;
}

function aws_apai_create_signed_url($asin) {

    // amazon JSを使っている場合のパラメータ
    $array = get_option("amazonjs_settings");

    $aws_access_key_id = $array["accessKeyId"];
    $aws_secret_key = $array["secretAccessKey"];
    // JP以外の場合は別途分岐が必要
    $aws_associate_tag = $array["associateTagJP"];
    $endpoint = "webservices.amazon.co.jp";
    $uri = "/onca/xml";

    $params = array(
        "Service" => "AWSECommerceService",
        "Operation" => "ItemLookup",
        "AWSAccessKeyId" => $aws_access_key_id,
        "AssociateTag" => $aws_associate_tag,
        "ItemId" => $asin,
        "IdType" => "ASIN",
        "ResponseGroup" => "Images,ItemAttributes,Offers,SalesRank"
    );

    // Set current timestamp if not set
    if (!isset($params["Timestamp"])) {
        $params["Timestamp"] = gmdate('Y-m-d\TH:i:s\Z');
    }

    // Sort the parameters by key
    ksort($params);

    $pairs = array();

    foreach ($params as $key => $value) {
        array_push($pairs, rawurlencode($key)."=".rawurlencode($value));
    }

    // Generate the canonical query
    $canonical_query_string = join("&", $pairs);

    // Generate the string to be signed
    $string_to_sign = "GET\n".$endpoint."\n".$uri."\n".$canonical_query_string;

    // Generate the signature required by the Product Advertising API
    $signature = base64_encode(hash_hmac("sha256", $string_to_sign, $aws_secret_key, true));

    // Generate the signed URL
    $apai_signed_url = 'http://'.$endpoint.$uri.'?'.$canonical_query_string.'&Signature='.rawurlencode($signature);

    return $apai_signed_url;
}

function aws_apai_parse_xml($url) {
    $context  = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));

    $xml = file_get_contents($url, false, $context);

    $xml = simplexml_load_string($xml);

    return $xml;
}
