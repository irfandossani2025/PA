import Foundation

public struct PACredentials: Equatable {
    public let serverURL: URL
    public let token: String

    public init(serverURL: String, token: String) throws {
        guard let url = URL(string: serverURL), url.scheme == "https", url.user == nil, url.password == nil, url.host != nil else {
            throw PAClientError.invalidServerURL
        }

        guard token.range(of: "^pa_mac_[A-Za-z0-9]{64}$", options: .regularExpression) != nil else {
            throw PAClientError.invalidPairingToken
        }

        self.serverURL = url
        self.token = token
    }
}

public struct HeartbeatResponse: Decodable {
    public let commands: [PACommand]
}

public struct PACommand: Decodable, Identifiable {
    public let id: Int
    public let action: String
    public let payload: [String: JSONValue]
}

public enum CommandResult {
    case success(message: String)
    case failure(message: String)

    public var success: Bool {
        if case .success = self { return true }
        return false
    }

    public var message: String {
        switch self {
        case let .success(message), let .failure(message): return message
        }
    }
}

public enum PAClientError: LocalizedError {
    case invalidServerURL
    case invalidPairingToken
    case invalidResponse
    case requestFailed(Int)

    public var errorDescription: String? {
        switch self {
        case .invalidServerURL: return "Enter a valid HTTPS PA server address."
        case .invalidPairingToken: return "Enter a valid Mac pairing token."
        case .invalidResponse: return "PA returned an invalid response."
        case let .requestFailed(status): return "PA server request failed (HTTP \(status))."
        }
    }
}

public struct PAClient {
    private let credentials: PACredentials
    private let session: URLSession

    public init(credentials: PACredentials, session: URLSession = .shared) {
        self.credentials = credentials
        self.session = session
    }

    public func heartbeat() async throws -> HeartbeatResponse {
        var request = try request(path: "/api/mac-agent/heartbeat")
        request.httpMethod = "POST"
        request.httpBody = try JSONEncoder().encode([
            "status": [
                "agent_version": "1.0.0",
                "hostname": Host.current().localizedName ?? "Irfan-Mac",
                "platform": "macOS",
            ],
        ])

        let (data, response) = try await session.data(for: request)
        try validate(response)
        return try JSONDecoder().decode(HeartbeatResponse.self, from: data)
    }

    public func complete(commandID: Int, result: CommandResult) async throws {
        var request = try request(path: "/api/mac-agent/commands/\(commandID)/complete")
        request.httpMethod = "POST"
        request.httpBody = try JSONEncoder().encode(CommandCompletionRequest(
            success: result.success,
            result: .init(message: result.message)
        ))

        let (_, response) = try await session.data(for: request)
        try validate(response)
    }

    private func request(path: String) throws -> URLRequest {
        guard let url = URL(string: path, relativeTo: credentials.serverURL) else {
            throw PAClientError.invalidServerURL
        }

        var request = URLRequest(url: url)
        request.timeoutInterval = 15
        request.setValue("Bearer \(credentials.token)", forHTTPHeaderField: "Authorization")
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        return request
    }

    private func validate(_ response: URLResponse) throws {
        guard let httpResponse = response as? HTTPURLResponse else {
            throw PAClientError.invalidResponse
        }

        guard (200...299).contains(httpResponse.statusCode) else {
            throw PAClientError.requestFailed(httpResponse.statusCode)
        }
    }
}

private struct CommandCompletionRequest: Encodable {
    let success: Bool
    let result: CommandCompletionResult
}

private struct CommandCompletionResult: Encodable {
    let message: String
}

public enum JSONValue: Decodable {
    case string(String)
    case integer(Int)
    case bool(Bool)
    case null

    public init(from decoder: Decoder) throws {
        let container = try decoder.singleValueContainer()
        if container.decodeNil() { self = .null }
        else if let value = try? container.decode(String.self) { self = .string(value) }
        else if let value = try? container.decode(Int.self) { self = .integer(value) }
        else if let value = try? container.decode(Bool.self) { self = .bool(value) }
        else { throw DecodingError.typeMismatch(JSONValue.self, .init(codingPath: decoder.codingPath, debugDescription: "Unsupported JSON value.")) }
    }
}
