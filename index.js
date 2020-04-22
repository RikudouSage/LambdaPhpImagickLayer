'use strict';

class ServerlessPlugin {
    constructor(serverless, options) {
        const fs = require('fs');
        const path = require('path');
        const filename = path.resolve(__dirname, 'config.json');
        const config = JSON.parse(fs.readFileSync(filename));

        const original = serverless.variables.getValueFromSource.bind(serverless.variables);
        serverless.variables.getValueFromSource = (variableString) => {
            if (variableString.startsWith('rikudou:')) {
                const region = serverless.getProvider('aws').getRegion();
                const layer = variableString.substr('rikudou:'.length);
                const version = layer.substr(`${config.awsLayerName}-`.length);
                if (!config.hasOwnProperty(version)) {
                    throw `Unknown version '${version}' of php-imagick layer`;
                }
                if (!config[version].hasOwnProperty(region)) {
                    throw `This plugin does not support the '${region}' region`;
                }
                const layerVersion = config[version][region];
                return `arn:aws:lambda:${region}:${config.awsAccountId}:layer:${layer}:${layerVersion}`;
            }

            return original(variableString);
        }
    }
}

module.exports = ServerlessPlugin;
