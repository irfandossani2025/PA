import IRFANPACore
import XCTest

final class PAClientTests: XCTestCase {
    func test_accepts_valid_https_server_and_pairing_token() throws {
        let credentials = try PACredentials(
            serverURL: "https://pa.irfandossani.online",
            token: "pa_mac_aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa"
        )

        XCTAssertEqual(credentials.serverURL.host, "pa.irfandossani.online")
    }

    func test_rejects_non_https_server() {
        XCTAssertThrowsError(try PACredentials(
            serverURL: "http://pa.irfandossani.online",
            token: "pa_mac_aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa"
        ))
    }

    func test_rejects_invalid_pairing_token() {
        XCTAssertThrowsError(try PACredentials(
            serverURL: "https://pa.irfandossani.online",
            token: "not-a-pairing-token"
        ))
    }
}
