const requestTimeoutMs = 15_000;

export class PaClient {
    constructor(config) {
        this.serverUrl = config.serverUrl;
        this.token = config.token;
    }

    async heartbeat(status) {
        return this.request('/api/mac-agent/heartbeat', {
            method: 'POST',
            body: JSON.stringify({ status }),
        });
    }

    async completeCommand(commandId, success, result) {
        return this.request(`/api/mac-agent/commands/${commandId}/complete`, {
            method: 'POST',
            body: JSON.stringify({
                success,
                result,
            }),
        });
    }

    async request(path, options) {
        const response = await fetch(`${this.serverUrl}${path}`, {
            ...options,
            headers: {
                Authorization: `Bearer ${this.token}`,
                'Content-Type': 'application/json',
            },
            signal: AbortSignal.timeout(requestTimeoutMs),
        });

        if (!response.ok) {
            throw new Error(`PA server request failed with HTTP ${response.status}.`);
        }

        return response.json();
    }
}
