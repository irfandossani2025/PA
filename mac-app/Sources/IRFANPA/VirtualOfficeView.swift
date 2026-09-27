import SwiftUI

struct VirtualOfficeView: View {
    let isWorking: Bool

    private let specialists = [
        OfficeAgent(name: "Designer", symbol: "paintpalette.fill"),
        OfficeAgent(name: "Developer", symbol: "chevron.left.forwardslash.chevron.right"),
        OfficeAgent(name: "UI/UX", symbol: "square.on.square"),
        OfficeAgent(name: "SEO", symbol: "magnifyingglass"),
        OfficeAgent(name: "Marketing", symbol: "megaphone.fill"),
        OfficeAgent(name: "Sales", symbol: "chart.line.uptrend.xyaxis"),
        OfficeAgent(name: "Social", symbol: "camera.fill"),
    ]

    var body: some View {
        ScrollView {
            VStack(alignment: .leading, spacing: 16) {
                HStack {
                    VStack(alignment: .leading, spacing: 4) {
                        Text("Your virtual office")
                            .font(.title3.weight(.bold))
                        Text("You give the outcome. PA Manager assigns the work and reports it back.")
                            .font(.subheadline)
                            .foregroundStyle(.secondary)
                    }
                    Spacer()
                    Label(isWorking ? "Work in progress" : "Office ready", systemImage: isWorking ? "bolt.fill" : "checkmark.circle.fill")
                        .font(.caption.weight(.semibold))
                        .foregroundStyle(isWorking ? .orange : .green)
                }

                HStack(spacing: 14) {
                    OfficeSeat(agent: OfficeAgent(name: "PA Manager", symbol: "person.badge.key.fill"), isCabin: true, isBusy: isWorking)
                    OfficeSeat(agent: OfficeAgent(name: "Personal PA", symbol: "person.crop.circle.badge.checkmark"), isCabin: true, isBusy: isWorking)
                }

                Text("SPECIALIST CUBICLES")
                    .font(.caption.weight(.bold))
                    .foregroundStyle(.secondary)
                    .padding(.top, 2)

                LazyVGrid(columns: [GridItem(.adaptive(minimum: 135), spacing: 12)], spacing: 12) {
                    ForEach(specialists) { specialist in
                        OfficeSeat(agent: specialist, isCabin: false, isBusy: false)
                    }
                }
            }
            .padding(28)
        }
    }
}

private struct OfficeAgent: Identifiable {
    let name: String
    let symbol: String
    var id: String { name }
}

private struct OfficeSeat: View {
    let agent: OfficeAgent
    let isCabin: Bool
    let isBusy: Bool
    @State private var pulse = false

    var body: some View {
        VStack(spacing: 8) {
            ZStack(alignment: .bottom) {
                RoundedRectangle(cornerRadius: isCabin ? 18 : 12)
                    .fill(isCabin ? Color.indigo.opacity(0.12) : Color.secondary.opacity(0.08))
                    .frame(height: isCabin ? 116 : 96)
                    .overlay(alignment: .topLeading) {
                        Text(isCabin ? "CABIN" : "CUBICLE")
                            .font(.system(size: 9, weight: .bold))
                            .foregroundStyle(.secondary)
                            .padding(10)
                    }

                RoundedRectangle(cornerRadius: 5)
                    .fill(Color.brown.opacity(0.65))
                    .frame(width: isCabin ? 118 : 92, height: 19)
                    .padding(.bottom, 16)

                Image(systemName: agent.symbol)
                    .font(.system(size: isCabin ? 29 : 24, weight: .semibold))
                    .foregroundStyle(isBusy ? .orange : .blue)
                    .padding(11)
                    .background(.background, in: Circle())
                    .scaleEffect(isBusy && pulse ? 1.08 : 1)
                    .padding(.bottom, 27)
            }

            Text(agent.name)
                .font(.caption.weight(.semibold))
                .lineLimit(1)
            Text(isBusy ? "Busy" : "Ready")
                .font(.caption2.weight(.medium))
                .foregroundStyle(isBusy ? .orange : .green)
        }
        .frame(maxWidth: .infinity)
        .padding(10)
        .background(.quaternary, in: RoundedRectangle(cornerRadius: 14))
        .onAppear {
            guard isBusy else { return }
            withAnimation(.easeInOut(duration: 0.75).repeatForever(autoreverses: true)) {
                pulse = true
            }
        }
    }
}
