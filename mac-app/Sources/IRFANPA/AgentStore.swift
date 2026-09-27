import AppKit
import Combine
import Foundation
import IRFANPACore
import ServiceManagement
import SwiftUI

@MainActor
final class AgentStore: ObservableObject {
    struct ChatMessage: Identifiable {
        let id = UUID()
        let role: String
        let content: String
    }
    enum Status: Equatable {
        case disconnected
        case connecting
        case connected
        case working
        case error

        var label: String {
            switch self {
            case .disconnected: return "Not paired"
            case .connecting: return "Connecting"
            case .connected: return "Connected"
            case .working: return "Working"
            case .error: return "Needs attention"
            }
        }
    }

    @Published private(set) var lastHeartbeat: Date?
    @Published private(set) var status: Status = .disconnected
    @Published private(set) var statusMessage = "Pair this Mac with your PA portal to begin."
    @Published private(set) var chatMessages: [ChatMessage] = []
    @Published private(set) var isSendingChat = false
    @Published private(set) var activeSpecialist: String?

    private let credentialStore = CredentialStore()
    private var heartbeatTimer: Timer?

    init() {
        if credentialStore.importLegacyConfigurationIfAvailable() != nil {
            status = .connecting
            statusMessage = "Starting secure connection with the existing Mac pairing…"
            startHeartbeatTimer()
            Task { await heartbeatNow() }
        }
    }

    var isPaired: Bool { credentialStore.load() != nil }
    var serverURL: String? { credentialStore.load()?.serverURL.absoluteString }
    var menuBarSymbol: String {
        switch status {
        case .connected: return "checkmark.circle.fill"
        case .working, .connecting: return "arrow.triangle.2.circlepath.circle.fill"
        case .error: return "exclamationmark.triangle.fill"
        case .disconnected: return "circle.dashed"
        }
    }
    var statusColor: Color {
        switch status {
        case .connected: return .green
        case .working, .connecting: return .blue
        case .error: return .orange
        case .disconnected: return .secondary
        }
    }

    var launchAtLoginEnabled: Bool {
        SMAppService.mainApp.status == .enabled
    }

    func setLaunchAtLogin(enabled: Bool) {
        do {
            if enabled {
                try SMAppService.mainApp.register()
            } else {
                try SMAppService.mainApp.unregister()
            }
        } catch {
            status = .error
            statusMessage = "Could not update launch-at-login: \(error.localizedDescription)"
        }
    }

    func pair(serverURL: String, token: String) throws {
        let credentials = try PACredentials(serverURL: serverURL, token: token)
        try credentialStore.save(credentials)
        status = .connecting
        statusMessage = "Mac paired. Checking in with PA…"
        startHeartbeatTimer()
    }

    func disconnect() {
        credentialStore.delete()
        heartbeatTimer?.invalidate()
        heartbeatTimer = nil
        status = .disconnected
        statusMessage = "This Mac is no longer paired with PA."
        lastHeartbeat = nil
    }

    func heartbeatNow() async {
        guard let credentials = credentialStore.load() else {
            return
        }

        status = .connecting

        do {
            let client = PAClient(credentials: credentials)
            let response = try await client.heartbeat()
            lastHeartbeat = .now

            if response.commands.isEmpty {
                status = .connected
                statusMessage = "Securely connected. No approved Mac work is waiting."
                return
            }

            status = .working
            statusMessage = "Completing \(response.commands.count) approved Mac task\(response.commands.count == 1 ? "" : "s")."

            for command in response.commands {
                activeSpecialist = Self.specialist(for: command.action)
                let result = await CommandExecutor.execute(command)
                try await client.complete(commandID: command.id, result: result)
            }

            activeSpecialist = nil
            status = .connected
            statusMessage = "Approved Mac work completed."
        } catch {
            activeSpecialist = nil
            status = .error
            statusMessage = Self.safeErrorMessage(error)
        }
    }

    func sendChat(_ message: String) async {
        let trimmedMessage = message.trimmingCharacters(in: .whitespacesAndNewlines)
        guard !trimmedMessage.isEmpty, let credentials = credentialStore.load() else { return }

        chatMessages.append(.init(role: "You", content: trimmedMessage))
        isSendingChat = true
        defer { isSendingChat = false }

        do {
            let response = try await PAClient(credentials: credentials).chat(message: trimmedMessage)
            chatMessages.append(.init(role: "PA Manager", content: response.reply))
            await heartbeatNow()
        } catch {
            chatMessages.append(.init(role: "PA Manager", content: Self.safeErrorMessage(error)))
        }
    }

    private func startHeartbeatTimer() {
        heartbeatTimer?.invalidate()
        heartbeatTimer = Timer.scheduledTimer(withTimeInterval: 30, repeats: true) { [weak self] _ in
            Task { @MainActor [weak self] in
                await self?.heartbeatNow()
            }
        }
    }

    private static func safeErrorMessage(_ error: Error) -> String {
        let message = error.localizedDescription.replacingOccurrences(of: "\n", with: " ")
        return "Connection needs attention: \(String(message.prefix(180)))"
    }

    private static func specialist(for action: String) -> String {
        switch action {
        case "inspect_outlook_inbox":
            return "Executive Assistant"
        case "open_url":
            return "Research"
        case "open_application", "open_path":
            return "Full-Stack Developer"
        default:
            return "PA Manager"
        }
    }
}
