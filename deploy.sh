#!/bin/bash

# Set environment
PHP_RUNTIME_ZIP='php-runtime.zip'
PHP_RUNTIME_LAYER='php-runtime-layer'
S3_BUCKET='php-runtime-duy' # You must create S3_BUCKET before run
STACK_NAME='lambda-stack';


function set_php_layer()
{
    # Create S3 bucket
    aws s3 mb s3://$S3_BUCKET

    # Create zip file php-runtime-layer
    (cd runtime && zip -r $PHP_RUNTIME_ZIP * -x "*.DS_Store")

    # Create Lambda layer
    php_runtime_arn=$(aws lambda publish-layer-version --layer-name $PHP_RUNTIME_LAYER --zip-file fileb://runtime/$PHP_RUNTIME_ZIP | grep -e 'LayerVersionArn')
    php_runtime_arn=$(sed -e 's/"LayerVersionArn": "\(.*\)",/\1/' <<< $php_runtime_arn)

    # Add Layer Arn in template.yml
    sed -i '' -E "s/arn:aws:lambda:.+:layer:.+/$php_runtime_arn/" template.yml

    # Remove zip file after setup php layer
    rm runtime/$PHP_RUNTIME_ZIP
}

function deploy()
{
    # Create packaged.yml and upload to S3 bucket
    sam package --output-template-file packaged.yml --s3-bucket $S3_BUCKET

    # Deploy Aws
    sam deploy --template-file packaged.yml --stack-name $STACK_NAME --capabilities CAPABILITY_NAMED_IAM
}
