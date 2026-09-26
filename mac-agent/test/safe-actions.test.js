import assert from 'node:assert/strict';
import test from 'node:test';
import { validateApprovedCommand } from '../src/safe-actions.js';

test('accepts an approved HTTPS URL command without credentials', async () => {
    const command = await validateApprovedCommand({
        action: 'open_url',
        id: 1,
        payload: { url: 'https://example.com/work' },
    });

    assert.deepEqual(command, {
        arguments: ['https://example.com/work'],
        message: 'Opened the approved HTTPS URL.',
    });
});

test('rejects a URL command that uses an insecure protocol', async () => {
    await assert.rejects(
        () => validateApprovedCommand({
            action: 'open_url',
            id: 1,
            payload: { url: 'http://example.com' },
        }),
        /only opens HTTPS URLs/,
    );
});

test('rejects command actions outside the explicit safe allow-list', async () => {
    await assert.rejects(
        () => validateApprovedCommand({
            action: 'delete_file',
            id: 1,
            payload: {},
        }),
        /not allowed/,
    );
});
