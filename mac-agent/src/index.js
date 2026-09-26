import os from 'node:os';
import process from 'node:process';
import { loadConfig } from './config.js';
import { PaClient } from './pa-client.js';
import { executeApprovedCommand } from './safe-actions.js';
import { loadReceipts, saveReceipts } from './state.js';

const agentVersion = '0.1.0';
const runOnce = process.argv.includes('--once');

async function run(config) {
    const pa = new PaClient(config);

    await flushReceipts(pa);

    const response = await pa.heartbeat({
        agent_version: agentVersion,
        hostname: os.hostname(),
        platform: 'macOS',
    });

    if (!Array.isArray(response.commands)) {
        throw new Error('The PA server returned an invalid heartbeat response.');
    }

    const commandCount = response.commands.length;

    for (const command of response.commands) {
        await executeAndQueueReceipt(command);
    }

    await flushReceipts(pa);

    console.log(`PA heartbeat completed at ${new Date().toISOString()}. Approved commands received: ${commandCount}.`);
}

async function executeAndQueueReceipt(command) {
    let receipt;

    try {
        receipt = {
            commandId: command.id,
            result: await executeApprovedCommand(command),
            success: true,
        };
    } catch (error) {
        receipt = {
            commandId: command?.id,
            result: { message: sanitizeError(error) },
            success: false,
        };
    }

    if (!Number.isInteger(receipt.commandId) || receipt.commandId < 1) {
        throw new Error('The PA server returned a command without a valid identifier.');
    }

    const receipts = await loadReceipts();
    receipts.push(receipt);
    await saveReceipts(receipts);
}

async function flushReceipts(pa) {
    const receipts = await loadReceipts();
    const undeliveredReceipts = [];

    for (const receipt of receipts) {
        try {
            await pa.completeCommand(receipt.commandId, receipt.success, receipt.result);
        } catch (error) {
            console.error(`Could not deliver completion receipt for command ${receipt.commandId}: ${sanitizeError(error)}`);
            undeliveredReceipts.push(receipt);
        }
    }

    if (undeliveredReceipts.length !== receipts.length) {
        await saveReceipts(undeliveredReceipts);
    }
}

function sanitizeError(error) {
    const message = error instanceof Error ? error.message : 'The command failed.';

    return message.replace(/[\r\n]+/g, ' ').slice(0, 500);
}

async function main(config) {
    try {
        await run(config);
    } catch (error) {
        console.error(`PA Mac Agent stopped: ${sanitizeError(error)}`);

        if (runOnce) {
            process.exitCode = 1;
        }
    }
}

async function bootstrap() {
    let config;

    try {
        config = await loadConfig();
    } catch (error) {
        console.error(`PA Mac Agent could not start: ${sanitizeError(error)}`);
        process.exitCode = 1;

        return;
    }

    await main(config);

    if (!runOnce) {
        setInterval(() => {
            void main(config);
        }, config.intervalSeconds * 1_000);
    }
}

await bootstrap();
