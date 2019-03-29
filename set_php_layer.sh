#!/bin/bash

set -a
. ./.env
set +a

(cd runtime && zip -r $PHP_RUNTIME_ZIP * -x "*.DS_Store")

php_runtime_arn=$(aws lambda publish-layer-version --layer-name $PHP_RUNTIME_LAYER --zip-file fileb://runtime/$PHP_RUNTIME_ZIP | grep -e 'LayerVersionArn')
php_runtime_arn=$(sed -e 's/"LayerVersionArn": "\(.*\)",/\1/' <<< $php_runtime_arn)

sed -i '' -E "s/arn:aws:lambda:.+:layer:.+/$php_runtime_arn/" template.yml

#Create S3

aws s3 mb s3://$S3_BUCKET
