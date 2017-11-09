# AmazonWordpressApi

## 使い方
wordpressからAmazon Product Advertising APIを使ってAmazonに商品情報を取得します。  

AmazonJSと互換性を持たせています。  
AmazonJSを使っている場合は、そのまま使うことができます。  
AmazonJSを使っていない場合は、次の行に適切な設定をしてください。

【変更箇所】  
$aws_access_key_id = $array["accessKeyId"];  
$aws_secret_key = $array["secretAccessKey"];  
