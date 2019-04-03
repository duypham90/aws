#!/bin/bash

# Set environment
PHP_RUNTIME_ZIP='name.zip'
PHP_RUNTIME_LAYER='Your layer'
S3_BUCKET='Your Bucker' # You must create S3_BUCKET before run
STACK_NAME='Your Lambda stack';

function set_php_layer()
{
    # Create S3 bucket
    printf "\e[92mCreate S3 Bucket... \e[0m\n"
    aws s3 mb s3://$S3_BUCKET

    # Create zip file php-runtime-layer
    printf "\e[92mZip file: 'php-runtime-layer.zip'... \e[0m\n"
    (cd runtime && zip -r $PHP_RUNTIME_ZIP * -x "*.DS_Store")

    # Create lambda layer
    printf "\e[92mCreate Lambda Layer... \e[0m\n"
    php_runtime_arn=$(aws lambda publish-layer-version --layer-name $PHP_RUNTIME_LAYER --zip-file fileb://runtime/$PHP_RUNTIME_ZIP | grep -e 'LayerVersionArn')
    php_runtime_arn=$(sed -e 's/"LayerVersionArn": "\(.*\)",/\1/' <<< $php_runtime_arn)

    # Add Layer Arn in template.yml
    printf "\e[92mAdd Layer Arn in template.yml.. \e[0m\n"
    sed -i '' -E "s/arn:aws:lambda:.+:layer:.+/$php_runtime_arn/" template.yml

    # Remove zip file after setup php layer
    printf "\e[92mRemove zip file $PHP_RUNTIME_ZIP... \e[0m\n"
    rm runtime/$PHP_RUNTIME_ZIP
    printf "\e[92mDone \e[0m\n"
}

function deploy()
{
    # Create packaged.yml and upload to S3 bucket
    printf "\e[92mUpload to S3 Bucket... \e[0m\n"
    sam package --output-template-file packaged.yml --s3-bucket $S3_BUCKET

    # Deploy Aws
    printf "\e[92mDeploy to AWS... \e[0m\n"
    sam deploy --template-file packaged.yml --stack-name $STACK_NAME --capabilities CAPABILITY_NAMED_IAM
    printf "\e[92mDeploy success... \e[0m\n"
}
