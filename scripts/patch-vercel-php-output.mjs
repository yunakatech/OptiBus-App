import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

export function patchOutput(outputDirectory) {
    const output = path.resolve(outputDirectory);
    const functions = path.join(output, 'functions');
    const candidates = [];
    function findFunctions(directory) {
        for (const entry of fs.readdirSync(directory, {
            withFileTypes: true,
        })) {
            if (!entry.isDirectory()) {
continue;
}

            const child = path.join(directory, entry.name);

            if (entry.name.endsWith('.func')) {
candidates.push(child);
} else {
findFunctions(child);
}
        }
    }

    if (fs.existsSync(functions)) {
findFunctions(functions);
}

    if (candidates.length !== 1) {
        throw new Error(
            `Expected one PHP function in ${functions}; found ${candidates.length}`,
        );
    }

    const directory = candidates[0];
    const configPath = path.join(directory, '.vc-config.json');
    const config = JSON.parse(fs.readFileSync(configPath, 'utf8'));

    if (config.handler !== 'launcher.launcher') {
        throw new Error(`Unexpected function handler: ${config.handler}`);
    }

    const launcher = path.join(directory, 'launcher.js');
    const original = path.join(directory, 'launcher-orig.js');

    if (fs.existsSync(launcher)) {
        fs.renameSync(launcher, original);
    } else if (config.filePathMap?.['launcher.js']) {
        config.filePathMap['launcher-orig.js'] =
            config.filePathMap['launcher.js'];
        delete config.filePathMap['launcher.js'];
    } else {
        throw new Error('Original launcher.js is missing from function output');
    }

    const bridge = `process.env.LAMBDA_TASK_ROOT ||= '/var/task';
const original = require('./launcher-orig.js');
exports.launcher = async (event, context) => {
    const request = { ...event };
    if (Array.isArray(request.body)) {
        request.body = Buffer.from(request.body);
    } else if (typeof request.body === 'string') {
        request.body = Buffer.from(request.body, request.isBase64Encoded ? 'base64' : 'utf8');
    } else {
        request.body = undefined;
    }
    const headers = request.headers || {};
    request.host ||= headers['x-forwarded-host'] || headers.host;
    return original.launcher(request, context);
};
`;
    fs.writeFileSync(launcher, bridge);
    config.handler = 'launcher.js';
    config.launcherType = 'Nodejs';
    config.awsLambdaHandler = 'launcher.launcher';
    fs.writeFileSync(configPath, `${JSON.stringify(config, null, 2)}\n`);
    console.log(`Patched ${path.relative(output, directory)}`);
}

if (
    process.argv[1] &&
    path.resolve(process.argv[1]) === fileURLToPath(import.meta.url)
) {
    patchOutput(process.argv[2] ?? '.vercel/output');
}
