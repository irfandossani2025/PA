import { readFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const agentDirectory = path.dirname(fileURLToPath(import.meta.url));
const defaultConfigPath = path.resolve(agentDirectory, '..', 'config.json');

export async function loadConfig(configPath = defaultConfigPath) {
    let parsed;

    try {
        parsed = JSON.parse(await readFile(configPath, 'utf8'));
    } catch (error) {
        throw new Error(`Unable to load Mac Agent configuration from ${configPath}: ${error.message}`);
    }

    return validateConfig(parsed);
}

export function validateConfig(config) {
    if (typeof config !== 'object' || config === null || Array.isArray(config)) {
        throw new Error('Mac Agent configuration must be a JSON object.');
    }

    if (typeof config.serverUrl !== 'string') {
        throw new Error('Mac Agent configuration requires a serverUrl.');
    }

    let serverUrl;

    try {
        serverUrl = new URL(config.serverUrl);
    } catch {
        throw new Error('Mac Agent serverUrl must be a valid HTTPS URL.');
    }

    if (serverUrl.protocol !== 'https:' || serverUrl.username !== '' || serverUrl.password !== '') {
        throw new Error('Mac Agent serverUrl must be an HTTPS URL without credentials.');
    }

    if (typeof config.token !== 'string' || !/^pa_mac_[A-Za-z0-9]{64}$/.test(config.token)) {
        throw new Error('Mac Agent configuration requires a valid pairing token.');
    }

    const intervalSeconds = config.intervalSeconds ?? 60;

    if (!Number.isInteger(intervalSeconds) || intervalSeconds < 30 || intervalSeconds > 3600) {
        throw new Error('Mac Agent intervalSeconds must be an integer between 30 and 3600.');
    }

    return {
        intervalSeconds,
        serverUrl: serverUrl.origin,
        token: config.token,
    };
}
