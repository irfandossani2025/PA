import AppKit
import ServiceManagement
import SwiftUI

@main
struct IRFANPAApp: App {
    @StateObject private var agent = AgentStore()

    var body: some Scene {
        MenuBarExtra {
            PAStatusView(agent: agent)
        } label: {
            Image(systemName: agent.menuBarSymbol)
        }
        .menuBarExtraStyle(.window)

        Settings {
            PASettingsView(agent: agent)
        }
    }
}

private struct PAStatusView: View {
    @ObservedObject var agent: AgentStore

    var body: some View {
        VStack(alignment: .leading, spacing: 14) {
            HStack {
                Image(systemName: agent.menuBarSymbol)
                    .foregroundStyle(agent.statusColor)
                Text("IRFAN PA")
                    .font(.headline)
                Spacer()
                Text(agent.status.label)
                    .font(.caption)
                    .foregroundStyle(agent.statusColor)
            }

            Text(agent.statusMessage)
                .font(.caption)
                .foregroundStyle(.secondary)

            if let heartbeat = agent.lastHeartbeat {
                Text("Last check-in: \(heartbeat.formatted(date: .omitted, time: .shortened))")
                    .font(.caption2)
                    .foregroundStyle(.secondary)
            }

            Divider()

            Button("Check in now") {
                Task { await agent.heartbeatNow() }
            }
            .disabled(!agent.isPaired || agent.status == .working)

            Button("Open settings…") {
                NSApp.activate(ignoringOtherApps: true)
                SettingsWindowController.shared.show(agent: agent)
            }

            Button("Quit IRFAN PA") {
                NSApp.terminate(nil)
            }
        }
        .padding()
        .frame(width: 320)
    }
}

private struct PASettingsView: View {
    @ObservedObject var agent: AgentStore
    @State private var serverURL = "https://pa.irfandossani.online"
    @State private var pairingToken = ""
    @State private var errorMessage: String?

    var body: some View {
        Form {
            Section("Connection") {
                TextField("PA server", text: $serverURL)
                    .textFieldStyle(.roundedBorder)
                    .disabled(agent.isPaired)

                SecureField("Mac pairing token", text: $pairingToken)
                    .textFieldStyle(.roundedBorder)
                    .disabled(agent.isPaired)

                HStack {
                    Button(agent.isPaired ? "Paired" : "Pair this Mac") {
                        do {
                            try agent.pair(serverURL: serverURL, token: pairingToken)
                            pairingToken = ""
                            errorMessage = nil
                            Task { await agent.heartbeatNow() }
                        } catch {
                            errorMessage = error.localizedDescription
                        }
                    }
                    .disabled(agent.isPaired)

                    if agent.isPaired {
                        Button("Disconnect this Mac", role: .destructive) {
                            agent.disconnect()
                        }
                    }
                }

                if let errorMessage {
                    Text(errorMessage)
                        .font(.caption)
                        .foregroundStyle(.red)
                }
            }

            Section("Mac permissions") {
                Text("IRFAN PA needs Accessibility permission to control approved desktop apps and Screen Recording permission to understand visible app content.")
                    .font(.caption)
                    .foregroundStyle(.secondary)

                Button("Open Accessibility settings") {
                    NSWorkspace.shared.open(URL(string: "x-apple.systempreferences:com.apple.preference.security?Privacy_Accessibility")!)
                }

                Button("Open Screen Recording settings") {
                    NSWorkspace.shared.open(URL(string: "x-apple.systempreferences:com.apple.preference.security?Privacy_ScreenCapture")!)
                }
            }

            Section("Reliability") {
                Toggle("Start IRFAN PA when I log in", isOn: Binding(
                    get: { agent.launchAtLoginEnabled },
                    set: { agent.setLaunchAtLogin(enabled: $0) }
                ))

                Text("The app checks in securely every 30 seconds while your Mac is awake and logged in. No inbound port is opened on your Mac.")
                    .font(.caption)
                    .foregroundStyle(.secondary)
            }
        }
        .formStyle(.grouped)
        .padding()
        .frame(width: 520)
        .onAppear {
            serverURL = agent.serverURL ?? serverURL
        }
    }
}

private final class SettingsWindowController {
    static let shared = SettingsWindowController()

    func show(agent: AgentStore) {
        NSApp.sendAction(Selector(("showSettingsWindow:")), to: nil, from: nil)
    }
}
