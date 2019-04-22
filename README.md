# AWS

## Deploying By SAM
- [Event rule](https://github.com/awslabs/aws-security-benchmark/blob/master/architecture/create-benchmark-rules.yaml)
- [Docs](https://docs.aws.amazon.com/serverless-application-model/latest/developerguide/serverless-deploying.html)
- [Aws CLI](https://docs.aws.amazon.com/cli/latest/reference/cloudformation/deploy/index.html)
- [sam-test-basic-project](https://github.com/mavi888/sam-test-basic-project)
- [serverless-sam to sam-template.yml](https://github.com/SAPessi/serverless-sam)
- [PHP Runtime](https://github.com/pagnihotry/PHP-Lambda-Runtime)
- [Custome PHP-runtime](https://aws.amazon.com/vi/blogs/apn/aws-lambda-custom-runtime-for-php-a-practical-example/)

## Note

> Both the sam package and sam deploy commands described in this section are identical to their AWS CLI 
equivalent commands: [aws cloudformation package](https://docs.aws.amazon.com/cli/latest/reference/cloudformation/package.html)
[aws cloudformation deploy](https://docs.aws.amazon.com/cli/latest/reference/cloudformation/deploy/index.html), respectively.

### Package SAM template

**YourBucket**: The name of the S3 bucket ( Must existed on S3 bucket or create your bucket before **deploy package**)

```javascript

$ sam package \
    --template-file deploy.yml \
    --output-template-file serverless-output.yml \
    --s3-bucket YourBucket
```

### Deploy packaged SAM template

```javascript
$ sam deploy \
    --template-file serverless-output.yml \
    --stack-name your-stack \
    --capabilities CAPABILITY_NAMED_IAM
```

## Example yml file: deploy.yml

```yml
AWSTemplateFormatVersion: 2010-09-09
Transform: AWS::Serverless-2016-10-31
Resources:
  DuyLambdaRole:
    Type: AWS::IAM::Role
    Properties:
      RoleName:
        Fn::Sub: lambda-role
      AssumeRolePolicyDocument:
        Version: 2012-10-17
        Statement:
          - Action:
            - sts:AssumeRole
            Effect: Allow
            Principal:
              Service:
              - lambda.amazonaws.com
      ManagedPolicyArns:
        - arn:aws:iam::aws:policy/AWSLambdaExecute
        - arn:aws:iam::aws:policy/AmazonS3FullAccess
        - arn:aws:iam::aws:policy/AmazonDynamoDBFullAccess
        - arn:aws:iam::aws:policy/AmazonKinesisFullAccess
      Path: /
  TestSAM:
    Type: 'AWS::Serverless::Function'
    Properties:
      Handler: sendGridTutorial
      Role: !GetAtt [DuyLambdaRole, Arn]
      Runtime: provided
      Layers:
        - arn:aws:lambda:us-east-1:270663217580:layer:fucku:3
      CodeUri: ./src
      Description: TEST SAM
      MemorySize: 1024
      Timeout: 15
```

# Deploy by zip layer file
```javscript
    zip -r php-runtime-layer.zip
    aws s3 cp ./layer.zip s3://yourbucket
```

```yml
AWSTemplateFormatVersion: 2010-09-09
Transform: AWS::Serverless-2016-10-31
Resources:
  InitLayer:
    Type: AWS::Lambda::LayerVersion
    Properties:
      LayerName: php-runtime-layer
      Description: My layer
       Content: 
        S3Bucket: yourbucket
        S3Key: php-runtime-layer.zip    #Layer File
      LicenseInfo: MIT
  InitRoleLambda:
    Type: AWS::IAM::Role
    Properties:
      RoleName:
        Fn::Sub: lambda-role
      AssumeRolePolicyDocument:
        Version: 2012-10-17
        Statement:
          - Action:
            - sts:AssumeRole
            Effect: Allow
            Principal:
              Service:
              - lambda.amazonaws.com
      ManagedPolicyArns:
        - arn:aws:iam::aws:policy/AWSLambdaExecute
        - arn:aws:iam::aws:policy/AmazonS3FullAccess
        - arn:aws:iam::aws:policy/AmazonDynamoDBFullAccess
        - arn:aws:iam::aws:policy/AmazonKinesisFullAccess
      Path: /
  TestSAM:
    Type: 'AWS::Serverless::Function'
    Properties:
      Handler: sendGridTutorial
      Role: !GetAtt [InitRoleLambda, Arn]
      Runtime: provided
      Layers:
        - !Ref InitLayer
      CodeUri: ./src
      Description: TEST SAM
      MemorySize: 1024
      Timeout: 15
   LambdaFunctionOverHttps:
    Type: AWS::Serverless::Function
    Properties:
      Handler: test
      Runtime: provided
      CodeUri: ./src
      Events:
        Api1:
          Type: Api
          Properties:
            Path: /hello
            Method: POST
        Api2:
          Type: Api
          Properties:
            Path: /hello/{itemId}
            Method: ANY   

```
