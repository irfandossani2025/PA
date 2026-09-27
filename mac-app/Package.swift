// swift-tools-version: 5.8
import PackageDescription

let package = Package(
    name: "IRFANPA",
    platforms: [.macOS(.v13)],
    products: [
        .executable(name: "IRFANPA", targets: ["IRFANPA"]),
    ],
    targets: [
        .target(name: "IRFANPACore"),
        .executableTarget(name: "IRFANPA", dependencies: ["IRFANPACore"]),
        .testTarget(name: "IRFANPATests", dependencies: ["IRFANPACore"]),
    ]
)
