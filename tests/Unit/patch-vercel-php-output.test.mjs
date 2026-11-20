import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { createRequire } from 'node:module';
import test from 'node:test';

import { patchOutput } from '../../scripts/patch-vercel-php-output.mjs';

for (const mapped of [false, true]) {
    test(`patches ${mapped ? 'mapped' : 'physical'} launcher`, async () => {
        const root = fs.mkdtempSync(path.join(os.tmpdir(), 'optibus-vercel-'));
        try {
            const directory = path.join(root, 'functions', 'api', 'index.func');
            fs.mkdirSync(directory, { recursive: true });
            const original = 'exports.launcher = async (event) => event;\n';
            const config = {
                handler: 'launcher.launcher',
                runtime: 'nodejs22.x',
            };
            if (mapped) {
                const source = path.join(root, 'original-launcher.js');
                fs.writeFileSync(source, original);
                config.filePathMap = { 'launcher.js': source };
            } else {
                fs.writeFileSync(path.join(directory, 'launcher.js'), original);
            }
            fs.writeFileSync(
                path.join(directory, '.vc-config.json'),
                JSON.stringify(config),
            );
            patchOutput(root);

            const updated = JSON.parse(
                fs.readFileSync(path.join(directory, '.vc-config.json')),
            );
            assert.equal(updated.handler, 'launcher.js');
            assert.equal(updated.launcherType, 'Nodejs');
            assert.equal(updated.awsLambdaHandler, 'launcher.launcher');
            if (mapped) {
                assert.equal(
                    updated.filePathMap['launcher-orig.js'],
                    config.filePathMap['launcher.js'],
                );
                assert.equal(updated.filePathMap['launcher.js'], undefined);
            } else {
                assert.equal(
                    fs.readFileSync(
                        path.join(directory, 'launcher-orig.js'),
                        'utf8',
                    ),
                    original,
                );
            }
            assert.match(
                fs.readFileSync(path.join(directory, 'launcher.js'), 'utf8'),
                /LAMBDA_TASK_ROOT/,
            );
            if (!mapped) {
                const require = createRequire(import.meta.url);
                const bridge = require(path.join(directory, 'launcher.js'));
                const request = await bridge.launcher({
                    body: [97, 61, 49],
                    headers: { host: 'optibus.id' },
                });
                assert.equal(request.body.toString(), 'a=1');
                assert.equal(request.host, 'optibus.id');
            }
        } finally {
            fs.rmSync(root, { recursive: true, force: true });
        }
    });
}
