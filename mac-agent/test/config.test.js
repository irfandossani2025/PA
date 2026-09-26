import assert from 'node:assert/strict';
import test from 'node:test';
import { validateConfig } from '../src/config.js';

test('accepts a secure PA server configuration', () => {
    const config = validateConfig({
        intervalSeconds: 60,
        serverUrl: 'https://pa.irfandossani.online/path-that-is-not-used',
        token: `pa_mac_${'a'.repeat(64)}`,
    });

    assert.deepEqual(config, {
        intervalSeconds: 60,
        serverUrl: 'https://pa.irfandossani.online',
        token: `pa_mac_${'a'.repeat(64)}`,
    });
});

test('rejects an insecure PA server configuration', () => {
    assert.throws(
        () => validateConfig({
            serverUrl: 'http://pa.irfandossani.online',
            token: `pa_mac_${'a'.repeat(64)}`,
        }),
        /HTTPS URL/,
    );
});
