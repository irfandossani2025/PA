import AppKit
import Foundation
import IRFANPACore

enum CommandExecutor {
    @MainActor
    static func execute(_ command: PACommand) async -> CommandResult {
        switch command.action {
        case "open_url":
            guard case let .string(urlString)? = command.payload["url"],
                  let url = URL(string: urlString), url.scheme == "https" else {
                return .failure(message: "PA sent an invalid HTTPS URL command.")
            }

            return NSWorkspace.shared.open(url)
                ? .success(message: "Opened the approved HTTPS URL.")
                : .failure(message: "macOS could not open the approved HTTPS URL.")

        case "open_path":
            guard case let .string(path)? = command.payload["path"], path.hasPrefix("/") else {
                return .failure(message: "PA sent an invalid local path command.")
            }

            return NSWorkspace.shared.open(URL(fileURLWithPath: path))
                ? .success(message: "Opened the approved Mac path.")
                : .failure(message: "macOS could not open the approved Mac path.")

        case "open_application":
            guard case let .string(name)? = command.payload["application"], !name.isEmpty else {
                return .failure(message: "PA sent an invalid application command.")
            }

            guard let applicationURL = applicationURL(named: name) else {
                return .failure(message: "The requested application is not installed on this Mac.")
            }

            return NSWorkspace.shared.open(applicationURL)
                ? .success(message: "Opened the approved application.")
                : .failure(message: "macOS could not open the approved application.")

        default:
            return .failure(message: "This version of IRFAN PA cannot complete the requested action yet.")
        }
    }

    private static func applicationURL(named name: String) -> URL? {
        let fileManager = FileManager.default
        let appName = name.hasSuffix(".app") ? name : "\(name).app"
        let directories = [
            URL(fileURLWithPath: "/Applications", isDirectory: true),
            fileManager.homeDirectoryForCurrentUser.appending(path: "Applications", directoryHint: .isDirectory),
        ]

        return directories.lazy
            .map { $0.appending(path: appName, directoryHint: .isDirectory) }
            .first(where: { fileManager.fileExists(atPath: $0.path) })
    }
}
