import Foundation
import IRFANPACore
import Security

final class CredentialStore {
    private let service = "online.irfandossani.pa.mac-agent"
    private let account = "paired-mac"

    func save(_ credentials: PACredentials) throws {
        let data = try JSONEncoder().encode(StoredCredentials(serverURL: credentials.serverURL.absoluteString, token: credentials.token))
        delete()

        let status = SecItemAdd([
            kSecClass: kSecClassGenericPassword,
            kSecAttrService: service,
            kSecAttrAccount: account,
            kSecValueData: data,
            kSecAttrAccessible: kSecAttrAccessibleAfterFirstUnlock,
        ] as CFDictionary, nil)

        guard status == errSecSuccess else {
            throw KeychainError.unavailable(status)
        }
    }

    func load() -> PACredentials? {
        var result: CFTypeRef?
        let status = SecItemCopyMatching([
            kSecClass: kSecClassGenericPassword,
            kSecAttrService: service,
            kSecAttrAccount: account,
            kSecReturnData: true,
            kSecMatchLimit: kSecMatchLimitOne,
        ] as CFDictionary, &result)

        guard status == errSecSuccess, let data = result as? Data,
              let stored = try? JSONDecoder().decode(StoredCredentials.self, from: data),
              let credentials = try? PACredentials(serverURL: stored.serverURL, token: stored.token) else {
            return nil
        }

        return credentials
    }

    func delete() {
        SecItemDelete([
            kSecClass: kSecClassGenericPassword,
            kSecAttrService: service,
            kSecAttrAccount: account,
        ] as CFDictionary)
    }

    func importLegacyConfigurationIfAvailable() -> PACredentials? {
        guard load() == nil else {
            return load()
        }

        let legacyConfigurationURL = FileManager.default.homeDirectoryForCurrentUser
            .appending(path: "Projects/PA/mac-agent/config.json")

        guard let data = try? Data(contentsOf: legacyConfigurationURL),
              let configuration = try? JSONDecoder().decode(LegacyAgentConfiguration.self, from: data),
              let credentials = try? PACredentials(serverURL: configuration.serverURL, token: configuration.token)
        else {
            return nil
        }

        do {
            try save(credentials)
            return credentials
        } catch {
            return nil
        }
    }
}

private struct StoredCredentials: Codable {
    let serverURL: String
    let token: String
}

private struct LegacyAgentConfiguration: Decodable {
    let serverURL: String
    let token: String

    enum CodingKeys: String, CodingKey {
        case serverURL = "serverUrl"
        case token
    }
}

private enum KeychainError: LocalizedError {
    case unavailable(OSStatus)

    var errorDescription: String? {
        switch self {
        case let .unavailable(status): return "macOS Keychain is unavailable (\(status))."
        }
    }
}
