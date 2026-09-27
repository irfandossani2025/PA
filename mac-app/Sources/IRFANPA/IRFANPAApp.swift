import AppKit
import ServiceManagement
import SwiftUI

@main
struct IRFANPAApp: App {
    @StateObject private var agent = AgentStore()

    var body: some Scene {
        WindowGroup("IRFAN PA") {
            PADashboardView(agent: agent)
        }
        .defaultSize(width: 620, height: 520)

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

private struct PADashboardView: View {
    @ObservedObject var agent: AgentStore
    @State private var selectedSection = Section.command

    private enum Section: String, CaseIterable, Identifiable {
        case command = "Command Center"
        case office = "Virtual Office"

        var id: Self { self }
    }

    var body: some View {
        VStack(alignment: .leading, spacing: 0) {
            HStack(spacing: 14) {
                Image(systemName: agent.menuBarSymbol)
                    .font(.system(size: 30, weight: .semibold))
                    .foregroundStyle(agent.statusColor)
                    .frame(width: 56, height: 56)
                    .background(agent.statusColor.opacity(0.12), in: RoundedRectangle(cornerRadius: 16))

                VStack(alignment: .leading, spacing: 3) {
                    Text("IRFAN PA")
                        .font(.title2.weight(.bold))
                    Text("Your secure Mac work companion")
                        .font(.subheadline)
                        .foregroundStyle(.secondary)
                }

                Spacer()

                Text(agent.status.label)
                    .font(.subheadline.weight(.semibold))
                    .foregroundStyle(agent.statusColor)
                    .padding(.horizontal, 12)
                    .padding(.vertical, 7)
                    .background(agent.statusColor.opacity(0.12), in: Capsule())
            }
            .padding(28)

            Divider()

            Picker("Workspace", selection: $selectedSection) {
                ForEach(Section.allCases) { section in
                    Text(section.rawValue).tag(section)
                }
            }
            .pickerStyle(.segmented)
            .padding(.horizontal, 28)
            .padding(.top, 18)

            if selectedSection == .command {
                commandCenter
            } else {
                VirtualOfficeView(isWorking: agent.status == .working || agent.status == .connecting)
            }
        }
        .frame(minWidth: 720, minHeight: 580)
    }

    private var commandCenter: some View {
        VStack(alignment: .leading, spacing: 18) {
                NativeChatView(agent: agent)

                Label(agent.statusMessage, systemImage: "lock.shield")
                    .font(.body)

                if let heartbeat = agent.lastHeartbeat {
                    Label("Last check-in: \(heartbeat.formatted(date: .abbreviated, time: .shortened))", systemImage: "clock")
                        .font(.subheadline)
                        .foregroundStyle(.secondary)
                }

                HStack(spacing: 12) {
                    Button {
                        Task { await agent.heartbeatNow() }
                    } label: {
                        Label("Check in now", systemImage: "arrow.clockwise")
                    }
                    .buttonStyle(.borderedProminent)
                    .disabled(!agent.isPaired || agent.status == .working)

                    Button {
                        NSApp.sendAction(Selector(("showSettingsWindow:")), to: nil, from: nil)
                    } label: {
                        Label("Settings", systemImage: "gearshape")
                    }
                    .buttonStyle(.bordered)
                }

                Spacer()

                Text("IRFAN PA connects outward to your PA server over HTTPS. Your Mac remains protected behind its normal firewall.")
                    .font(.caption)
                    .foregroundStyle(.secondary)
                    .fixedSize(horizontal: false, vertical: true)
        }
        .padding(28)
    }
}

private struct NativeChatView: View {
    @ObservedObject var agent: AgentStore
    @State private var message = ""

    var body: some View {
        VStack(alignment: .leading, spacing: 10) {
            Text("Talk to your PA Manager")
                .font(.headline)

            ScrollView {
                LazyVStack(alignment: .leading, spacing: 8) {
                    ForEach(agent.chatMessages) { chatMessage in
                        Text("\(chatMessage.role): \(chatMessage.content)")
                            .padding(10)
                            .frame(maxWidth: .infinity, alignment: .leading)
                            .background(chatMessage.role == "You" ? Color.blue.opacity(0.12) : Color.secondary.opacity(0.1), in: RoundedRectangle(cornerRadius: 10))
                    }
                }
            }
            .frame(minHeight: 130, maxHeight: 210)

            HStack {
                TextField("Give PA an outcome-based request…", text: $message, axis: .vertical)
                    .textFieldStyle(.roundedBorder)
                Button("Send") {
                    let request = message
                    message = ""
                    Task { await agent.sendChat(request) }
                }
                .buttonStyle(.borderedProminent)
                .disabled(message.trimmingCharacters(in: .whitespacesAndNewlines).isEmpty || agent.isSendingChat)
            }
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
