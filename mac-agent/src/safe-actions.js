import { execFile } from 'node:child_process';
import { realpath } from 'node:fs/promises';
import os from 'node:os';
import path from 'node:path';
import { promisify } from 'node:util';

const execFileAsync = promisify(execFile);

export async function executeApprovedCommand(command, homeDirectory = os.homedir()) {
    if (process.platform !== 'darwin') {
        throw new Error('The Mac Agent can only run on macOS.');
    }

    const safeCommand = await validateApprovedCommand(command, homeDirectory);

    await execFileAsync('open', safeCommand.arguments, {
        timeout: 15_000,
        windowsHide: true,
    });

    return safeCommand.message;
}

export async function validateApprovedCommand(command, homeDirectory = os.homedir()) {
    if (typeof command !== 'object' || command === null || !Number.isInteger(command.id) || command.id < 1) {
        throw new Error('The PA server returned an invalid command identifier.');
    }

    if (typeof command.action !== 'string' || typeof command.payload !== 'object' || command.payload === null) {
        throw new Error('The PA server returned an invalid command payload.');
    }

    if (command.action === 'open_url') {
        return validateOpenUrl(command.payload);
    }

    if (command.action === 'open_path') {
        return validateOpenPath(command.payload, homeDirectory);
    }

    if (command.action === 'open_application') {
        return validateOpenApplication(command.payload);
    }

    throw new Error(`The command action "${command.action}" is not allowed by this Mac Agent.`);
}

function validateOpenUrl(payload) {
    if (typeof payload.url !== 'string') {
        throw new Error('An open_url command requires a URL.');
    }

    let url;

    try {
        url = new URL(payload.url);
    } catch {
        throw new Error('An open_url command requires a valid HTTPS URL.');
    }

    if (url.protocol !== 'https:' || url.username !== '' || url.password !== '') {
        throw new Error('The Mac Agent only opens HTTPS URLs without credentials.');
    }

    return {
        arguments: [url.toString()],
        message: 'Opened the approved HTTPS URL.',
    };
}

async function validateOpenPath(payload, homeDirectory) {
    if (typeof payload.path !== 'string' || !path.isAbsolute(payload.path)) {
        throw new Error('An open_path command requires an absolute path inside this Mac user folder.');
    }

    const resolvedHomeDirectory = await realpath(homeDirectory);
    const resolvedPath = await realpath(payload.path);

    if (resolvedPath !== resolvedHomeDirectory && !resolvedPath.startsWith(`${resolvedHomeDirectory}${path.sep}`)) {
        throw new Error('The Mac Agent only opens paths inside this Mac user folder.');
    }

    return {
        arguments: [resolvedPath],
        message: 'Opened the approved file or folder.',
    };
}

function validateOpenApplication(payload) {
    if (typeof payload.application !== 'string' || !/^[\p{L}\p{N} .'-]{1,100}$/u.test(payload.application)) {
        throw new Error('An open_application command requires a simple application name.');
    }

    return {
        arguments: ['-a', payload.application],
        message: 'Opened the approved application.',
    };
}
