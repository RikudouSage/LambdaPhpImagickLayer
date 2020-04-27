[![Build Status](https://travis-ci.com/RikudouSage/LambdaPhpImagickLayer.svg?branch=master)](https://travis-ci.com/RikudouSage/LambdaPhpImagickLayer)
![Build Lambda Layers](https://github.com/RikudouSage/LambdaPhpImagickLayer/workflows/Build%20Lambda%20Layers/badge.svg)

# AWS Lambda PHP imagick layer with HEIC and WEBP support

This layer requires [bref](https://packagist.org/packages/bref/bref) to be installed.

If you want to use the layer without installing the plugin, see the bottom of this README, otherwise read on.

## Installation

`composer require rikudou/lambda-imagick-layer`

## Usage

Import the plugin inside your serverless.yml:

```yaml
plugins:
      - ./vendor/bref/bref
      - ./vendor/rikudou/lambda-imagick-layer
```

Then use the layer by adding `${rikudou:imagick-version}` to your layers section (where version is one of `72`, `73`, `74`).

The version should be the same as in the base bref layer.

Example: 

```yaml
functions:
  website:
    handler: public/index.php
    timeout: 28 # in seconds (API Gateway has a timeout of 29 seconds)
    layers:
      - ${bref:layer.php-74-fpm}
      - ${rikudou:imagick-74} # or ${rikudou:imagick-73} or ${rikudou:imagick-72}
    events:
      -   http: 'ANY /'
      -   http: 'ANY /{proxy+}'
```

## Currently supported regions:

- us-east-1
- us-east-2
- us-west-1
- us-west-2
- ca-central-1
- eu-central-1
- eu-west-1
- eu-west-2
- eu-west-3
- eu-north-1

## Using the layer without installing plugin

Just import the layer manually, the format is
`arn:aws:lambda:{region}:725092069371:layer:imagick-{version}:{layerVersion}`.

Replace the values in curly braces with the desired values:

- `{region}` - the AWS region (e.g. `eu-central-1`)
- `{version}` - the php layer version (`72`, `73`, `74`)
- `{layerVersion}` - the internal layer version, see file [`config.json`](config.json) for the latest version for each region
