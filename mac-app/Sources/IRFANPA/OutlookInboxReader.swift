import Foundation

enum OutlookInboxReader {
    static func unreadMessages(limit: Int) async -> Result<String, OutlookInboxReaderError> {
        guard (1...20).contains(limit) else {
            return .failure(.invalidLimit)
        }

        do {
            let output = try await runAppleScript(limit: limit)
            let normalizedOutput = output.trimmingCharacters(in: .whitespacesAndNewlines)

            guard !normalizedOutput.isEmpty else {
                return .failure(.emptyResponse)
            }

            return .success(String(normalizedOutput.prefix(12_000)))
        } catch {
            return .failure(.automationUnavailable)
        }
    }

    private static func runAppleScript(limit: Int) async throws -> String {
        let script = """
        on clippedText(sourceText, characterLimit)
            if sourceText is missing value then return ""
            set normalizedText to sourceText as text
            if (count characters of normalizedText) > characterLimit then
                return text 1 thru characterLimit of normalizedText
            end if
            return normalizedText
        end clippedText

        tell application "Microsoft Outlook"
            set unreadMessages to every message of inbox whose is read is false
            set messageCount to count of unreadMessages
            if messageCount is 0 then return "NO_UNREAD_MESSAGES"

            set maximumMessages to min(messageCount, (limit))
            set reportText to ""

            repeat with messageIndex from 1 to maximumMessages
                set inboxMessage to item messageIndex of unreadMessages
                set subjectText to clippedText(subject of inboxMessage, 500)
                set senderText to clippedText(sender of inboxMessage, 500)
                set receivedText to (time received of inboxMessage) as text
                set bodyText to clippedText(plain text content of inboxMessage, 3500)
                set reportText to reportText & "MESSAGE " & messageIndex & linefeed & "FROM: " & senderText & linefeed & "SUBJECT: " & subjectText & linefeed & "RECEIVED: " & receivedText & linefeed & "BODY:" & linefeed & bodyText & linefeed & "---" & linefeed
            end repeat

            return reportText
        end tell
        """

        return try await withCheckedThrowingContinuation { continuation in
            let process = Process()
            let outputPipe = Pipe()
            let errorPipe = Pipe()

            process.executableURL = URL(fileURLWithPath: "/usr/bin/osascript")
            process.arguments = ["-l", "AppleScript", "-e", script]
            process.standardOutput = outputPipe
            process.standardError = errorPipe
            process.terminationHandler = { completedProcess in
                let output = outputPipe.fileHandleForReading.readDataToEndOfFile()

                guard completedProcess.terminationStatus == 0,
                      let text = String(data: output, encoding: .utf8) else {
                    continuation.resume(throwing: OutlookInboxReaderError.automationUnavailable)

                    return
                }

                continuation.resume(returning: text)
            }

            do {
                try process.run()
            } catch {
                continuation.resume(throwing: error)
            }
        }
    }
}

enum OutlookInboxReaderError: LocalizedError {
    case automationUnavailable
    case emptyResponse
    case invalidLimit

    var errorDescription: String? {
        switch self {
        case .automationUnavailable:
            return "IRFAN PA needs permission to read Outlook. Allow the macOS automation prompt, then try again."
        case .emptyResponse:
            return "Outlook returned no readable Inbox content."
        case .invalidLimit:
            return "PA sent an invalid Outlook Inbox limit."
        }
    }
}
