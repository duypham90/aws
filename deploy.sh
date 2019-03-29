#!/bin/bash

set -a
. ./.env
set +a

sam package --output-template-file packaged.yml --s3-bucket $S3_BUCKET
sam deploy --template-file packaged.yml --stack-name $STACK_NAME --capabilities CAPABILITY_NAMED_IAM