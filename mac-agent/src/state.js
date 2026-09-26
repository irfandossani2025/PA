import { mkdir, readFile, rename, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const agentDirectory = path.dirname(fileURLToPath(import.meta.url));
const stateDirectory = path.resolve(agentDirectory, '..', 'data');
const statePath = path.join(stateDirectory, 'agent-state.json');

export async function loadReceipts() {
    try {
        const state = JSON.parse(await readFile(statePath, 'utf8'));

        return Array.isArray(state.receipts) ? state.receipts : [];
    } catch (error) {
        if (error.code === 'ENOENT') {
            return [];
        }

        throw new Error(`Unable to load Mac Agent delivery receipts: ${error.message}`);
    }
}

export async function saveReceipts(receipts) {
    await mkdir(stateDirectory, { recursive: true, mode: 0o700 });

    const temporaryPath = `${statePath}.tmp`;
    await writeFile(temporaryPath, `${JSON.stringify({ receipts }, null, 2)}\n`, { mode: 0o600 });
    await rename(temporaryPath, statePath);
}
